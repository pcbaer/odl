<?php
declare(strict_types = 1);
namespace App\Controller;

use App\Configuration\Color;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Dosage\StationData;
use App\Repository\StationRepository;

class ChartController extends AbstractController
{
	protected array $stations = [];

	protected array $colors = [];

	protected \DateTimeInterface $from;

	/**
	 * @throws \Exception
	 */
	public function __construct(ContainerBagInterface $config, StationRepository $stationRepository,
								protected StationData $stationData) {
		foreach (explode(',', $config->get('odl.chart.stations')) as $city) {
			$this->stations[] = $stationRepository->findOneBy(['city' => trim($city)]);
		}
		foreach (explode(',', $config->get('odl.chart.colors')) as $name) {
			$color          = new Color($name);
			$this->colors[] = (string)$color;
		}
		if (count($this->colors) <= count($this->stations)) {
			throw new \RuntimeException('You must define at least ' . (count($this->stations) + 1) . ' colors.');
		}
		$this->from = new \DateTime();
		$this->from->sub(new \DateInterval('P' . (int)$config->get('odl.chart.days') . 'D'));
		$this->stationData->setFrom($this->from);
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
		$data   = [];
		$labels = [];
		foreach ($this->stations as $station) {
			$data[]   = $this->stationData->setStation($station)->fetch();
			$labels[] = $station->getZip() . ' ' . $station->getCity();
		}
		return $this->json([
			'data'       => $data,
			'gammascout' => $this->stationData->getGammascoutData(),
			'labels'     => $labels,
			'colors'     => $this->colors
		]);
	}
}
