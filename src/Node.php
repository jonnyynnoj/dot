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
			return $this->callMethod();
		}

		if (is_object($this->item)) {
			return $this->item->{$this->key};
		}

		if (!is_array($this->item) && !($this->item instanceof \ArrayAccess)) {
			throw new \Exception('Not an array or ArrayAccess');
		}

		if ($this->isExpand()) {
			return $this->item;
		}

		return $this->item[$this->key];
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

	public function isExpand(): bool
	{
		return $this->key === '*';
	}

	public function withMethod(string $method)
	{
		return new self($this->item, $method);
	}
}
