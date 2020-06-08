<?php
declare(strict_types = 1);
namespace App\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

use App\Entity\Station;
use App\Repository\StationRepository;
use App\Retrieval\Fetcher;
use App\Retrieval\Parser;

class OdlFetchCommand extends Command {

	/**
	 * @var string
	 */
	protected static $defaultName = 'odl:fetch';

	/**
	 * @var SymfonyStyle|null
	 */
	private $io;

	/**
	 * @var string|null
	 */
	private $dir;

	/**
	 * @var Fetcher
	 */
	private $fetcher;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var StationRepository
	 */
	private $stationRepository;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @param Fetcher $fetcher
	 * @param StationRepository $stationRepository
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(Fetcher $fetcher, StationRepository $stationRepository,
								EntityManagerInterface $entityManager) {
		parent::__construct();
		$this->fetcher           = $fetcher;
		$this->stationRepository = $stationRepository;
		$this->entityManager     = $entityManager;
	}

	/**
	 * Set command configuration.
	 */
	protected function configure(): void {
		$this->setDescription('Fetch and import ODL data.');
		$this->setHelp('This command connects to the BfS web service and fetches the latest ODL data.');
		$this->addOption('fetch-only', 'f', InputOption::VALUE_NONE, 'Fetch only, do not import');
		$this->addOption('import-dir', 'i', InputOption::VALUE_REQUIRED, 'Do not fetch, import given directory');
		$this->addOption('list', 'l', InputOption::VALUE_NONE, 'Just list all stations in the database');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->io = new SymfonyStyle($input, $output);

		$fetchOnly = $input->getOption('fetch-only');
		$importDir = $input->getOption('import-dir');
		$list      = $input->getOption('list');
		try {
			if ($list) {
				$this->io->note('Listing ODL stations from database.');
				return $this->listStations() ? 0 : 1;
			}

			$this->io->note($this->date() . 'ODL fetch/import started.');
			$this->init($importDir);
			if (!$importDir) {
				$this->fetch();
			}
			if (!$fetchOnly) {
				$this->import();
			}
			if ((bool)$fetchOnly === (bool)$importDir) {
				$this->io->success($this->date() . 'New data fetched and imported.');
			} elseif ($fetchOnly) {
				$this->io->success($this->date() . 'New data fetched.');
			} else {
				$this->io->success($this->date() . 'New data imported.');
			}
			return 0;
		} catch (\Exception $e) {
			$this->io->error($e->getMessage());
			return 1;
		}
	}

	/**
	 * @param string $message
	 */
	protected function debug(string $message): void {
		if ($this->io->isDebug()) {
			$this->io->note($message);
		}
	}

	/**
	 * @return string
	 */
	private function date(): string {
		return '[' . date('H:i') . '] ';
	}

	/**
	 * @return bool
	 */
	private function listStations(): bool {
		$stations = $this->stationRepository->findAll();
		if (empty($stations)) {
			$this->io->warning('No stations found.');
			return false;
		}

		foreach ($stations as $station) {
			$this->io->writeLn($this->createStationStatus($station));
		}
		return true;
	}

	/**
	 * @param string|null $importDir
	 * @throws \RuntimeException
	 */
	private function init(?string $importDir): void {
		$this->initFetchDirectory();
		if ($importDir) {
			$this->checkImportDir($importDir);
		} else {
			$this->initTimestampDirectory();
		}
	}

	/**
	 * @throws \RuntimeException
	 */
	private function initFetchDirectory(): void {
		$this->dir = realpath(__DIR__ . '/../../var') . DIRECTORY_SEPARATOR . 'fetch';
		if (is_dir($this->dir)) {
			$this->debug('Fetch directory already exists.');
		} else {
			@mkdir($this->dir);
			if (!is_dir($this->dir)) {
				throw new \RuntimeException('Could not create the fetch directory.');
			}
			$this->debug('Fetch directory created.');
		}
	}

	/**
	 * @param string $importDir
	 */
	private function checkImportDir(string $importDir): void {
		$this->dir .= DIRECTORY_SEPARATOR . $importDir;
		if (!is_dir($this->dir)) {
			throw new \RuntimeException('Import directory not found.');
		}
		$this->debug('Use import directory ' . $importDir . '.');
	}

	/**
	 * @throws \RuntimeException
	 */
	private function initTimestampDirectory(): void {
		$timestamp  = date('Y-m-d_His');
		$this->dir .= DIRECTORY_SEPARATOR . $timestamp;
		@mkdir($this->dir);
		if (is_dir($this->dir)) {
			$this->debug('Fetch directory ' . $timestamp . ' created.');
		} else {
			throw new \RuntimeException('Could not create fetch directory ' . $timestamp . '.');
		}
	}

	/**
	 * @throws \RuntimeException
	 */
	private function fetch(): void {
		$baseFile = $this->dir . DIRECTORY_SEPARATOR . Fetcher::BASE_FILE;
		try {
			$stationsFile = $this->fetcher->getBaseFile();
		} catch (ExceptionInterface $e) {
			throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
		}
		if (!file_put_contents($baseFile, $stationsFile)) {
			throw new \RuntimeException('Could not save stations.');
		}
		$stations = json_decode($stationsFile, true);
		if (empty($stations)) {
			throw new \RuntimeException('No stations found.');
		}

		$tries       = 10;
		$stationKeys = array_keys($stations);
		$i           = 0;
		$n           = count($stationKeys);
		$this->io->note($this->date() . $n . ' stations found.');
		foreach ($stationKeys as $key) {
			$id          = (string)$key;
			$stationFile = $this->dir . DIRECTORY_SEPARATOR . $id . '.json';
			sleep(2);

			$this->debug('Fetching ' . $id . ' (' . ++$i . '/' . $n . ')...');
			try {
				$stationData = $this->fetcher->getStation($id);
				if (!file_put_contents($stationFile, $stationData)) {
					throw new \RuntimeException('Could not save station ' . $id . '.');
				}
			} catch (ExceptionInterface $e) {
				$this->io->error($e->getMessage());
				if (--$tries <= 0) {
					throw new \RuntimeException('Too many fetching errors.');
				}
				$this->io->warning('Waiting 30 seconds before next try...');
				sleep(30);
			}
		}
	}

