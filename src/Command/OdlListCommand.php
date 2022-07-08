<?php
declare(strict_types = 1);
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Dosage\StationData;
use App\Entity\Station;
use App\Logging\DebugTrait;
use App\Repository\StationRepository;

#[AsCommand('odl:list', 'List ODL stations.')]
class OdlListCommand extends Command
{
	use DebugTrait;

	public function __construct(protected StationRepository $stationRepository, protected StationData $stationData) {
		parent::__construct();
	}

	/**
	 * Set command configuration.
	 */
	protected function configure(): void {
		$this->setHelp('This command lists all stations in the database.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->io = new SymfonyStyle($input, $output);
		$this->io->note('Listing ODL stations from database.');
		$stations = $this->stationRepository->findAll();
		if (empty($stations)) {
			$this->io->warning('No stations found.');
			return 1;
		}
		foreach ($stations as $station) {
			$this->io->writeLn($this->createStationStatus($station));
		}
		return 0;
	}

	/**
	 * @param Station $station
	 * @return string
	 */
	protected function createStationStatus(Station $station): string {
		$measurement = $this->stationData->setStation($station)->fetchLastMeasurement();

		$id     = sprintf('%1$4u', $station->getId());
		$zip    = sprintf('%1$5s', $station->getZip());
		$odlId  = sprintf('%1$9s', $station->getOdlId());
		$last   = sprintf('%1$1.3f', $station->getLastValue());
		$status = sprintf('%1$3u', $station->getStatus());
		$text   = $station->getStatusText();
		$time   = isset($measurement['time']) ? substr($measurement['time'], 0, 16) : '';
		$dosage = isset($measurement['dosage']) ? sprintf('%1$1.3f', (float)$measurement['dosage']) : '';

		$city = mb_substr($station->getCity(), 0, 30);
		$l    = mb_strlen($city);
		if ($l < 30) {
			$city .= str_pad('', 30 - $l);
		}
		$db = ($time && $dosage) ? ' DB: ' . $time . ' ' . $dosage . ' µSv/h' : '';

		return '#' . $id . ': ' . $zip . ' ' . $city . ' (' . $odlId . ') Last: ' . $last . ' Status: ' . $status . ' ' . $text . $db;
	}
}
