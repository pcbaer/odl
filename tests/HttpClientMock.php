<?php
declare(strict_types = 1);
namespace App\Tests;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class HttpClientMock implements HttpClientInterface
{
	/**
	 * @var array[string=>string]
	 */
	private $response = [];

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $options
	 * @return ResponseInterface
	 */
	public function request(string $method, string $url, array $options = []): ResponseInterface {
		return $this->response[$url][$method] ?? new ResponseMock();
	}

	/**
	 * @param iterable|ResponseInterface|ResponseInterface[] $responses
	 * @param float|null $timeout
	 * @return ResponseStreamInterface
	 */
	public function stream($responses, float $timeout = null): ResponseStreamInterface {
		throw new \RuntimeException('Not implemented.');
	}

	/**
	 * @param ResponseInterface $response
	 * @param string $url
	 * @param string $method
	 */
	public function add(ResponseInterface $response, string $url, string $method = 'GET'): void {
		$this->response[$url][$method] = $response;
	}
}
