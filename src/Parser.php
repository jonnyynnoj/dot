<?php declare(strict_types=1);

namespace Fabrica\Dot;

class Parser
{
	public function parse(&$data, string $path)
	{
		$segments = explode('.', $path);
		return $this->traverse($data, $segments);
	}

	/**
	 * @return Result|Result[]
	 */
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

	public function &accessProperty(&$item, $key)
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

	public function callMethod($item, $key, $value = null)
	{
		if (is_array($value) && substr($key, -1) === '*') {
			array_map(function ($param) use ($item, $key) {
				$this->callMethod($item, substr($key, 0, -1), $param);
			}, $value);
			return null;
		}

		$method = substr($key, 1);
		return $item->$method($value);
	}

	public function isMethodCall(string $key): bool
	{
		return strpos($key, '@') === 0;
	}

	private function isArrayLike($value): bool
	{
		return is_array($value) || $value instanceof \Traversable;
	}
}
