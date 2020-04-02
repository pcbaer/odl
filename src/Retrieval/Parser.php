<?php
declare(strict_types = 1);
namespace App\Retrieval;

use App\Entity\Station;
use App\Repository\StationRepository;

class Parser {

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var StationRepository
	 */
	private $stationRepository;

	/**
	 * @var array
	 */
	private $stations = [];

	/**
	 * @var int
	 */
	private $index = 0;

	/**
	 * @var int
	 */
	private $count = 0;

	/**
	 * @param string $directory
	 * @param StationRepository $stationRepository
	 */
	public function __construct(string $directory, StationRepository $stationRepository) {
		$this->directory         = $directory;
		$this->stationRepository = $stationRepository;
	}

	/**
	 * @return Station[]
	 */
	public function getStations(): \Traversable {
		if ($this->count <= 0) {
			$this->readStations();
		}

		for ($this->index = 0; $this->index < $this->count; $this->index++) {
			$data    = $this->stations[$this->index];
			$station = $this->stationRepository->findByOdlId($data['kenn']);
			if (!$station) {
				$station = new Station();
				$station->setLast(0.0);
			}
			$station->setCity($data['ort'])->setOdlId($data['kenn'])->setZip($data['plz'])->setStatus($data['status']);
			$station->setKid($data['kid'])->setAltitude($data['hoehe'])->setLongitude($data['lon']);
			$station->setLatitude($data['lat']);
			if (isset($data['mw'])) {
				$station->setLast($data['mw']);
			}

			yield $station;
		}
	}

	/**
	 * @param string $odlId
	 * @return array
	 */
	public function getMeasurements(string $odlId): array {
		$measurements = [];

		$json = file_get_contents($this->directory . DIRECTORY_SEPARATOR . $odlId . '.json');
		if ($json) {
			$values = json_decode($json, true);
			if (is_array($values) && isset($values['mw1h']) && is_array($values['mw1h'])) {
				$t  = $values['mw1h']['t'] ?? [];
				$mw = $values['mw1h']['mw'] ?? [];
				$ps = $values['mw1h']['mw'] ?? [];
				$n  = count($t);
				if (count($mw) === $n && count($ps) === $n) {
					for ($i = 0; $i < $n; $i++) {
						$measurements[$t[$i]] = ['mw' => $mw[$i] ?? 0.0, 'ps' => $ps[$i] ?? 0, 'r' => 0.0];
					}
				}

				$tr = $values['mw1h']['tr'] ?? [];
				$r  = $values['mw1h']['r'] ?? [];
				$n  = count($tr);
				if (count($r) === $n) {
					for ($i = 0; $i < $n; $i++) {
						$t = $tr[$i];
						if (isset($measurements[$t])) {
							$measurements[$t]['r'] = $r[$i] ?? 0.0;
						} else {
							$measurements[$t] = ['mw' => 0.0, 'ps' => 0, 'r' => $r[$i] ?? 0.0];
						}
					}
				}
			}
		}

		return $measurements;
	}

	/**
	 * Read the stations file.
	 */
	private function readStations(): void {
		$json = file_get_contents($this->directory . DIRECTORY_SEPARATOR . Fetcher::BASE_FILE);
		if ($json) {
			$stations = json_decode($json, true);
			if (is_array($stations)) {
				$this->stations = array_values(($stations));
				$this->count    = count($this->stations);
			}
		}
	}
}
