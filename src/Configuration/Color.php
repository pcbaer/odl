<?php
declare(strict_types = 1);
namespace App\Configuration;

final class Color implements \Stringable
{
	public const BLUE = '6ac0e5';

	public const BROWN = 'bc8d00';

	public const CYAN = '9bffff';

	public const GREY = 'dfe2c9';

	public const GREEN = '7cff7c';

	public const ORANGE = 'f8c435';

	public const PINK = 'eba2bf';

	public const RED = 'ffa366';

	public const ROSE = 'cdb7b5';

	public const YELLOW = 'ffff43';

	private const FALLBACK = '000000';

	private static ?array $cache = null;

	private string $color;

	public function __construct(string $name) {
		self::initCache();
		$this->color = self::$cache[strtoupper($name)] ?? self::FALLBACK;
	}

	public function __toString() {
		return '#' . $this->color;
	}

	private static function initCache(): void {
		if (!self::$cache) {
			self::$cache = [];
			$reflection  = new \ReflectionClass(self::class);
			foreach ($reflection->getConstants() as $name => $color) {
				self::$cache[$name] = $color;
			}
		}
	}
}
