<?php
declare(strict_types = 1);
namespace App\Retrieval;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Fetcher
{
	public const BASE_URL = 'https://www.imis.bfs.de/ogc/opendata/ows?service=WFS&version=1.1.0';

	public const FEATURE_SITES = [
		'request'      => 'GetFeature',
		'typeName'     => 'opendata:odlinfo_sitelist',
		'outputFormat' => 'application/json'
	];

	public const FEATURE_MEASUREMENTS = [
		'request'      => 'GetFeature',
		'typeName'     => 'opendata:odlinfo_timeseries_odl_1h',
		'outputFormat' => 'application/json',
		'viewparams'   => 'kenn:%ODL_ID%',
		'sortBy'       => 'start_measure',
		'filter'       => '%FILTER_DATETIME%'
	];

	protected HttpClientInterface $client;

	public function __construct() {
		$this->client = HttpClient::create(['headers' => ['Accept' => 'application/json']]);
	}

	/**
	 * @return string
	 * @throws ExceptionInterface
	 */
	public function getSiteList(): string {
		$url = $this->buildUrl(self::FEATURE_SITES);
		return $this->fetch($url);
	}

	/**
	 * @throws ExceptionInterface
	 */
	public function getMeasurements(string $id, Filter $filter): string {
		$parameters = [
			'%ODL_ID%'          => $id,
			'%FILTER_DATETIME%' => (string)$filter
		];
		$url        = $this->buildUrl(self::FEATURE_MEASUREMENTS, $parameters);
		return $this->fetch($url);
	}

	public function setClient(HttpClientInterface $client): self {
		$this->client = $client;
		return $this;
	}

	protected function buildUrl(array $feature, array $parameters = []): string {
		$url = self::BASE_URL;
		foreach ($feature as $param => $value) {
			foreach ($parameters as $search => $replace) {
				$value = str_replace($search, $replace, $value);
			}
			$url .= '&' . $param . '=' . $value;
		}
		return $url;
	}

	/**
	 * @throws ExceptionInterface
	 */
	protected function fetch(string $url): string {
		$response = $this->client->request('GET', $url);
		return $response->getContent();
	}
}
