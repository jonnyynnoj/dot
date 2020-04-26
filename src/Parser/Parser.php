<?php declare(strict_types=1);

namespace Noj\Dot\Parser;

use Noj\Dot\Exception\InvalidMethodException;

class Parser
{
	private $createMissingPaths;
	public $branched = false;

	public function __construct($createMissingPaths = false)
	{
		$this->createMissingPaths = $createMissingPaths;
	}

	public function parse(&$data, string $path): NodeList
	{
		$segments = explode('.', $path);
		$this->branched = in_array('*', $segments);
		$key = array_shift($segments);

		return $this->traverse(new Node($data, $key), $segments);
	}

	private function traverse(Node $node, array $segments): NodeList
	{
		$nodeList = new NodeList();

		if (empty($segments)) {
			return $nodeList->add($node);
		}

		$nextKey = array_shift($segments);

		if ($node->targetsAllArrayKeys()) {
			foreach ($node->item as &$nextValue) {
				$nodeList->add($this->traverse(new Node($nextValue, $nextKey), $segments));
			}
			return $nodeList;
		}

		try {
			$nextValue = &$node->accessValue($this->createMissingPaths);
		} catch (InvalidMethodException $e) {
			$nextValue = null;
		}

		if ($nextValue === null) {
			if (!$this->createMissingPaths) {
				return $nodeList->add($node);
			}

			$nextValue = $node->isArrayLike() ? [] : (object)[];
		}

		return $this->traverse(new Node($nextValue, $nextKey), $segments);
	}
}
