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
			$method = $this->getMethod();
			$result = $method ? $method->invoke($this->item) : null;
			return $result;
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

	public function getMethod()
	{
		$method = substr($this->key, 1);

		if (!is_callable([$this->item, $method])) {
			return null;
		}

		return new \ReflectionMethod($this->item, $method);
	}

	public function isMethodCall(): bool
	{
		return strpos($this->key, '@') === 0;
	}

	public function isBranch(): bool
	{
		return $this->key === '*';
	}

	public function isBranchable(): bool
	{
		return $this->isBranch() && $this->isArrayLike();
	}

	public function withKey(string $key): Node
	{
		return new self($this->item, $key);
	}

	public function isArrayLike(): bool
	{
		return is_array($this->item) || $this->item instanceof \Traversable;
	}
}
