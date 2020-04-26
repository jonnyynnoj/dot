<?php declare(strict_types=1);

namespace Noj\Dot\Parser;

use Noj\Dot\Exception\InvalidMethodException;

class Node
{
	public $item;
	public $key;

	public function __construct(&$item, $key)
	{
		$this->item = &$item;
		$this->key = $key;
	}

	public function withKey(string $key): Node
	{
		return new self($this->item, $key);
	}

	/**
	 * @throws InvalidMethodException
	 * @return mixed|null
	 */
	public function &accessValue($initialiseIfNotSet = false)
	{
		if ($method = $this->getMethod()) {
			$result = $method ? $method->invoke($this->item) : null;
			return $result;
		}

		if (is_object($this->item)) {
			if (!property_exists($this->item, $this->key) && !$initialiseIfNotSet) {
				$result = null;
				return $result;
			}
			return $this->item->{$this->key};
		}

		if ($this->targetsAllArrayKeys()) {
			return $this->item;
		}

		if (!array_key_exists($this->key, $this->item) && !$initialiseIfNotSet) {
			$result = null;
			return $result;
		}

		return $this->item[$this->key];
	}

	public function getMethod()
	{
		if (!$this->isMethodCall()) {
			return null;
		}

		$method = $this->getMethodName();

		if (!is_callable([$this->item, $method])) {
			throw InvalidMethodException::fromNode($this);
		}

		return new \ReflectionMethod($this->item, $method);
	}

	public function isMethodCall(): bool
	{
		return strpos($this->key, '@') === 0;
	}

	public function getMethodName()
	{
		return substr($this->key, 1);
	}

	public function targetsAllArrayKeys(): bool
	{
		return $this->isArrayLike() && $this->key === '*';
	}

	public function isArrayLike(): bool
	{
		return is_array($this->item) || ($this->item instanceof \ArrayAccess && $this->item instanceof \Traversable);
	}
}
