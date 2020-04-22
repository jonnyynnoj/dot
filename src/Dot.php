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
		list($item, $key) = $this->parse($this->data, $path);

		if (is_array($item)) {
			return $item[$key];
		}

		if (is_object($item)) {
			return $item->$key;
		}
	}

	public function set(string $path, $value)
	{
		$return = $this->parse($this->data, $path);
		$item = &$return[0];
		$key = $return[1];

		if (is_array($item)) {
			$item[$key] = $value;
		}

		if (is_object($item)) {
			$item->$key = $value;
		}
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

		if (is_array($data)) {
			$data = &$data[$key];
		} elseif (is_object($data)) {
			$data = &$data->$key;
		}

		return $this->traverse($data, $segments);
	}
}
