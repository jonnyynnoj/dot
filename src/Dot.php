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
		$node = $this->parser->parse($this->data, $path);
		return $this->recursiveGet($node);
	}

	/**
	 * @param Node|Node[] $node
	 *
	 * @throws \Exception
	 * @return mixed
	 */
	private function recursiveGet($node)
	{
		if (is_array($node)) {
			return array_map([$this, 'recursiveGet'], $node);
		}

		return $node->accessValue();
	}

	public function set(string $path, $value)
	{
		$node = $this->parser->parse($this->data, $path);
		$this->recursiveSet($node, $value);
	}

	/**
	 * @param Node|Node[] $node
	 * @param mixed       $value
	 *
	 * @throws \Exception
	 */
	private function recursiveSet($node, $value)
	{
		if (is_array($node)) {
			foreach ($node as $n) {
				$this->recursiveSet($n, $value);
			}
			return;
		}

		if ($node->isMethodCall()) {
			$node->callMethod($value);
			return;
		}

		if ($node->isExpand()) {
			foreach ($node->item as &$item) {
				$item = $value;
			}
			return;
		}

		$r = &$node->accessValue();
		$r = $value;
	}
}
