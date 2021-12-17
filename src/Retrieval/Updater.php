<?php
declare(strict_types = 1);
namespace App\Retrieval;

use Doctrine\ORM\EntityManagerInterface;

use App\Dosage\StationData;
use App\Entity\Station;
use App\Repository\StationRepository;

class Updater
{
	/**
	 * @var Station[]
	 */
	protected array $stations = [];

	public function __construct(protected Fetcher $fetcher, protected StationData $stationData,
		                        protected StationRepository $stationRepository, protected EntityManagerInterface $entityManager) {
	}

	public function getStations(): array {
		return $this->stations;
	}

	/**
	 * @return void
	 * @throws \Throwable
	 */
	public function updateStations(): void {
		$this->stations = [];
		$sites          = $this->fetcher->getSiteList();
		$stations       = $this->getFeatures($sites);

		foreach ($stations as $data) {
			$odlId   = $this->getValue($data, 'kenn');
			$station = $this->stationRepository->findByOdlId($odlId);
			if (!$station) {
				$station = new Station();
				$station->setOdlId($odlId);
			}

			$station->setOdlId2($this->getValue($data, 'id'));
			$station->setZip($this->getValue($data, 'plz'));
			$station->setCity($this->getValue($data, 'name'));
			$station->setStatus($this->getValue($data, 'site_status'));
			$station->setStatusText($this->getValue($data, 'site_status_text'));
			$station->setKid($this->getValue($data, 'kid'));
			$station->setAltitude($this->getValue($data, 'height_above_sea'));
			$station->setLongitude($this->getValue($data, 'longitude'));
			$station->setLatitude($this->getValue($data, 'latitude'));
			$timestamp = $this->getValue($data, 'start_measure');
			if (is_string($timestamp)) {
				$dateTime = $this->getDateTime($timestamp);
				if ($dateTime) {
					$station->setLastTimestamp($dateTime);
				}
			}
			$last = $this->getValue($data, 'value');
			if (is_float($last) && $last > 0.0) {
				$station->setLastValue($last);
			}
			$station->setUnit($this->getValue($data, 'unit'));
			$station->setIsValidated((bool)$this->getValue($data, 'validated'));
			$station->setNuclide($this->getValue($data, 'nuclide'));
			$station->setDuration($this->getValue($data, 'duration'));

			$this->stations[] = $station;
			$this->entityManager->persist($station);
		}
		$this->entityManager->flush();;
	}

	/**
	 * @throws \Throwable
	 */
	public function updateMeasurements(Station $station): array {
		$measurements = [];

		$last = $this->stationData->setStation($station)->fetchLastTimestamp();
		if ($last) {
			$last = \DateTime::createFromInterface($last)->add(new \DateInterval('PT1H'));
		} else {
			$last = new \DateTime();
			$last->sub(new \DateInterval('P30D'))->setTime(0, 0);
		}
		$filter = new Filter($last);

		$odlId = $station->getOdlId();
		$json  = $this->fetcher->getMeasurements($odlId, $filter);
		$data  = $this->getFeatures($json);
		foreach ($data as $values) {
			$this->validate($values, 'kenn', $odlId);
			$this->validate($values, 'unit', 'µSv/h');
			$this->validate($values, 'duration', '1h');
			$time  = $this->getValue($values, 'start_measure');
			$value = $this->getValue($values, 'value');
			if (is_string($time) && is_float($value) && $value > 0.0) {
				$timestamp = $this->getDateTime($time);
				if ($timestamp) {
					$measurements[$timestamp->format('Y-m-d H:i:s')] = $value;
				}
			}
		}

		if (!empty($measurements)) {
			$this->stationData->updateMeasurements($station, $measurements);
		}
		return $measurements;
	}

	protected function getFeatures(string $json): array {
		$data = json_decode($json, true);
		$this->validateIsArray($data);
		$this->validate($data, 'type', 'FeatureCollection');
		$this->validateIsArray($data, 'features');

		$features = [];
		foreach ($data['features'] as $feature) {
			$this->validateIsArray($feature);
			$properties = $this->getProperties($feature);
			$features[] = $properties;
		}
		return $features;
	}

	protected function validate(array $data, int|string $key, string $value): void {
		if (!array_key_exists($key, $data) || $data[$key] !== $value) {
			throw new \RuntimeException();
		}
	}

	protected function validateIsArray(mixed $data, ?string $key = null): void {
		if (!is_array($data)) {
			throw new \RuntimeException();
		}
		if ($key && !array_key_exists($key, $data)) {
			throw new \RuntimeException();
		}
	}

	protected function getProperties(array $feature): array {
		$this->validate($feature, 'type', 'Feature');
		$this->validate($feature, 'geometry_name', 'geom');
		$this->validateIsArray($feature, 'geometry');
		$geometry = $this->getValue($feature, 'geometry');
		$this->validate($geometry, 'type', 'Point');
		$this->validateIsArray($geometry, 'coordinates');
		$coordinates = $this->getValue($geometry, 'coordinates');
		$longitude = $this->getValue($coordinates, 0);
		$latitude = $this->getValue($coordinates, 1);
		$this->validateIsArray($feature, 'properties');
		$properties = $this->getValue($feature, 'properties');
		$properties['latitude'] = $latitude;
		$properties['longitude'] = $longitude;
		return $properties;
	}

	protected function getValue(array $data, int|string $key): mixed {
		if (!array_key_exists($key, $data)) {
			throw new \RuntimeException();
		}
		return $data[$key];
	}

	protected function getDateTime(string $timestamp): ?\DateTime {
		if (strlen($timestamp) === 20) {
			$dateTime = substr($timestamp, 0, 10) . ' ' . substr($timestamp, 11, 5) . ':00';
			return \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
		}
		return null;
	}
}
