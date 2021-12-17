<?php
declare(strict_types = 1);
namespace App\Tests\Entity;

use HallerbachIT\Testing\BaseTest;

use App\Entity\Station;

class StationTest extends BaseTest {

	/**
	 * @test
	 * @return Station
	 */
	public function construct(): Station {
		$station = new Station();

		$this->assertNotNull($station);

		return $station;
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getId(Station $station): void {
		$this->assertSame(0, $station->getId());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getOdlId(Station $station): void {
		$this->assertSame('', $station->getOdlId());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setOdlId(Station $station): void {
		$odlId = '123456';

		$this->assertSame($station, $station->setOdlId($odlId));
		$this->assertSame($odlId, $station->getOdlId());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getZip(Station $station): void {
		$this->assertSame('', $station->getZip());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setZip(Station $station): void {
		$zip = '12345';

		$this->assertSame($station, $station->setZip($zip));
		$this->assertSame($zip, $station->getZip());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getCity(Station $station): void {
		$this->assertSame('', $station->getCity());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setCity(Station $station): void {
		$city = 'München';

		$this->assertSame($station, $station->setCity($city));
		$this->assertSame($city, $station->getCity());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getKid(Station $station): void {
		$this->assertSame(0, $station->getKid());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setKid(Station $station): void {
		$kid = 5;

		$this->assertSame($station, $station->setKid($kid));
		$this->assertSame($kid, $station->getKid());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getAltitude(Station $station): void {
		$this->assertSame(0, $station->getAltitude());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setAltitude(Station $station): void {
		$altitude = 123;

		$this->assertSame($station, $station->setAltitude($altitude));
		$this->assertSame($altitude, $station->getAltitude());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getLatitude(Station $station): void {
		$this->assertSame(0.0, $station->getLatitude());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setLatitude(Station $station): void {
		$latitude = 50.13;

		$this->assertSame($station, $station->setLatitude($latitude));
		$this->assertSame($latitude, $station->getLatitude());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getLongitude(Station $station): void {
		$this->assertSame(0.0, $station->getLongitude());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setLongitude(Station $station): void {
		$longitude = 6.45;

		$this->assertSame($station, $station->setLongitude($longitude));
		$this->assertSame($longitude, $station->getLongitude());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getStatusMethod(Station $station): void {
		$this->assertSame(0, $station->getStatus());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setStatus(Station $station): void {
		$status = 1;

		$this->assertSame($station, $station->setStatus($status));
		$this->assertSame($status, $station->getStatus());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function getLast(Station $station): void {
		$this->assertSame(0.0, $station->getLastValue());
	}

	/**
	 * @test
	 * @depends construct
	 * @param Station $station
	 */
	public function setLast(Station $station): void {
		$last = 25.99;

		$this->assertSame($station, $station->setLastValue($last));
		$this->assertSame($last, $station->getLastValue());
	}
}
