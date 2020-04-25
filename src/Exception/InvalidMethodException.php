<?php declare(strict_types=1);

namespace Noj\Dot\Exception;

use Noj\Dot\Parser\Node;

class InvalidMethodException extends DotException
{
	public static function fromNode(Node $node): self
	{
		$type = is_object($node->item) ? get_class($node->item) : gettype($node->item);
		return new self("Can't call method {$node->getMethodName()} on $type");
	}
}
