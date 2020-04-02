<?php
declare(strict_types = 1);
namespace App\Command;

use App\Repository\StationRepository;
use App\Retrieval\Parser;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

use App\Retrieval\Fetcher;

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
		$this->setDescription('Connect to the BfS web service and fetch the latest ODL data.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->io = new SymfonyStyle($input, $output);

		try {
			$this->init();
			// $this->fetch(); //TODO
			$this->import();
		} catch (\Exception $e) {
			$this->io->error($e->getMessage());
			return 1;
		}

		$this->io->success('New data fetched and imported.');
		return 0;
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
	 * @throws \RuntimeException
	 */
	private function init(): void {
		$this->initFetchDirectory();
		$this->initTimestampDirectory();
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
	 * @throws \RuntimeException
	 */
	private function initTimestampDirectory(): void {
		$timestamp = date('Y-m-d_His'); $timestamp = '2020-04-01_232006'; //TODO
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
		$this->io->note($n . ' stations found.');
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
		foreach ($stations as $odlId) {
			$this->importMeasurements($odlId);
		}
	}

	/**
	 * Import station data.
	 */
	private function importStations(): array {
		$this->io->note('Importing stations...');
		$stations = [];

		foreach ($this->parser->getStations() as $station) {
			$odlId = $station->getOdlId();
			if ($station->getId()) {
				$this->debug('Updating station ' . $odlId . '.');
			} else {
				$this->io->note('New station ' . $odlId . ' (' . $station->getCity() . ').');
			}
			$this->entityManager->persist($station);
			$stations[] = $odlId;
		}

		$this->entityManager->flush();
		return $stations;
	}

	/**
	 * @param string $odlId
	 * @throws \RuntimeException
	 */
	private function importMeasurements(string $odlId): void {
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
			}
		}

		try {
			if (!$connection->commit()) {
				throw new \RuntimeException('Transaction commit failed for measurements of ' . $id . '.');
			}
			$this->io->note($count . ' new rows imported for station ' . $odlId . '.');
		} catch (DBALException $e) {
			throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
		}
	}
}
