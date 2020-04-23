<?php declare(strict_types=1);

namespace Noj\Dot\Test;

use Noj\Dot\Dot;
use PHPUnit\Framework\TestCase;

class DotTest extends TestCase
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
				'data' => [
					'property' => 'value'
				]
			]
		];

		$value = Dot::from($data)->get('nested.data.property');
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
		self::assertEquals('foo', $data['nested']['data']['property']);
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
		self::assertEquals('foo', $data['nested']->data->property);
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
		$data = ['key' => null];

		$dot = Dot::from($data);

		self::assertNull($dot->get('@getSomething'));
		self::assertNull($dot->get('key.@getSomething'));
		self::assertNull($dot->get('key.@getSomething.foo'));
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

		$dot = Dot::from($data);
		$dot->set('nested.property.@setValue', 'value');
		self::assertEquals('value', $dot->get('nested.property.@getValue'));
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

		$dot = Dot::from($data);
		$dot->set('nested.property.@addValue*', ['foo', 'bar', 'baz']);
		self::assertEquals(['foo', 'bar', 'baz'], $dot->get('nested.property.@getValues'));
		self::assertEquals(['foo', 'bar', 'baz'], $data['nested']['property']->getValues());
	}

	/** @test */
	public function it_can_traverse_through_array_index()
	{
		$data = [
			'nested' => [
				'data' => [
					['property' => 'value'],
					['property' => 'value'],
					['property' => 'value'],
				]
			]
		];

		$dot = Dot::from($data);
		$dot->set('nested.data.1.property', 'foo');
		self::assertEquals('value', $dot->get('nested.data.0.property'));
		self::assertEquals('foo', $dot->get('nested.data.1.property'));
		self::assertEquals('value', $dot->get('nested.data.2.property'));

		self::assertEquals('value', $data['nested']['data'][0]['property']);
		self::assertEquals('foo', $data['nested']['data'][1]['property']);
		self::assertEquals('value', $data['nested']['data'][2]['property']);
	}

	/** @test */
	public function it_can_traverse_through_expanded_array()
	{
		$data = [
			'nested' => [
				'data' => [
					['property' => 'value'],
					['property' => 'value'],
					['property' => 'value'],
				]
			]
		];

		$dot = Dot::from($data);
		$dot->set('nested.data.*.property', 'foo');
		self::assertEquals('foo', $dot->get('nested.data.0.property'));
		self::assertEquals('foo', $dot->get('nested.data.1.property'));
		self::assertEquals('foo', $dot->get('nested.data.2.property'));
		self::assertEquals(['foo', 'foo', 'foo'], $dot->get('nested.data.*.property'));

		self::assertEquals('foo', $data['nested']['data'][0]['property']);
		self::assertEquals('foo', $data['nested']['data'][1]['property']);
		self::assertEquals('foo', $data['nested']['data'][2]['property']);
	}

	/** @test */
	public function it_can_traverse_through_multiple_expanded_arrays()
	{
		$data = [
			'nested' => [
				'data' => [
					[
						'property' => [
							['test' => 'value'],
							['test' => 'bar']
						]
					],
					[
						'property' => [
							['test' => 'value'],
							['test' => 'bar']
						]
					],
					[
						'property' => [
							['test' => 'value'],
							['test' => 'bar']
						]
					],
				]
			]
		];

		$dot = Dot::from($data);
		$dot->set('nested.data.*.property.*.test', 'foo');
		self::assertEquals('foo', $dot->get('nested.data.0.property.0.test'));
		self::assertEquals('foo', $dot->get('nested.data.0.property.1.test'));
		self::assertEquals('foo', $dot->get('nested.data.1.property.0.test'));
		self::assertEquals('foo', $dot->get('nested.data.1.property.1.test'));
		self::assertEquals('foo', $dot->get('nested.data.2.property.0.test'));
		self::assertEquals('foo', $dot->get('nested.data.2.property.1.test'));

		$expected = [
			['foo', 'foo'],
			['foo', 'foo'],
			['foo', 'foo'],
		];

		self::assertEquals($expected, $dot->get('nested.data.*.property.*.test'));
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

		$dot = Dot::from($data);
		$dot->set('nested.data.*.property.*', 'foo');
		self::assertEquals(['foo', 'foo'], $dot->get('nested.data.0.property'));

		$expected = [
			['foo', 'foo'],
			['foo', 'foo'],
			['foo', 'foo'],
		];

		self::assertEquals($expected, $dot->get('nested.data.*.property.*'));
	}

	/** @test */
	public function it_can_set_multiple_paths()
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

		$dot = Dot::from($data);
		$dot->set([
			'nested.data.property' => 'foo',
			'nested.another.property' => 'bar',
		]);

		self::assertEquals('foo', $dot->get('nested.data.property'));
		self::assertEquals('bar', $dot->get('nested.another.property'));
	}

	/** @test */
	public function it_can_create_array_keys_if_missing()
	{
		$data = ['key' => null];

		$dot = Dot::from($data);
		$dot->set('key.foo.bar.baz', 2);
		self::assertEquals(2, $data['key']['foo']['bar']['baz']);
	}

	/** @test */
	public function it_can_create_object_properties_if_missing()
	{
		$data = (object)['key' => null];

		$dot = Dot::from($data);
		$dot->set('key.foo.bar.baz', 2);
		self::assertEquals(2, $data->key->foo->bar->baz);
	}
}
