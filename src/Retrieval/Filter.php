<?php
declare(strict_types = 1);
namespace App\Retrieval;

class Filter implements \Stringable
{
	public const FILTER_DATETIME = '<Filter xmlns="http://www.opengis.net/ogc"><PropertyIsBetween>' .
		'<PropertyName>start_measure</PropertyName><LowerBoundary><Literal>%FROM%</Literal></LowerBoundary>' .
		'<UpperBoundary><Literal>%TO%</Literal></UpperBoundary></PropertyIsBetween></Filter>';

	public function __construct(protected \DateTimeInterface $from) {
	}

	public function __toString(): string {
		$from   = $this->timestamp($this->from);
		$to     = $this->timestamp(new \DateTimeImmutable());
		$filter = str_replace('%FROM%', $from, self::FILTER_DATETIME);
		$filter =  str_replace('%TO%', $to, $filter);
		return urlencode($filter);
	}

	protected function timestamp(\DateTimeInterface $dateTime): string {
		return $dateTime->format('Y-m-d') . 'T' . $dateTime->format('H:i:s') . '.000Z';
	}
}
