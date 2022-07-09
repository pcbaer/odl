<?php
declare(strict_types = 1);
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Dosage\StationData;
use App\Logging\DebugTrait;
use App\Repository\StationRepository;
use App\Retrieval\Updater;

#[AsCommand('odl:update', 'Import a bunch of ODL data.')]
class OdlUpdateCommand extends Command
{
	use DebugTrait;

	public function __construct(protected Updater $updater, protected StationData $stationData,
		                        protected StationRepository $stationRepository) {
		parent::__construct();
	}

	/**
	 * Set command configuration.
	 */
	protected function configure(): void {
		$this->setHelp('This command connects to the BfS web service and fetches the latest ODL data for a bunch of stations.');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->io = new SymfonyStyle($input, $output);
		$this->io->note($this->date() . 'ODL fetch/import started.');
		try {
			$this->update();
		} catch (\Throwable $e) {
			$this->io->error($e->getMessage());
			return 1;
		}
		$this->io->success($this->date() . 'New data fetched and imported.');
		return 0;
	}

	/**
	 * @return string
	 */
	private function date(): string {
		return '[' . date('H:i') . '] ';
	}

	/**
	 * @throws \Throwable
	 */
	private function update(): void {
		$this->updater->updateStations();
		$stations = $this->updater->getStations();
		if (empty($stations)) {
			throw new \RuntimeException('No stations found.');
		}

		$tries = 10;
		$i     = 0;
		$n     = count($stations);
		$this->io->note($this->date() . $n . ' stations found.');

		foreach ($stations as $station) {
			sleep(2);
			$this->debug('Fetching ' . $station . ' (' . ++$i . '/' . $n . ')...');
			try {
				$this->updater->updateMeasurements($station);
			} catch (\Throwable $e) {
				$this->io->error($e->getMessage());
				if (--$tries <= 0) {
					throw new \RuntimeException('Too many fetching errors.', 0, $e);
				}
				$this->io->warning('Waiting 10 seconds before next try...');
				sleep(10);
			}
		}
	}
}
