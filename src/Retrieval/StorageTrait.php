<?php
declare(strict_types = 1);
namespace App\Retrieval;

use App\Logging\DebugTrait;

trait StorageTrait {

	use DebugTrait;

	/**
	 * @var string|null
	 */
	protected $dir;

	/**
	 * @throws \RuntimeException
	 */
	protected function initFetchDirectory(): void {
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
}
