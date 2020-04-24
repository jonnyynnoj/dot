<?php declare(strict_types=1);

namespace Noj\Dot;

class Dot
{
	private $data;

	public function __construct(&$data)
	{
		$this->data = &$data;
	}

	public static function from(&$data): self
	{
		return new self($data);
	}

	public function get(string $path)
	{
		$node = (new Parser())->parse($this->data, $path);
		return $this->recursiveGet($node);
	}

	/**
	 * @param Node|Node[] $node
	 *
	 * @return mixed
	 */
	private function recursiveGet($node)
	{
		if (is_array($node)) {
			return array_map([$this, 'recursiveGet'], $node);
		}

		return $node->accessValue();
	}

	public function set($paths, $value = null)
	{
		if (is_array($paths)) {
			foreach ($paths as $path => $pathValue) {
				$this->set($path, $pathValue);
			}
			return;
		}

		$node = (new Parser(true))->parse($this->data, $paths);
		$this->recursiveSet($node, $value);
	}

	/**
	 * @param Node|Node[] $node
	 * @param mixed       $value
	 *
	 * @throws DotException
	 */
	private function recursiveSet($node, $value)
	{
		if (is_array($node)) {
			foreach ($node as $n) {
				$this->recursiveSet($n, $value);
			}
			return;
		}

		if (is_array($value) && substr($node->key, -1) === '*') {
			foreach ($value as $param) {
				$name = substr($node->key, 0, -1);
				$this->recursiveSet($node->withKey($name), $param);
			}
			return;
		}

		if ($node->isMethodCall()) {
			if ($method = $node->getMethod()) {
				$method->invoke($node->item, $value);
				return;
			}
			throw DotException::fromInvalidSetter($node);
		}

		if ($node->isBranchable()) {
			foreach ($node->item as &$item) {
				$item = $value;
			}
			return;
		}

		$r = &$node->accessValue();
		$r = $value;
	}
}
