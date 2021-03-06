<?php
declare(strict_types = 1);
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StationRepository")
 */
class Station {

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="smallint")
	 * @var int
	 */
	private $id = 0;

	/**
	 * @ORM\Column(type="string", length=9)
	 * @var string
	 */
	private $odlId = '';

	/**
	 * @ORM\Column(type="string", length=5)
	 * @var string
	 */
	private $zip = '';

	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	private $city = '';

	/**
	 * @ORM\Column(type="smallint")
	 * @var int
	 */
	private $kid = 0;

	/**
	 * @ORM\Column(type="smallint")
	 * @var int
	 */
	private $altitude = 0;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	private $latitude = 0.0;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	private $longitude = 0.0;

	/**
	 * @ORM\Column(type="smallint")
	 * @var int
	 */
	private $status = 0;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	private $last = 0.0;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getOdlId(): string {
		return $this->odlId;
	}

	/**
	 * @param string $odlId
	 * @return self
	 */
	public function setOdlId(string $odlId): self {
		$this->odlId = $odlId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getZip(): string {
		return $this->zip;
	}

	/**
	 * @param string $zip
	 * @return self
	 */
	public function setZip(string $zip): self {
		$this->zip = $zip;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCity(): string {
		return $this->city;
	}

	/**
	 * @param string $city
	 * @return self
	 */
	public function setCity(string $city): self {
		$this->city = $city;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getKid(): int {
		return $this->kid;
	}

	/**
	 * @param int $kid
	 * @return self
	 */
	public function setKid(int $kid): self {
		$this->kid = $kid;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getAltitude(): int {
		return $this->altitude;
	}

	/**
	 * @param int $altitude
	 * @return self
	 */
	public function setAltitude(int $altitude): self {
		$this->altitude = $altitude;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getLatitude(): float {
		return $this->latitude;
	}

	/**
	 * @param float $latitude
	 * @return self
	 */
	public function setLatitude(float $latitude): self {
		$this->latitude = $latitude;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getLongitude(): float {
		return $this->longitude;
	}

	/**
	 * @param float $longitude
	 * @return self
	 */
	public function setLongitude(float $longitude): self {
		$this->longitude = $longitude;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getStatus(): int {
		return $this->status;
	}

	/**
	 * @param int $status
	 * @return self
	 */
	public function setStatus(int $status): self {
		$this->status = $status;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getLast(): float {
		return $this->last;
	}

	/**
	 * @param float $last
	 * @return self
	 */
	public function setLast(float $last): self {
		$this->last = $last;
		return $this;
	}
}
