<?php
declare(strict_types = 1);
namespace App\Logging;

use Symfony\Component\Console\Style\SymfonyStyle;

trait DebugTrait
{
	private SymfonyStyle $io;

	protected function info(string $message): void {
		if ($this->io->isVerbose()) {
			$this->io->note($message);
		}
	}

	protected function debug(string $message): void {
		if ($this->io->isDebug()) {
			$this->io->note($message);
		}
	}
}
