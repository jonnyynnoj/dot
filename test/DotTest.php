<?php declare(strict_types=1);

namespace Fabrica\Dot\Test;

use Fabrica\Dot\Dot;
use PHPUnit\Framework\TestCase;

class DotTest extends TestCase
{
	/** @test */
	public function it_can_get_path_through_arrays()
	{
		$data = [
			'nested' => [
				'data' => [
					'property' => 'value'
				]
			]
		];

		$value = Dot::from($data)->get('nested.data.property');
		self::assertEquals('value', $value);
	}

	/** @test */
	public function it_can_set_path_through_arrays()
	{
		$data = [
			'nested' => [
				'data' => [
					'property' => 'value'
				]
			]
		];

		$dot = Dot::from($data);
		$dot->set('nested.data.property', 'foo');
		self::assertEquals('foo', $dot->get('nested.data.property'));
	}

	/** @test */
	public function it_can_get_path_through_objects()
	{
		$data = [
			'nested' => (object)[
				'data' => (object)[
					'property' => 'value'
				]
			]
		];

		$value = Dot::from($data)->get('nested.data.property');
		self::assertEquals('value', $value);
	}

	/** @test */
	public function it_can_set_path_through_objects()
	{
		$data = [
			'nested' => (object)[
				'data' => (object)[
					'property' => 'value'
				]
			]
		];

		$dot = Dot::from($data);
		$dot->set('nested.data.property', 'foo');
		self::assertEquals('foo', $dot->get('nested.data.property'));
	}
}
