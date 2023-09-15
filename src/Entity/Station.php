<?php
declare(strict_types = 1);
namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

use App\Repository\StationRepository;

#[Entity(repositoryClass: StationRepository::class)]
class Station implements \Stringable
{
	#[Column(type: 'smallint')]
	#[GeneratedValue]
	#[Id]
	protected int $id = 0;

	#[Column(length: 9)]
	protected string $odlId = '';

	#[Column(name: 'odl_id_2', length: 7)]
	protected string $odlId2 = '';

	#[Column(length: 5)]
	protected string $zip = '';

	#[Column(length: 255)]
	protected string $city = '';

	#[Column(type: 'smallint')]
	protected int $kid = 0;

	#[Column(type: 'smallint')]
	protected int $altitude = 0;

	#[Column]
	protected float $latitude = 0.0;

	#[Column]
	protected float $longitude = 0.0;

	#[Column(type: 'smallint')]
	protected int $status = 0;

	#[Column(length: 255)]
	protected string $statusText = '';

	#[Column]
	protected ?\DateTime $lastTimestamp = null;

	#[Column]
	protected float $lastValue = 0.0;

	#[Column(length: 8)]
	protected string $unit = '';

	#[Column(length: 10)]
	protected string $duration = '';

	#[Column]
	protected bool $isValidated = false;

	#[Column(length: 255)]
	protected string $nuclide = '';

	public function __toString(): string {
		return $this->city . ' (' . $this->odlId . ')';
	}

	public function getId(): int {
		return $this->id;
	}

	public function getOdlId(): string {
		return $this->odlId;
	}

	public function setOdlId(string $odlId): self {
		$this->odlId = $odlId;
		return $this;
	}

	public function getOdlId2(): string {
		return $this->odlId2;
	}

	public function setOdlId2(string $odlId2): self {
		$this->odlId2 = $odlId2;
		return $this;
	}

	public function getZip(): string {
		return $this->zip;
	}

	public function setZip(string $zip): self {
		$this->zip = $zip;
		return $this;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function setCity(string $city): self {
		$this->city = $city;
		return $this;
	}

	public function getKid(): int {
		return $this->kid;
	}

	public function setKid(int $kid): self {
		$this->kid = $kid;
		return $this;
	}

	public function getAltitude(): int {
		return $this->altitude;
	}

	public function setAltitude(int $altitude): self {
		$this->altitude = $altitude;
		return $this;
	}

	public function getLatitude(): float {
		return $this->latitude;
	}

	public function setLatitude(float $latitude): self {
		$this->latitude = $latitude;
		return $this;
	}

	public function getLongitude(): float {
		return $this->longitude;
	}

	public function setLongitude(float $longitude): self {
		$this->longitude = $longitude;
		return $this;
	}

	public function getStatus(): int {
		return $this->status;
	}

	public function setStatus(int $status): self {
		$this->status = $status;
		return $this;
	}

	public function getStatusText(): string {
		return $this->statusText;
	}

	public function setStatusText(string $statusText): self {
		$this->statusText = $statusText;
		return $this;
	}

	public function getLastTimestamp(): ?\DateTime {
		return $this->lastTimestamp;
	}

	public function setLastTimestamp(\DateTime $dateTime): self {
		$this->lastTimestamp = $dateTime;
		return $this;
	}

	public function getLastValue(): float {
		return $this->lastValue;
	}

	public function setLastValue(float $lastValue): self {
		$this->lastValue = $lastValue;
		return $this;
	}

	public function getUnit(): string {
		return $this->unit;
	}

	public function setUnit(string $unit): self {
		$this->unit = $unit;
		return $this;
	}

	public function getDuration(): string {
		return $this->duration;
	}

	public function setDuration(string $duration): self {
		$this->duration = $duration;
		return $this;
	}

	public function isValidated(): bool {
		return $this->isValidated;
	}

	public function setIsValidated(bool $isValidated): self {
		$this->isValidated = $isValidated;
		return $this;
	}

	public function getNuclide(): string {
		return $this->nuclide;
	}

	public function setNuclide(string $nuclide): self {
		$this->nuclide = $nuclide;
		return $this;
	}
}
