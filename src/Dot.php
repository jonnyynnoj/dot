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
		$return = $this->parse($this->data, $path);
		$item = &$return[0];
		$key = $return[1];

		return $this->getProperty($item, $key);
	}

	public function set(string $path, $value)
	{
		$return = $this->parse($this->data, $path);
		$item = &$return[0];
		$key = $return[1];

		$property = &$this->getProperty($item, $key);
		$property = $value;
	}

	private function parse(&$data, string $path)
	{
		$segments = explode('.', $path);
		return $this->traverse($data, $segments);
	}

	private function traverse(&$data, array $segments): array
	{
		$key = array_shift($segments);

		if (count($segments) === 0) {
			return [&$data, $key];
		}

		$data = &$this->getProperty($data, $key);
		return $this->traverse($data, $segments);
	}

	private function &getProperty(&$item, $key)
	{
		if (is_array($item)) {
			return $item[$key];
		}

		if (is_object($item)) {
			if (strpos($key, '@') === 0) {
				$result = $this->handleMethodCall($item, $key);
				return $result;
			}

			return $item->$key;
		}
	}

	private function handleMethodCall($item, $key)
	{
		$method = substr($key, 1);
		return $item->$method();
	}
}
