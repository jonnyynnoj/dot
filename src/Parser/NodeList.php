<?php declare(strict_types=1);

namespace Noj\Dot\Parser;

class NodeList
{
	/** @var (self|Node)[] */
	public $items = [];

	/**
	 * @param self|Node $item
	 *
	 * @return self
	 */
	public function add($item): self
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * @return Node[]
	 */
	public function getLeafNodes(): array
	{
		$nodes = array_map(function ($item) {
			return $item instanceof self ? $item->getLeafNodes() : [$item];
		}, $this->items);

		return array_merge([], ...$nodes);
	}
}
