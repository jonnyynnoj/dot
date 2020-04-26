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
		$segments = $this->getSegments($path);

		$keys = array_map(function (Segment $segment) {
			return $segment->key;
		}, $segments);

		$this->branched = in_array('*', $keys);

		$segment = array_shift($segments);
		return $this->traverse(new Node($data, $segment), $segments);
	}

	private function traverse(Node $node, array $segments): NodeList
	{
		$nodeList = new NodeList();

		if (empty($segments)) {
			return $nodeList->add($node);
		}

		$nextSegment = array_shift($segments);

		if ($node->targetsAllArrayKeys()) {
			foreach ($node->item as &$nextValue) {
				$nodeList->add($this->traverse(new Node($nextValue, $nextSegment), $segments));
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

			$nextValue = $node->segment->delimiter === Segment::DELIMITER_OBJECT ? new \stdClass : [];
		}

		return $this->traverse(new Node($nextValue, $nextSegment), $segments);
	}

	/**
	 * @return Segment[]
	 */
	private function getSegments(string $path): array
	{
		if (strpos($path, Segment::DELIMITER_OBJECT) === false) {
			return array_map(function (string $part) {
				return new Segment($part);
			}, explode('.', $path));
		}

		$remaining = $path;
		$segments = [];

		while ($remaining !== '') {
			$nextDot = strpos($remaining, Segment::DELIMITER_ARRAY);
			$nextArrow = strpos($remaining, Segment::DELIMITER_OBJECT);

			if ($nextDot === false && $nextArrow === false) {
				$segments[] = new Segment($remaining);
				break;
			}

			if ($nextArrow === false || ($nextDot !== false && $nextDot < $nextArrow)) {
				$pos = $nextDot;
				$type = Segment::DELIMITER_ARRAY;
			} else {
				$pos = $nextArrow;
				$type = Segment::DELIMITER_OBJECT;
			}

			$key = substr($remaining, 0, $pos);
			$segments[] = new Segment($key, $type);
			$remaining = substr($remaining, strlen($key . $type));
		}

		return $segments;
	}
}
