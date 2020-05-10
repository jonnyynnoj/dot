<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\push;

class PushTest extends TestCase
{
	/** @test */
	public function it_should_push_onto_array()
	{
		$data = ['items' => ['item1', 'item2']];
		push($data, 'items', 'item3');

		self::assertEquals(['item1', 'item2', 'item3'], $data['items']);
	}

	/** @test */
	public function it_can_push_with_wildcard()
	{
		$users = [
			['items' => []],
			['items' => ['item1']],
		];

		push($users, '*.items', 'item2');

		self::assertEquals(['item2'], $users[0]['items']);
		self::assertEquals(['item1', 'item2'], $users[1]['items']);
	}

	/** @test */
	public function it_does_nothing_if_array_doesnt_exist()
	{
		$data = [];
		push($data, 'user.items', 'item');

		self::assertEmpty($data);
	}
}
