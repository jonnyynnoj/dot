<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Dot;
use PHPUnit\Framework\TestCase;

class PushTest extends TestCase
{
	/** @test */
	public function it_should_push_onto_array()
	{
		$data = ['items' => ['item1', 'item2']];
		(new Dot($data))->push('items', 'item3');

		self::assertEquals(['item1', 'item2', 'item3'], $data['items']);
	}

	/** @test */
	public function it_can_push_with_wildcard()
	{
		$users = [
			['items' => []],
			['items' => ['item1']],
		];

		(new Dot($users))->push('*.items', 'item2');

		self::assertEquals(['item2'], $users[0]['items']);
		self::assertEquals(['item1', 'item2'], $users[1]['items']);
	}

	/** @test */
	public function it_does_nothing_if_array_doesnt_exist()
	{
		$data = [];
		(new Dot($data))->push('user.items', 'item');

		self::assertEmpty($data);
	}
}
