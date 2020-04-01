<?php
declare(strict_types = 1);
namespace App\Retrieval;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Fetcher {

	public const BASE_FILE = 'stamm.json';

	private const URL = 'https://odlinfo.bfs.de/daten/json';

	/**
	 * @var HttpClientInterface
	 */
	private HttpClientInterface $client;

	/**
	 * @param string $user
	 * @param string $password
	 */
	public function __construct(string $user, string $password) {
		$this->client = HttpClient::create([
			'auth_basic' => [$user, $password],
			'headers'    => [
				'Content-Type' => 'application/json'
			]
]		);
	}

	/**
	 * @return string
	 */
	public function getBaseFile(): string {
		return $this->fetch(self::BASE_FILE);
	}

	/**
	 * @param string $id
	 * @return string
	 */
	public function getStation(string $id): string {
		return $this->fetch($id . '.json');
	}

	private function fetch(string $fileName): string {
		$url      = self::URL . '/' . $fileName;
		$response = $this->client->request('GET', $url);
		return $response->getContent();
	}
};
