<?php
declare(strict_types = 1);
namespace App\Tests\Repository;

use HallerbachIT\Testing\Symfony\EntityManagerTest;

use App\Entity\Station;
use App\Repository\StationRepository;

class StationRepositoryTest extends EntityManagerTest
{
	/**
	 * @return string
	 */
	protected function getDefaultSql(): string {
		return file_get_contents(__DIR__ . '/../data/default.sql');
	}

	/**
	 * @param Station $station
	 */
	protected function assertStationOne($station): void {
		$this->assertInstanceOf(Station::class, $station);
		$this->assertSame(1, $station->getId(), 'Expected Station #1.');
	}

	/**
	 * @test
	 * @return StationRepository
	 */
	public function construct(): StationRepository {
		/** @var StationRepository $repository */
		$repository = self::$doctrine->getRepository(Station::class);

		$this->assertInstanceOf(StationRepository::class, $repository);

		return $repository;
	}

	/**
	 * @test
	 * @depends construct
	 * @param StationRepository $repository
	 */
	public function find(StationRepository $repository): void {
		$station = $repository->find(1);

		$this->assertInstanceOf(Station::class, $station);
		$this->assertSame(1, $station->getId());
		$this->assertSame('064110003', $station->getOdlId());
		$this->assertSame('64295', $station->getZip());
		$this->assertSame('Darmstadt', $station->getCity());
		$this->assertSame(138, $station->getAltitude());
		$this->assertSame(49.84, $station->getLatitude());
		$this->assertSame(8.59, $station->getLongitude());
		$this->assertSame(1, $station->getStatus());
		$this->assertSame(0.086, $station->getLastValue());
	}

	/**
	 * @test
	 * @depends construct
	 * @param StationRepository $repository
	 */
	public function findOneBy(StationRepository $repository): void {
		$this->assertStationOne($repository->findOneBy(['city' => 'Darmstadt']));
	}

	/**
	 * @test
	 * @depends construct
	 * @param StationRepository $repository
	 */
	public function findAll(StationRepository $repository): void {
		$this->assertArray($repository->findAll(), 1, Station::class);
	}

	/**
	 * @test
	 * @depends construct
	 * @param StationRepository $repository
	 */
	public function findBy(StationRepository $repository): void {
		$this->assertArray($repository->findBy(['zip' => '64295']), 1, Station::class);
	}

	/**
	 * @test
	 * @depends construct
	 * @param StationRepository $repository
	 */
	public function findByOdlId(StationRepository $repository): void {
		$this->assertStationOne($repository->findByOdlId('064110003'));
	}
}
