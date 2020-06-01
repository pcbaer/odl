<?php
declare(strict_types = 1);
namespace App\Tests;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ResponseMock implements ResponseInterface
{
	/**
	 * @var string
	 */
	private $content;

	/**
	 * @param string $content
	 */
	public function __construct(string $content = '') {
		$this->content = $content;
	}

	/**
	 * @throws \RuntimeException
	 */
	public function cancel(): void {
		throw new \RuntimeException('Not implemented.');
	}

	/**
	 * @param bool $throw
	 * @return string
	 */
	public function getContent(bool $throw = true): string {
		return $this->content;
	}

	/**
	 * @param bool $throw
	 * @return array
	 */
	public function getHeaders(bool $throw = true): array {
		return [];
	}

	/**
	 * @param string|null $type
	 * @return array|mixed|void|null
	 */
	public function getInfo(string $type = null) {
		return null;
	}

	/**
	 * @return int
	 */
	public function getStatusCode(): int {
		return 200;
	}

	/**
	 * @param bool $throw
	 * @return array
	 */
	public function toArray(bool $throw = true): array {
		return [];
	}
}
