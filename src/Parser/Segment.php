<?php declare(strict_types=1);

namespace Noj\Dot\Parser;

class Segment
{
	const DELIMITER_ARRAY = '.';
	const DELIMITER_OBJECT = '->';

	public $key;
	public $delimiter;

	public function __construct(string $key, string $delimiter = self::DELIMITER_ARRAY)
	{
		$this->key = $key;
		$this->delimiter = $delimiter;
	}
}
