<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Dot;
use PHPUnit\Framework\TestCase;

class ChainTest extends TestCase
{
	use Dataset;

	/** @test */
	public function it_can_find_push_and_select()
	{
		$items = Dot::from($this->data)
			->find('groups.*.users.*.banned', false)
			->push('*.items', 'an item')
			->get('*.items');

		self::assertContains('an item', $this->data['groups'][0]['users'][0]['items']);
		self::assertContains('an item', $items);
	}
}
