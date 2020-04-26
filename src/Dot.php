<?php declare(strict_types=1);

namespace Noj\Dot;

use Noj\Dot\Exception\InvalidMethodException;
use Noj\Dot\Parser\Node;
use Noj\Dot\Parser\Parser;
use Noj\Dot\Parser\Segment;

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
		$parser = new Parser();
		$nodeList = $parser->parse($this->data, $path);

		$nodes = $nodeList->getLeafNodes();
		$values = array_map(function (Node $node) {
			try {
				return $node->accessValue();
			} catch (InvalidMethodException $e) {
				return null;
			}
		}, $nodes);

		return !$parser->branched ? $values[0] : $values;
	}

	public function has(string $path): bool
	{
		return $this->get($path) !== null;
	}

	public function push(string $path, $value)
	{
		$parser = new Parser();
		$nodeList = $parser->parse($this->data, $path);

		foreach ($nodeList->getLeafNodes() as $node) {
			$array = &$node->accessValue();
			$array[] = $value;
		}
	}

	public function set($paths, $value = null)
	{
		if (is_array($paths)) {
			foreach ($paths as $path => $pathValue) {
				$this->set($path, $pathValue);
			}
			return;
		}

		$nodeList = (new Parser(true))->parse($this->data, $paths);

		foreach ($nodeList->getLeafNodes() as $node) {
			if (is_array($value) && substr($node->segment->key, -1) === '*') {
				$name = substr($node->segment->key, 0, -1);
				$method = $node->withSegment(new Segment($name))->getMethod();
				foreach ($value as $param) {
					$method->invoke($node->item, $param);
				}
				continue;
			}

			if ($method = $node->getMethod()) {
				$method->invoke($node->item, $value);
				continue;
			}

			if ($node->targetsAllArrayKeys()) {
				foreach ($node->item as &$item) {
					$item = $value;
				}
				continue;
			}

			$currentValue = &$node->accessValue(true);
			$currentValue = $value;
		}
	}
}
