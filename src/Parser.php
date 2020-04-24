<?php declare(strict_types=1);

namespace Noj\Dot;

class Parser
{
	private $createMissingPaths;

	public function __construct($createMissingPaths = false)
	{
		$this->createMissingPaths = $createMissingPaths;
	}

	public function parse(&$data, string $path)
	{
		$segments = explode('.', $path);
		$key = array_shift($segments);
		return $this->traverse(new Node($data, $key), $segments);
	}

	private function traverse(Node $node, array $segments)
	{
		if (count($segments) === 0) {
			return $node;
		}

		$nextKey = array_shift($segments);

		if ($node->isBranchable()) {
			$results = [];
			foreach ($node->item as &$nextValue) {
				$results[] = $this->traverse(new Node($nextValue, $nextKey), $segments);
			}
			return $results;
		}

		$nextValue = &$node->accessValue();

		if ($nextValue === null) {
			if (!$this->createMissingPaths) {
				return $node;
			}

			$nextValue = $node->isArrayLike() ? [] : (object)[];
		}

		return $this->traverse(new Node($nextValue, $nextKey), $segments);
	}
}
