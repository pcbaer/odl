<?php
declare( strict_types =1 );
namespace App\Controller;

use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Station;
use App\Repository\StationRepository;

class ChartController extends AbstractController {

	private const STATION = '053820081';

	private const DAYS = 10;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var Station
	 */
	private $station;

	/**
	 * @var \DateTime
	 */
	private $time;

	/**
	 * @param StationRepository $stationRepository
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(StationRepository $stationRepository, EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
		$this->station       = $stationRepository->findByOdlId(self::STATION);
		$this->time          = (new \DateTime())->sub(new \DateInterval('P' . self::DAYS . 'D'));
	}

	/**
	 * @Route("/", name="index")
	 */
	public function index() {
		return $this->render('chart/index.html.twig');
	}

	/**
	 * @Route("/data", name="data")
	 */
	public function data() {
		return $this->json([
			'data'       => $this->getData(),
			'gammascout' => $this->getGammascoutData(),
			'label'      => $this->getLabel()
		]);
	}

	/**
	 * @return array
	 */
	private function getData(): array {
		$query = $this->entityManager->getConnection()->createQueryBuilder();
		$query->select('time AS t', 'dosage AS y')->from('measurement');
		$query->andWhere('station_id = ' . $this->station->getId());
		$query->andWhere("time >= '" . $this->time->format('Y-m-d') . "'");
		return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
	}

	/**
	 * @return array
	 */
	private function getGammascoutData(): array {
		$query = $this->entityManager->getConnection()->createQueryBuilder();
		$query->select('time AS t', 'dosage AS y')->from('gammascout');
		$query->andWhere("time >= '" . $this->time->format('Y-m-d') . "'");
		return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
	}

	/**
	 * @return string
	 */
	private function getLabel(): string {
		return $this->station->getZip() . ' ' . $this->station->getCity();
	}
}
