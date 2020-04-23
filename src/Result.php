<?php declare(strict_types=1);

namespace Fabrica\Dot;

class Result
{
	public $item;
	public $key;

	public function __construct(&$item, $key)
	{
		$this->item = &$item;
		$this->key = $key;
	}
}
