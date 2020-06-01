<?php
declare(strict_types = 1);
namespace App\Tests\Retrieval;

use HallerbachIT\Testing\BaseTest;

use App\Retrieval\Fetcher;
use App\Tests\HttpClientMock;
use App\Tests\ResponseMock;

class FetcherTest extends BaseTest
{
	/**
	 * @var HttpClientMock
	 */
	protected static $client;

	/**
	 * Initialize mock objects.
	 */
	public static function setUpBeforeClass(): void {
		self::$client = new HttpClientMock();
		$content = self::getContent(Fetcher::BASE_FILE);
		self::$client->add(new ResponseMock($content), Fetcher::URL . '/' . Fetcher::BASE_FILE);
		$content = self::getContent('064110003.json');
		self::$client->add(new ResponseMock($content), Fetcher::URL . '/064110003.json');
		$content = self::getContent('092751221.json');
		self::$client->add(new ResponseMock($content), Fetcher::URL . '/092751221.json');
		$content = self::getContent('120671201.json');
		self::$client->add(new ResponseMock($content), Fetcher::URL . '/120671201.json');
	}

	/**
	 * @param string $fileName
	 * @return string
	 */
	protected static function getContent(string $fileName): string {
		$path = __DIR__ . '/../data/' . $fileName;
		return is_file($path) ? file_get_contents($path) : '';
	}

	/**
	 * @test
	 * @return Fetcher
	 */
	public function construct(): Fetcher {
		$fetcher = new Fetcher('', '');

		$this->assertNotNull($fetcher);

		return $fetcher;
	}

	/**
	 * @test
	 * @depends construct
	 * @param Fetcher $fetcher
	 * @return Fetcher
	 */
	public function setClient(Fetcher $fetcher): Fetcher {
		$this->assertSame($fetcher, $fetcher->setClient(self::$client));

		return $fetcher;
	}

	/**
	 * @test
	 * @depends setClient
	 * @param Fetcher $fetcher
	 */
	public function getBaseFile(Fetcher $fetcher): void {
		$content = $fetcher->getBaseFile();

		$this->assertSame(self::getContent(Fetcher::BASE_FILE), $content);
		$this->assertStringContainsString('Darmstadt', $content);
	}

	/**
	 * @test
	 * @depends setClient
	 * @param Fetcher $fetcher
	 * @throws \Symfony\Contracts\HttpClient\Exception\ExceptionInterface
	 */
	public function getStation(Fetcher $fetcher): void {
		$content = $fetcher->getStation('120671201');

		$this->assertSame(self::getContent('120671201.json'), $content);
		$this->assertStringContainsString('Eisenh\u00fcttenstadt', $content);
	}
}
