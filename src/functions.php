<?php declare(strict_types=1);

namespace Noj\Dot;

function count($data, string $path): int
{
	return Dot::from($data)->count($path);
}

function find(&$data, string $path, $equals)
{
	return Dot::from($data)
		->find($path, $equals)
		->get();
}

function first(&$data, string $path = null, $equals = null)
{
	return Dot::from($data)
		->first($path, $equals)
		->get();
}

function get($data, $path)
{
	return Dot::from($data)->get($path);
}

function has($data, string $path): bool
{
	return Dot::from($data)->has($path);
}

function push(&$data, string $path, $value)
{
	return Dot::from($data)
		->push($path, $value)
		->get($path);
}

function set(&$data, $paths, $value = null)
{
	Dot::from($data)->set($paths, $value);
}
