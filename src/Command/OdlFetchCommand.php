<?php
declare(strict_types = 1);
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
	 * @param Fetcher $fetcher
	 */
	public function __construct(Fetcher $fetcher) {
		parent::__construct();
		$this->fetcher = $fetcher;
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
	 * @throws \RuntimeException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->io = new SymfonyStyle($input, $output);

		$this->init();
		$this->fetch();
		$this->import();

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
		$timestamp = date('Y-m-d_His');
		$this->dir .= DIRECTORY_SEPARATOR . $timestamp;
		@mkdir($this->dir);
		if (is_dir($this->dir)) {
			$this->debug('Fetch directory ' . $timestamp . ' created.');
		} else {
			throw new \RuntimeException('Could not create fetch directory ' . $timestamp . '.');
		}
	}

	private function fetch(): void {
		$baseFile      = $this->dir . DIRECTORY_SEPARATOR . Fetcher::BASE_FILE;
		$stationsFile  = $this->fetcher->getBaseFile();
		if (!file_put_contents($baseFile, $stationsFile)) {
			throw new \RuntimeException('Could not save stations.');
		}
		$stations = json_decode($stationsFile, true);
		if (empty($stations)) {
			throw new \RuntimeException('No stations found.');
		}

		$tries       = 10;
		$stationKeys = array_keys($stations);
		$this->io->note(count($stationKeys) . ' stations found.');
		foreach ($stationKeys as $key) {
			$id          = (string)$key;
			$stationFile = $this->dir . DIRECTORY_SEPARATOR . $id . '.json';
			sleep(2);

			$this->debug('Fetching ' . $id . '...');
			if (!file_put_contents($stationFile, $this->fetcher->getStation($id))) {
				$this->io->error('Could not save station ' . $id . '.');
				if (--$tries <= 0) {
					throw new \RuntimeException('Too many fetching errors.');
				}
				sleep(30);
			}
		}
	}

	private function import(): void {

	}
}
