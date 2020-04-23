<?php declare(strict_types=1);

namespace Fabrica\Dot;

class Dot
{
	private $parser;
	private $data;

	public function __construct(&$data)
	{
		$this->parser = new Parser();
		$this->data = &$data;
	}

	public static function from(&$data): self
	{
		return new self($data);
	}

	public function get(string $path)
	{
		$result = $this->parser->parse($this->data, $path);
		return $this->getValueFromResult($result);
	}

	public function getValueFromResult($result)
	{
		if (is_array($result)) {
			return array_map([$this, 'getValueFromResult'], $result);
		}

		return $this->parser->accessProperty($result->item, $result->key);
	}

	public function set(string $path, $value)
	{
		$result = $this->parser->parse($this->data, $path);
		$this->setValueFromResult($result, $value);
	}

	public function setValueFromResult($result, $value)
	{
		if (is_array($result)) {
			foreach ($result as $r) {
				$this->setValueFromResult($r, $value);
			}
			return;
		}

		if (is_object($result->item) && $this->parser->isMethodCall($result->key)) {
			$this->parser->callMethod($result->item, $result->key, $value);
			return;
		}

		if ($result->key === '*') {
			foreach ($result->item as &$item) {
				$item = $value;
			}
			return;
		}

		$r = &$this->parser->accessProperty($result->item, $result->key);
		$r = $value;
	}
}
