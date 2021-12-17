<?php
declare( strict_types =1 );
namespace App\Dosage;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Station;

class StationData
{
	protected ?Station $station = null;

	protected ?\DateTimeInterface $from = null;

	public function __construct(protected EntityManagerInterface $entityManager) {
	}

	public function setStation(Station $station): self {
		$this->station = $station;
		return $this;
	}

	public function setFrom(?\DateTimeInterface $from): self {
		$this->from = $from;
		return $this;
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function fetch(): array {
		$query = $this->entityManager->getConnection()->createQueryBuilder();
		$query->select('time AS t', 'dosage AS y')->from('measurement');
		if ($this->station) {
			$query->andWhere('station_id = ' . $this->station->getId());
		}
		if ($this->from) {
			$query->andWhere("time >= '" . $this->from->format('Y-m-d') . "'");
		}
		return $query->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function fetchLastTimestamp(): ?\DateTimeInterface {
		$query = $this->entityManager->getConnection()->createQueryBuilder();
		$query->select('MAX(time)')->from('measurement');
		if ($this->station) {
			$query->andWhere('station_id = ' . $this->station->getId());
		}
		$query->andWhere('dosage > 0.0');
		$last = $query->executeQuery()->fetchFirstColumn();
		if (!empty($last)) {
			$last = $last[0];
			if ($last) {
				return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $last);
			}
		}
		return null;
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function fetchLastMeasurement(): array {
		$query = $this->entityManager->getConnection()->createQueryBuilder();
		$query->select('time, dosage')->from('measurement');
		if ($this->station) {
			$query->andWhere('station_id = ' . $this->station->getId());
		}
		$query->orderBy('time', 'DESC')->setMaxResults(1);
		return $query->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function updateMeasurements(Station $station, array $measurements): void {
		$connection = $this->entityManager->getConnection();
		$connection->beginTransaction();
		$id = $station->getId();
		try {
			foreach ($measurements as $time => $dosage) {
				try {
					$connection->insert('measurement', [
						'station_id' => $id,
						'time'       => $time,
						'dosage'     => $dosage
					]);
				} catch (UniqueConstraintViolationException) {
					$criteria = ['station_id' => $id, 'time' => $time];
					$connection->update('measurement', ['dosage' => $dosage], $criteria);
				}
			}
			$connection->commit();
		} catch (\Throwable) {
			$connection->rollBack();
		}
	}

	public function getGammascoutData(): array {
		$query = $this->entityManager->getConnection()->createQueryBuilder();
		$query->select('time AS t', 'ROUND(dosage, 3) AS y')->from('gammascout');
		if ($this->from) {
			$query->andWhere("time >= '" . $this->from->format('Y-m-d') . "'");
		}
		return $query->executeQuery()->fetchAllAssociative();
	}
}