	/**
	 * Import all data.
	 *
	 * @throws \RuntimeException
	 */
	private function import(): void {
		$this->parser = new Parser($this->dir, $this->stationRepository);
		$stations     = $this->importStations();
		$counts       = [];
		foreach ($stations as $odlId) {
			$n            = $this->importMeasurements($odlId);
			$counts[$n][] = $odlId;
		}
		$this->printStatistics($counts);
	}

	/**
	 * Import station data.
	 */
	private function importStations(): array {
		$this->io->note($this->date() . 'Importing stations...');
		$stations = [];

		foreach ($this->parser->getStations() as $station) {
			$odlId = $station->getOdlId();
			if ($station->getId()) {
				$this->debug('Updating station ' . $odlId . '.');
			} else {
				$this->io->note($this->date() . 'New station ' . $odlId . ' (' . $station->getCity() . ').');
			}
			$this->entityManager->persist($station);
			$stations[] = $odlId;
		}

		$this->entityManager->flush();
		return $stations;
	}

	/**
	 * @param string $odlId
	 * @return int
	 * @throws \RuntimeException
	 */
	private function importMeasurements(string $odlId): int {
		$station = $this->stationRepository->findByOdlId($odlId);
		if (!$station) {
			throw new \RuntimeException('Station ' . $odlId . ' is not persistent.');
		}
		$count = 0;
		$id    = $station->getId();

		$connection   = $this->entityManager->getConnection();
		$measurements = $this->parser->getMeasurements($odlId);
		if (!$connection->beginTransaction()) {
			throw new \RuntimeException('Transaction not started for measurements of ' . $id . '.');
		}
		foreach ($measurements as $time => $row) {
			try {
				$connection->insert('measurement', [
					'station_id'  => $id,
					'time'        => $time . ':00',
					'dosage'      => $row['mw'],
					'rain'        => $row['r'],
					'abnormality' => $row['ps']
				]);
				$count++;
			} catch (UniqueConstraintViolationException $e) {
				$this->debug($e->getMessage());
			} catch (DBALException $e) {
				throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
			}
		}

		try {
			if (!$connection->commit()) {
				throw new \RuntimeException('Transaction commit failed for measurements of ' . $id . '.');
			}
			$this->debug($count . ' new rows imported for station ' . $odlId . '.');
		} catch (DBALException $e) {
			throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		return $count;
	}

	/**
	 * @param array $counts
	 */
	private function printStatistics(array $counts): void {
		krsort($counts, SORT_NUMERIC);
		$this->io->note($this->date() . 'New rows statistics:');
		foreach ($counts as $n => $odlIds) {
			$count = count($odlIds);
			$this->io->note($n . ' new rows for ' . $count . ' stations.');
		}

		$emptyOdlIds = $counts[0] ?? [];
		$count       = count($emptyOdlIds);
		if ($count > 0) {
			$this->io->note($count . ' stations without new rows:');
			foreach ($this->stationRepository->findByOdlIds($emptyOdlIds) as $station) {
				$measurement = $this->fetchLastMeasurement($station);
				$this->io->writeln($this->createStationStatus($station, $measurement));
			}
		}
	}

	/**
	 * @param Station $station
	 * @return array
	 */
	private function fetchLastMeasurement(Station $station): array {
		$connection = $this->entityManager->getConnection();
		$builder    = $connection->createQueryBuilder();
		$query      = $builder->select('time, dosage')->from('measurement');
		$query->where($query->expr()->eq('station_id', '?'))->orderBy('time', 'DESC')->setMaxResults(1);
		$query->setParameter(1, $station->getId());
		$result = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
		return $result[0] ?? [];
	}

	/**
	 * @param Station $station
	 * @return string
	 */
	private function createStationStatus(Station $station, array $measurement): string {
		$id     = sprintf('%1$4u', $station->getId());
		$zip    = sprintf('%1$5s', $station->getZip());
		$odlId  = sprintf('%1$9s', $station->getOdlId());
		$last   = sprintf('%1$1.3f', $station->getLast());
		$status = sprintf('%1$3u', $station->getStatus());
		$time   = isset($measurement['time']) ? substr($measurement['time'], 0, 16) : '';
		$dosage = isset($measurement['dosage']) ? sprintf('%1$1.3f', (float)$measurement['dosage']) : '';

		$city = mb_substr($station->getCity(), 0, 30);
		$l    = mb_strlen($city);
		if ($l < 30) {
			$city .= str_pad('', 30 - $l);
		}
		$db = ($time && $dosage) ? ' DB: ' . $time . ' ' . $dosage . ' µSv/h' : '';

		return '#' . $id . ': ' . $zip . ' ' . $city . ' (' . $odlId . ') Last: ' . $last . ' Status: ' . $status . $db;
	}
}
