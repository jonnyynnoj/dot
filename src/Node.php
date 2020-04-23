<?php declare(strict_types=1);

namespace Noj\Dot;

class Node
{
	public $item;
	public $key;

	public function __construct(&$item, $key)
	{
		$this->item = &$item;
		$this->key = $key;
	}

	public function &accessValue()
	{
		if ($this->isMethodCall()) {
			$return = $this->callMethod();
			return $return;
		}

		if ($this->isArrayLike()) {
			if ($this->isBranch()) {
				return $this->item;
			}

			return $this->item[$this->key];
		}

		if (is_object($this->item)) {
			return $this->item->{$this->key};
		}

		return null;
	}

	public function callMethod($value = null)
	{
		if (is_array($value) && substr($this->key, -1) === '*') {
			foreach ($value as $param) {
				$method = substr($this->key, 0, -1);
				$this->withMethod($method)->callMethod($param);
			}
			return null;
		}

		$method = substr($this->key, 1);
		return $this->item->$method($value);
	}

	public function isMethodCall(): bool
	{
		return is_object($this->item) && strpos($this->key, '@') === 0;
	}

	public function isBranch(): bool
	{
		return $this->key === '*';
	}

	public function isBranchable(): bool
	{
		return $this->isBranch() && $this->isArrayLike();
	}

	public function withMethod(string $method): Node
	{
		return new self($this->item, $method);
	}

	public function isArrayLike(): bool
	{
		return is_array($this->item) || $this->item instanceof \Traversable;
	}
}
