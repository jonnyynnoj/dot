<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\count;

class CountTest extends TestCase
{
	use Dataset;

	/** @test */
	public function it_can_count_top_level()
	{
		$count = count($this->data, 'groups');
		self::assertEquals(2, $count);
	}

	/** @test */
	public function it_can_count_nested()
	{
		$count = count($this->data, 'groups.0.users');
		self::assertEquals(3, $count);
	}

	/** @test */
	public function it_can_count_with_wildcard()
	{
		$count = count($this->data, 'groups.*.users');
		self::assertEquals(4, $count);
	}

	/** @test */
	public function it_handles_invalid_path()
	{
		$count = count($this->data, 'foo');
		self::assertEquals(0, $count);

		$count = count($this->data, 'groups.*.users.*.doesnt.*.exist');
		self::assertEquals(0, $count);
	}
}
