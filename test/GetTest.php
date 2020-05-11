<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Test\Stubs\Collection;
use PHPUnit\Framework\TestCase;
use function Noj\Dot\get;

class GetTest extends TestCase
{
	/** @test */
	public function it_can_get_a_top_level_property()
	{
		$data = ['property' => ['foo' => 'bar']];
		$value = get($data, 'property');
		self::assertEquals(['foo' => 'bar'], $value);

		$data = ['foo', 'bar'];
		$value = get($data, '0');
		self::assertEquals('foo', $value);

		$data = ['foo', 'bar'];
		$value = get($data, 0);
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

		$value = get($data, 'nested.0.property');
		self::assertEquals('value', $value);
	}

	/** @test */
	public function it_can_get_path_through_array_like_objects()
	{
		$items = new Collection(['name' => 'item1']);
		$collection = new Collection(['items' => $items]);

		$value = get($collection, 'items.name');
		self::assertEquals('item1', $value);
	}

	/** @test */
	public function it_returns_null_if_array_path_doesnt_exist()
	{
		$data = [
			'key' => []
		];

		self::assertNull(get($data, 'foo'));
		self::assertNull(get($data, 'key.foo'));
		self::assertNull(get($data, 'key.foo.bar'));
		self::assertNull(get($data, 'key.foo.bar.baz'));
		self::assertArrayNotHasKey('foo', $data);
		self::assertArrayNotHasKey('foo', $data['key']);
	}

	/** @test */
	public function it_returns_null_if_object_path_doesnt_exist()
	{
		$data = (object)[
			'key' => (object)[]
		];

		self::assertNull(get($data, 'foo'));
		self::assertNull(get($data, 'key.foo'));
		self::assertNull(get($data, 'key.foo.bar'));
		self::assertNull(get($data, 'key.foo.bar.baz'));
		self::assertObjectNotHasAttribute('foo', $data);
		self::assertObjectNotHasAttribute('foo', $data->key);
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

		$value = get($data, 'nested.data.property');
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

		$value = get($data, 'nested.@getSomething.property');
		self::assertEquals('value', $value);

		$data = [
			'nested' => new class {
				public function getSomething() {
					return 'value';
				}
			}
		];

		$value = get($data, 'nested.@getSomething');
		self::assertEquals('value', $value);
	}

	/** @test */
	public function it_returns_null_if_getter_doesnt_exist()
	{
		$data = ['key' => []];

		self::assertNull(get($data, '@getSomething'));
		self::assertNull(get($data, 'invalid.@getSomething'));
		self::assertNull(get($data, 'key.@getSomething'));
		self::assertNull(get($data, 'key.@getSomething.foo'));
	}

	/** @test */
	public function it_can_pluck_values_from_multidimensional_path()
	{
		$data = [
			[
				'name' => 'group1',
				'users' => [
					[
						'items' => [
							['name' => 'item1'],
							['name' => 'item3'],
						]
					]
				]
			],
			[
				'name' => 'group2',
				'users' => [
					[
						'items' => []
					]
				]
			],
			[
				'name' => 'group3',
				'users' => [
					[
						'items' => [
							['name' => 'item2'],
						]
					]
				]
			],
		];

		$expected = ['item1', 'item3', 'item2'];
		self::assertEquals($expected, get($data, '*.users.*.items.*.name'));

		$expected = [
			['name' => 'item1'],
			['name' => 'item3'],
			['name' => 'item2'],
		];
		self::assertEquals($expected, get($data, '*.users.*.items'));
	}
}
