<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Dot;
use PHPUnit\Framework\TestCase;

class GetTest extends TestCase
{
	/** @test */
	public function it_can_get_a_top_level_property()
	{
		$data = ['property' => ['foo' => 'bar']];
		$value = Dot::from($data)->get('property');
		self::assertEquals(['foo' => 'bar'], $value);

		$data = ['foo', 'bar'];
		$value = Dot::from($data)->get('0');
		self::assertEquals('foo', $value);
	}

	/** @test */
	public function it_can_get_path_through_arrays()
	{
		$data = [
			'nested' => [
				['property' => 'value']
			]
		];

		$value = Dot::from($data)->get('nested.0.property');
		self::assertEquals('value', $value);
	}

	/** @test */
	public function it_returns_null_if_path_doesnt_exist()
	{
		$data = [
			'key' => []
		];

		$dot = Dot::from($data);
		self::assertNull($dot->get('foo'));
		self::assertNull($dot->get('key.foo'));
		self::assertNull($dot->get('key.foo.bar'));
		self::assertNull($dot->get('key.foo.bar.baz'));
		self::assertFalse(isset($data['key']['foo']));
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
	public function it_can_traverse_through_getters()
	{
		$data = [
			'nested' => new class {
				public function getSomething() {
					return ['property' => 'value'];
				}
			}
		];

		$value = Dot::from($data)->get('nested.@getSomething.property');
		self::assertEquals('value', $value);

		$data = [
			'nested' => new class {
				public function getSomething() {
					return 'value';
				}
			}
		];

		$value = Dot::from($data)->get('nested.@getSomething');
		self::assertEquals('value', $value);
	}

	/** @test */
	public function it_returns_null_if_getter_doesnt_exist()
	{
		$data = ['key' => []];

		$dot = Dot::from($data);

		self::assertNull($dot->get('@getSomething'));
		self::assertNull($dot->get('invalid.@getSomething'));
		self::assertNull($dot->get('key.@getSomething'));
		self::assertNull($dot->get('key.@getSomething.foo'));
	}

	/** @test */
	public function it_can_pluck_values_from_multidimensional_path()
	{
		$data = [
			[
				'name' => 'group1',
				'users' => [
					'user1' => [
						'items' => [
							['name' => 'item1'],
							['name' => 'item3'],
							['name' => 'item6']
						]
					]
				]
			],
			[
				'name' => 'group2',
				'users' => [
					'user2' => [
						'items' => [
							['name' => 'item2'],
						]
					]
				]
			],
			[
				'name' => 'group3',
				'users' => [
					'user3' => [
						'items' => [
							['name' => 'item4'],
							['name' => 'item5'],
						]
					]
				]
			],
		];

		$dot = Dot::from($data);

		$expected = ['item1', 'item3', 'item6', 'item2', 'item4', 'item5',];
		self::assertEquals($expected, $dot->get('*.users.*.items.*.name'));
	}
}
