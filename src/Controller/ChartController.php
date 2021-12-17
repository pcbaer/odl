<?php
declare(strict_types = 1);
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Dosage\StationData;
use App\Entity\Station;
use App\Repository\StationRepository;

class ChartController extends AbstractController {

	private const STATION = '053820081';

	private const DAYS = 10;

	protected Station $station;

	protected \DateTimeInterface $from;

	/**
	 * @throws \Exception
	 */
	public function __construct(StationRepository $stationRepository, protected StationData $stationData) {
		$this->station = $stationRepository->findByOdlId(self::STATION);
		$this->from = new \DateTime();
		$this->from->sub(new \DateInterval('P' . self::DAYS . 'D'));
		$this->stationData->setStation($this->station)->setFrom($this->from);
	}

	/**
	 * @Route("/", name="index")
	 */
	public function index(): Response {
		return $this->render('chart/index.html.twig');
	}

	/**
	 * @Route("/data", name="data")
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function data(): JsonResponse {
		return $this->json([
			'data'       => $this->stationData->fetch(),
			'gammascout' => $this->stationData->getGammascoutData(),
			'label'      => $this->station->getZip() . ' ' . $this->station->getCity()
		]);
	}
}
