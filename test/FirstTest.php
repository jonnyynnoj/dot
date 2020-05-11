<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\first;

class FirstTest extends TestCase
{
	use Dataset;

	/** @test */
	public function it_returns_first_match_by_value()
	{
		$result = first($this->data, 'groups.*.users.*.banned', true)->get();

		self::assertEquals($this->data['groups'][0]['users'][1], $result);
	}

	/** @test */
	public function it_returns_first_match_by_callable()
	{
		$result = first($this->data, 'groups.*.users.*', function (array $user) {
			return $user['id'] > 1;
		})->get();

		self::assertEquals($this->data['groups'][0]['users'][1], $result);
	}

	/** @test */
	public function it_returns_null_if_no_match()
	{
		$result = first($this->data, 'groups.*.users.*.banned', 'foo')->get();

		self::assertNull($result);
	}

	/** @test */
	public function it_returns_first_index()
	{
		$result = first($this->data['groups'][0]['users'])->get();

		self::assertEquals($this->data['groups'][0]['users'][0], $result);
	}
}
