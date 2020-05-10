<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\find;

class FindTest extends TestCase
{
	private $data = [];

	protected function setUp()
	{
		$this->data = [
			'groups' => [[
				'users' => [
					(object)[
						'id' => 1,
						'banned' => false
					],
					(object)[
						'id' => 3,
						'banned' => true
					],
					(object)[
						'id' => 4,
						'banned' => true
					],
				]
			], [
				'users' => [
					(object)[
						'id' => 2,
						'banned' => true
					],
				]
			]]
		];
	}

	/** @test */
	public function it_can_find_property_by_value()
	{
		$found = find($this->data, 'groups.*.users.*.banned', true);

		self::assertEquals(
			[
				(object)[
					'id' => 3,
					'banned' => true
				],
				(object)[
					'id' => 4,
					'banned' => true
				],
				(object)[
					'id' => 2,
					'banned' => true
				],
			],
			$found->get()
		);
	}

	/** @test */
	public function it_can_find_property_by_callable()
	{
		$found = find($this->data, 'groups.*.users.*.id', function (int $id) {
			return $id < 3;
		});

		self::assertEquals(
			[
				(object)[
					'id' => 1,
					'banned' => false
				],
				(object)[
					'id' => 2,
					'banned' => true
				],
			],
			$found->get()
		);
	}

	/** @test */
	public function it_can_find_item_by_callable()
	{
		$found = find($this->data, 'groups.*.users.*', function (\stdClass $user) {
			return $user->id < 3 && $user->banned;
		});

		self::assertEquals(
			[
				(object)[
					'id' => 2,
					'banned' => true
				],
			],
			$found->get()
		);
	}

	/** @test */
	public function it_returns_empty_array_if_no_matches()
	{
		$found = find($this->data, 'groups.*.users.*.foo', 'bar');

		self::assertCount(0, $found->get());
	}
}
