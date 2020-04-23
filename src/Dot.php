<?php declare(strict_types=1);

namespace Fabrica\Dot;

class Dot
{
	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public static function from(&$data): self
	{
		return new self($data);
	}

	public function get(string $path)
	{
		$result = $this->parse($this->data, $path);
		return $this->getValueFromResult($result);
	}

	private function getValueFromResult($result)
	{
		if (is_array($result)) {
			return array_map([$this, 'getValueFromResult'], $result);
		}

		return $this->accessProperty($result->item, $result->key);
	}

	public function set(string $path, $value)
	{
		$result = $this->parse($this->data, $path);
		$this->setValueFromResult($result, $value);
	}

	private function setValueFromResult($result, $value)
	{
		if (is_array($result)) {
			foreach ($result as $r) {
				$this->setValueFromResult($r, $value);
			}
			return;
		}

		if (is_object($result->item) && $this->isMethodCall($result->key)) {
			$this->callMethod($result->item, $result->key, $value);
			return;
		}

		if ($result->key === '*') {
			foreach ($result->item as &$item) {
				$item = $value;
			}
			return;
		}

		$r = &$this->accessProperty($result->item, $result->key);
		$r = $value;
	}

	private function parse(&$data, string $path)
	{
		$segments = explode('.', $path);
		return $this->traverse($data, $segments);
	}

	private function traverse(&$data, array $segments)
	{
		$key = array_shift($segments);

		if (count($segments) === 0) {
			return new Result($data, $key);
		}

		if ($key === '*' && $this->isArrayLike($data)) {
			$results = [];
			foreach ($data as &$item) {
				$results[] = $this->traverse($item, $segments);
			}
			return $results;
		}

		$data = &$this->accessProperty($data, $key);
		return $this->traverse($data, $segments);
	}

	private function &accessProperty(&$item, $key)
	{
		if (is_object($item)) {
			if ($this->isMethodCall($key)) {
				$result = $this->callMethod($item, $key);
				return $result;
			}

			return $item->$key;
		}

		if (!is_array($item) && !($item instanceof \ArrayAccess)) {
			throw new \Exception('Not an array or ArrayAccess');
		}

		if ($key === '*') {
			return $item;
		}

		return $item[$key];
	}

	private function isMethodCall(string $key): bool
	{
		return strpos($key, '@') === 0;
	}

	private function callMethod($item, $key, $value = null)
	{
		$method = substr($key, 1);
		return $item->$method($value);
	}

	private function isArrayLike($value)
	{
		return is_array($value) || $value instanceof \Traversable;
	}
}

class Result
{
	public $item;
	public $key;

	public function __construct(&$item, $key)
	{
		$this->item = &$item;
		$this->key = $key;
	}
}
