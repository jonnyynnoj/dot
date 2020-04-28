<?php declare(strict_types=1);

namespace Noj\Dot\Test\Stubs;

class Collection implements \ArrayAccess
{
	private $items;

	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	public function offsetExists($offset)
	{
		return isset($this->items[$offset]);
	}

	public function &offsetGet($offset)
	{
		return $this->items[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->items[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}
}
