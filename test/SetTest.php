<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use PHPUnit\Framework\TestCase;
use function Noj\Dot\set;

class SetTest extends TestCase
{
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

		set($data, 'nested.data.property', 'foo');
		self::assertEquals('foo', $data['nested']['data']['property']);
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

		set($data, 'nested.data.property', 'foo');
		self::assertEquals('foo', $data['nested']->data->property);
	}

	/** @test */
	public function it_can_create_keys_if_missing()
	{
		$data = [];

		set($data, 'key.foo->bar.baz', 2);
		self::assertEquals(2, $data['key']['foo']->bar['baz']);
	}

	/** @test */
	public function it_can_accept_array_to_set_multiple_paths()
	{
		$data = [
			'nested' => [
				'data' => [
					'property' => 'value'
				],
				'another' => [
					'property' => 'value'
				]
			]
		];

		set($data, [
			'nested.data.property' => 'foo',
			'nested.another.property' => 'bar',
		]);

		self::assertEquals('foo', $data['nested']['data']['property']);
		self::assertEquals('bar', $data['nested']['another']['property']);
	}

	/** @test */
	public function it_can_call_setter()
	{
		$data = [
			'nested' => [
				'property' => new class {
					private $value;
					public function getValue() {
						return $this->value;
					}
					public function setValue($value) {
						$this->value = $value;
					}
				}
			]
		];

		set($data, 'nested.property.@setValue', 'value');
		self::assertEquals('value', $data['nested']['property']->getValue());
	}

	/** @test */
	public function it_can_call_setter_multiple_times()
	{
		$data = [
			'nested' => [
				'property' => new class {
					private $values = [];
					public function getValues() {
						return $this->values;
					}
					public function addValue($value) {
						$this->values[] = $value;
					}
				}
			]
		];

		set($data, 'nested.property.@addValue*', ['foo', 'bar', 'baz']);
		self::assertEquals(['foo', 'bar', 'baz'], $data['nested']['property']->getValues());
	}

	/**
	 * @test
	 * @expectedException \Noj\Dot\Exception\InvalidMethodException
	 * @expectedExceptionMessage Can't call method setValue on array
	 */
	public function it_throws_exception_if_setter_not_callable()
	{
		$data = [];

		set($data, 'property.ddd.@setValue', 'value');
	}

	/** @test */
	public function it_can_set_values_on_multidimensional_path()
	{
		$data = [
			[
				'name' => 'group1',
				'items' => [
					['name' => 'item1'],
					['name' => 'item3'],
					['name' => 'item6']
				]
			],
			[
				'name' => 'group2',
				'items' => [
					['name' => 'item2'],
				]
			],
			[
				'name' => 'group3',
				'items' => [
					['name' => 'item4'],
					['name' => 'item5']
				]
			],
		];

		set($data, '*.items.*.name', 'sameName');

		self::assertEquals('sameName', $data[0]['items'][0]['name']);
		self::assertEquals('sameName', $data[0]['items'][1]['name']);
		self::assertEquals('sameName', $data[0]['items'][2]['name']);
		self::assertEquals('sameName', $data[1]['items'][0]['name']);
		self::assertEquals('sameName', $data[2]['items'][0]['name']);
		self::assertEquals('sameName', $data[2]['items'][1]['name']);
	}

	/** @test */
	public function it_can_handle_expand_as_last_key()
	{
		$data = [
			'nested' => [
				'data' => [
					['property' => ['value1', 'value2']],
					['property' => ['value1', 'value2']],
					['property' => ['value1', 'value2']],
				]
			]
		];

		set($data, 'nested.data.*.property.*', 'foo');
		self::assertEquals(['foo', 'foo'], $data['nested']['data'][0]['property']);

		self::assertEquals(['foo', 'foo'], $data['nested']['data'][0]['property']);
		self::assertEquals(['foo', 'foo'], $data['nested']['data'][1]['property']);
		self::assertEquals(['foo', 'foo'], $data['nested']['data'][2]['property']);
	}
}
