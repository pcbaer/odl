<?php
declare(strict_types = 1);
namespace App\Tests\Retrieval;

use App\Entity\Station;
use App\Repository\StationRepository;
use App\Retrieval\Parser;
use HallerbachIT\Testing\Symfony\EntityManagerTest;

class ParserTest extends EntityManagerTest
{
	/**
	 * @var string|null
	 */
	protected $dir = __DIR__ . '/../data';

	/**
	 * @var StationRepository
	 */
	protected $repository;

	/**
	 * Initialize test.
	 */
	protected function setUp(): void {
		parent::setUp();
		if (!$this->repository) {
			$this->repository = self::$doctrine->getRepository(Station::class);
		}
	}

	/**
	 * @return string
	 */
	protected function getDefaultSql(): string {
		return file_get_contents(__DIR__ . '/../data/default.sql');
	}

	/**
	 * @test
	 * @return Parser
	 */
	public function construct(): Parser {
		$parser = new Parser($this->dir, $this->repository);

		$this->assertNotNull($parser);

		return $parser;
	}

	/**
	 * @test
	 * @depends construct
	 * @param Parser $parser
	 */
	public function getStations(Parser $parser): void {
		$stations = [];
		foreach($parser->getStations() as $station) {
			$stations[] = $station;
		}

		$this->assertArray($stations, 3, Station::class);

		/* @var Station $station */

		$station = $stations[0];
		$this->assertSame(1, $station->getId());
		$this->assertSame('Darmstadt', $station->getCity());
		$this->assertSame(0.085, $station->getLast());

		$station = $stations[1];
		$this->assertSame(0, $station->getId());
		$this->assertSame('15890', $station->getZip());
		$this->assertSame('Eisenhüttenstadt', $station->getCity());
		$this->assertSame('120671201', $station->getOdlId());
		$this->assertSame(2, $station->getKid());
		$this->assertSame(43, $station->getAltitude());
		$this->assertSame(52.16, $station->getLatitude());
		$this->assertSame(14.66, $station->getLongitude());
		$this->assertSame(1, $station->getStatus());
		$this->assertSame(0.068, $station->getLast());

		$station = $stations[2];
		$this->assertSame(0, $station->getId());
		$this->assertSame('Fürstenzell OT Gföhret', $station->getCity());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Parser $parser
	 */
	public function getMeaurements(Parser $parser): void {
		$measurements = $parser->getMeasurements('064110003');

		$this->assertArray($measurements, 24 + 11 + 1, 'array');
		$this->assertArrayHasKey('2020-03-25 00:00', $measurements);
		$this->assertArrayHasKey('2020-03-26 11:00', $measurements);

		$measurement = $measurements['2020-03-25 00:00'];

		$this->assertArrayHasKey('mw', $measurement);
		$this->assertSame(0.089, $measurement['mw']);
		$this->assertArrayHasKey('ps', $measurement);
		$this->assertSame(0, $measurement['ps']);
		$this->assertArrayHasKey('r', $measurement);
		$this->assertSame(0.01, $measurement['r']);

		$measurement = $measurements['2020-03-26 11:00'];

		$this->assertArrayHasKey('mw', $measurement);
		$this->assertSame(0.0, $measurement['mw']);
		$this->assertArrayHasKey('ps', $measurement);
		$this->assertSame(0, $measurement['ps']);
		$this->assertArrayHasKey('r', $measurement);
		$this->assertSame(0.02, $measurement['r']);
	}
}
