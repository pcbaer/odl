<?php
declare(strict_types = 1);
namespace App\Logging;

use Symfony\Component\Console\Style\SymfonyStyle;

trait DebugTrait
{
	/**
	 * @var SymfonyStyle|null
	 */
	private $io;

	protected function info(string $message): void {
		if ($this->io && $this->io->isVerbose()) {
			$this->io->note($message);
		}
	}

	/**
	 * @param string $message
	 */
	protected function debug(string $message): void {
		if ($this->io && $this->io->isDebug()) {
			$this->io->note($message);
		}
	}
}
