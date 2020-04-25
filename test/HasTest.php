<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Dot;
use PHPUnit\Framework\TestCase;

class HasTest extends TestCase
{
	/** @test */
	public function it_returns_true_if_path_exists()
	{
		$data = ['foo' => ['bar' => 'baz']];

		$dot = new Dot($data);
		self::assertTrue($dot->has('foo.bar'));
	}

	/** @test */
	public function it_returns_false_if_path_does_not_exist()
	{
		$data = ['foo' => ['bar' => 'baz']];

		$dot = new Dot($data);
		self::assertFalse($dot->has('foo.baz'));
	}

	/** @test */
	public function it_can_check_multidimensional_paths()
	{
		$data = [
			['foo' => true],
			['bar' => true],
			['baz' => true],
		];

		$dot = new Dot($data);
		self::assertTrue($dot->has('*.bar'));
	}
}
