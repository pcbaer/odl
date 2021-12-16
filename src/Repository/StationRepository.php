<?php
declare(strict_types = 1);
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Station;

/**
 * @method Station|null find($id, $lockMode = null, $lockVersion = null)
 * @method Station|null findOneBy(array $criteria, array $orderBy = null)
 * @method Station[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StationRepository extends ServiceEntityRepository {

	/**
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Station::class);
	}

	/**
	 * @return Station[]
	 */
	public function findAll(): array {
		$builder = $this->createQueryBuilder('s');
		$query   = $builder->orderBy('s.zip')->getQuery();
		return $query->getResult();
	}

	/**
	 * @param string $odlId
	 * @return Station|null
	 */
	public function findByOdlId(string $odlId): ?Station {
		$builder = $this->createQueryBuilder('s');
		$query   = $builder->andWhere('s.odlId = :odlId')->setParameter('odlId', $odlId)->getQuery();
		return $query->getOneOrNullResult();
	}

	/**
	 * @param string[] $odlIds
	 * @return Station[]
	 */
	public function findByOdlIds(array $odlIds): array {
		$builder = $this->createQueryBuilder('s');
		$query   = $builder->andWhere($builder->expr()->in('s.odlId', $odlIds))->orderBy('s.zip')->getQuery();
		return $query->getResult();
	}
}
