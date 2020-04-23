<?php declare(strict_types=1);

namespace Noj\Dot;

class Parser
{
	public function parse(&$data, string $path)
	{
		$segments = explode('.', $path);
		return $this->traverse($data, $segments);
	}

	/**
	 * @return Node|Node[]
	 */
	private function traverse(&$data, array $segments)
	{
		$key = array_shift($segments);
		$node = new Node($data, $key);

		if (count($segments) === 0) {
			return $node;
		}

		if ($key === '*' && $node->isArrayLike()) {
			$results = [];
			foreach ($data as &$item) {
				$results[] = $this->traverse($item, $segments);
			}
			return $results;
		}

		$data = &$node->accessValue();
		return $this->traverse($data, $segments);
	}
}
