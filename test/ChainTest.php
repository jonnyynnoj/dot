<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\find;

class ChainTest extends TestCase
{
	use Dataset;

	/** @test */
	public function it_can_find_push_and_select()
	{
		$items = find($this->data, 'groups.*.users.*.banned', false)
			->push('*.items', 'an item')
			->get('*.items');

		self::assertContains('an item', $this->data['groups'][0]['users'][0]['items']);
		self::assertContains('an item', $items);
	}
}
