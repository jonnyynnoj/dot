<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\has;

class HasTest extends TestCase
{
	/** @test */
	public function it_returns_true_if_path_exists()
	{
		$data = ['foo' => ['bar' => 'baz']];

		self::assertTrue(has($data, 'foo.bar'));
	}

	/** @test */
	public function it_returns_false_if_path_does_not_exist()
	{
		$data = ['foo' => ['bar' => 'baz']];

		self::assertFalse(has($data, 'foo.baz'));
	}

	/** @test */
	public function it_can_check_multidimensional_paths()
	{
		$data = [
			['foo' => true],
			['bar' => true],
			['baz' => true],
		];

		self::assertTrue(has($data, '*.bar'));
	}
}
