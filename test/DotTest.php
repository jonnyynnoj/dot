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
		$dot->set('nested.data[1].property', 'foo');
		self::assertEquals('value', $dot->get('nested.data[0].property'));
		self::assertEquals('foo', $dot->get('nested.data[1].property'));
		self::assertEquals('value', $dot->get('nested.data[2].property'));
	}
}
