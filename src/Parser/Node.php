<?php declare(strict_types=1);

namespace Noj\Dot\Parser;

use Noj\Dot\Exception\InvalidMethodException;

class Node
{
	public $item;
	public $segment;

	public function __construct(&$item, Segment $segment)
	{
		$this->item = &$item;
		$this->segment = $segment;
	}

	public function withSegment(Segment $segment): Node
	{
		return new self($this->item, $segment);
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

		if ($this->isArrayLike()) {
			if ($this->targetsAllArrayKeys()) {
				return $this->item;
			}

			if (!isset($this->item[$this->segment->key]) && !$initialiseIfNotSet) {
				$result = null;
				return $result;
			}

			return $this->item[$this->segment->key];
		}

		if (!property_exists($this->item, $this->segment->key) && !$initialiseIfNotSet) {
			$result = null;
			return $result;
		}

		return $this->item->{$this->segment->key};
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
		return strpos($this->segment->key, '@') === 0;
	}

	public function getMethodName()
	{
		return substr($this->segment->key, 1);
	}

	public function targetsAllArrayKeys(): bool
	{
		return $this->isIterable() && $this->segment->key === '*';
	}

	public function isArrayLike(): bool
	{
		return is_array($this->item) || $this->item instanceof \ArrayAccess;
	}

	public function isIterable(): bool
	{
		return is_array($this->item) || $this->item instanceof \Traversable;
	}
}
