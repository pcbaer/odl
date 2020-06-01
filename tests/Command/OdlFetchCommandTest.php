<?php
declare(strict_types = 1);
namespace App\Tests\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use HallerbachIT\Testing\Symfony\EntityManagerTest;

use App\Command\OdlFetchCommand;
use App\Entity\Station;
use App\Repository\StationRepository;
use App\Retrieval\Fetcher;
use App\Tests\HttpClientMock;
use App\Tests\ResponseMock;

class OdlFetchCommandTest extends EntityManagerTest
{
	/**
	 * @var HttpClientMock
	 */
	protected static $client;

	/**
	 * Initialize mock objects.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
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
	 * @return string
	 */
	protected function getDefaultSql(): string {
		return file_get_contents(__DIR__ . '/../data/default.sql');
	}

	/**
	 * @test
	 * @return OdlFetchCommand
	 */
	public function construct(): OdlFetchCommand {
		$fetcher = new Fetcher('', '');
		$fetcher->setClient(self::$client);
		/* @var StationRepository $repository */
		$repository = self::$doctrine->getRepository(Station::class);
		$command    = new OdlFetchCommand($fetcher, $repository, self::$doctrine->getEntityManager());

		$this->assertNotNull($command);

		return $command;
	}

	/**
	 * @test
	 * @depends construct
	 * @param OdlFetchCommand $command
	 */
	public function runMethod(OdlFetchCommand $command): void {
		$this->assertSame(0, $command->run(new ArrayInput([]), new NullOutput()));
	}
}
