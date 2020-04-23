<?php declare(strict_types=1);

namespace Noj\Dot;

class DotException extends \Exception
{
	public static function fromInvalidMethod($object, $method)
	{
		$class = get_class($object);
		return new self("Method $method is not callable on $class");
	}
}
