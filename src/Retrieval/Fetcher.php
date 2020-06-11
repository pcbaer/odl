<?php
declare(strict_types = 1);
namespace App\Retrieval;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Fetcher {

	public const URL = 'https://odlinfo.bfs.de/daten/json';

	public const BASE_FILE = 'stamm.json';

	/**
	 * @var HttpClientInterface
	 */
	private $client;

	/**
	 * @param string $user
	 * @param string $password
	 */
	public function __construct(string $user, string $password) {
		$this->client = HttpClient::create([
			'auth_basic' => [$user, $password],
			'headers'    => [
				'Accept' => 'application/json'
			]
]		);
	}

	/**
	 * @return string
	 * @throws ExceptionInterface
	 */
	public function getBaseFile(): string {
		return $this->fetch(self::BASE_FILE);
	}

	/**
	 * @param string $id
	 * @return string
	 * @throws ExceptionInterface
	 */
	public function getStation(string $id): string {
		return $this->fetch($id . '.json');
	}

	/**
	 * @param HttpClientInterface $client
	 * @return self
	 */
	public function setClient(HttpClientInterface $client): self {
		$this->client = $client;

		return $this;
	}

	/**
	 * @param string $fileName
	 * @return string
	 * @throws ExceptionInterface
	 */
	private function fetch(string $fileName): string {
		$url      = self::URL . '/' . $fileName;
		$response = $this->client->request('GET', $url);
		return $response->getContent();
	}
}
