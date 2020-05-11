<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Dot;
use PHPUnit\Framework\TestCase;
use function Noj\Dot\first;

class FirstTest extends TestCase
{
	use Dataset;

	/** @test */
	public function it_returns_first_match_by_value()
	{
		$result = first($this->data, 'groups.*.users.*.banned', true);

		self::assertEquals($this->data['groups'][0]['users'][1], $result);
	}

	/** @test */
	public function it_returns_first_match_by_callable()
	{
		$result = first($this->data, 'groups.*.users.*', function (array $user) {
			return $user['id'] > 1;
		});

		self::assertEquals($this->data['groups'][0]['users'][1], $result);
	}

	/** @test */
	public function it_returns_null_if_no_match()
	{
		$result = first($this->data, 'groups.*.users.*.banned', 'foo');

		self::assertNull($result);
	}

	/** @test */
	public function it_returns_first_index()
	{
		$result = Dot::from($this->data['groups'][0]['users'])->first()->get();

		self::assertEquals($this->data['groups'][0]['users'][0], $result);
	}
}
