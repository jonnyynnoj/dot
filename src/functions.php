<?php declare(strict_types=1);

namespace Noj\Dot;

function get($data, string $path)
{
	return Dot::from($data)->get($path);
}

function has($data, string $path)
{
	return Dot::from($data)->has($path);
}

function set($data, $paths, $value = null)
{
	Dot::from($data)->set($paths, $value);
}
