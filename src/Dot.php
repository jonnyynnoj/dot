<?php declare(strict_types=1);

namespace Noj\Dot;

use Closure;
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

	public function count(string $path = null): int
	{
		$values = $this->get($path);
		return is_array($values) ? \count(array_filter($values)) : 0;
	}

	public function find(string $path, $equals): self
	{
		$parser = new Parser();
		$nodeList = $parser->parse($this->data, $path);
		$found = [];

		if (!$equals instanceof Closure) {
			$equals = $this->equality($equals);
		}

		foreach ($nodeList->getLeafNodes() as $node) {
			if ($node->targetsAllArrayKeys()) {
				foreach ($node->item as &$value) {
					if ($equals($value)) {
						$found[] = &$value;
					}
				}
				continue;
			}

			try {
				if ($equals($node->accessValue())) {
					$found[] = &$node->item;
				}
			} catch (InvalidMethodException $e) {
			}
		}

		return new self($found);
	}

	public function get(string $path = null)
	{
		if ($path === null) {
			return $this->data;
		}

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

		return !$parser->branched ? $values[0] : $this->flatten($values);
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

	private function equality($value): callable
	{
		return function ($item) use ($value) {
			return $item === $value;
		};
	}

	private function flatten(array $values): array
	{
		return array_merge([], ...array_map([$this, 'wrap'], $values));
	}

	private function wrap($value): array
	{
		return is_array($value) ? $value : [$value];
	}
}
