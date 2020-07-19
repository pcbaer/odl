<?php
declare(strict_types = 1);
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Logging\DebugTrait;
use App\Retrieval\StorageTrait;

class OdlCleanCommand extends Command {

	use DebugTrait;
	use StorageTrait;

	private const DAYS = 10;

	/**
	 * @var string
	 */
	protected static $defaultName = 'odl:clean';

	/**
	 * @var bool
	 */
	private $simulate = false;

	/**
	 * Set command configuration.
	 */
	protected function configure() {
		$this->setDescription('Clean up old import data files.');
		$this->setHelp('This command removes the remains of previous data fetches.');
		$this->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Clear all data older than number of days (default: 10)');
		$this->addOption('simulate', 's', InputOption::VALUE_NONE, 'Do not delete, simulate only');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->io = new SymfonyStyle($input, $output);

		$days = (int)($input->getOption('days') ?? self::DAYS);
		if ($days < 0) {
			$this->io->warning('Please specify a number of days ≥ 0.');
			return 1;
		}
		$this->simulate = (bool)$input->getOption('simulate');
		if ($this->simulate) {
			$this->io->success('Simulating only, no data will be deleted.');
		}

		$dateTime = new \DateTime();
		$date     = $dateTime->sub(new \DateInterval('P' . $days . 'D'))->format('Y-m-d');
		$this->info('All data fetched before ' . $date . ' will be deleted.');

		$this->initFetchDirectory();
		return $this->deleteBefore($date . '_000000');
	}

	/**
	 * @param string $date
	 * @return int
	 */
	private function deleteBefore(string $date): int {
		$result = true;

		foreach (glob($this->dir . '/*', GLOB_ONLYDIR) as $path) {
			$dir = basename($path);
			if ($dir < $date) {
				$this->info('Data in dir ' . $dir . ' will be deleted.');
				$result = $result && $this->delete($path);
			} else {
				$this->info('Ignoring data in dir ' . $dir . '.');
			}
		}

		return $result ? 0 : 1;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	private function delete(string $path): bool {
		foreach (glob($path . '/*.json') as $file) {
			$this->debug('Deleting ' . $file . '...');
			try {
				if (!$this->simulate && !unlink($file)) {
					$this->io->error('File ' . $file . ' could not be deleted.');
					return false;
				}
			} catch (\ErrorException $e) {
				$this->io->error($e->getMessage());
				return false;
			}
		}

		$this->debug('Removing ' . $path . '...');
		try {
			if (!$this->simulate && !rmdir($path)) {
				$this->io->error('Directory ' . $path . ' could not be removed.');
				return false;
			}
		} catch (\ErrorException $e) {
			$this->io->error($e->getMessage());
			return false;
		}

		return true;
	}
}
