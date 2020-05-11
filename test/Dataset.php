<?php declare(strict_types=1);

namespace Noj\Dot\Test;

trait Dataset
{
	private $data = [];

	protected function setUp()
	{
		$this->data = [
			'groups' => [[
				'users' => [
					[
						'id' => 1,
						'banned' => false,
						'items' => []
					],
					[
						'id' => 3,
						'banned' => true,
						'items' => []
					],
					[
						'id' => 4,
						'banned' => true,
						'items' => []
					],
				]
			], [
				'users' => [
					[
						'id' => 2,
						'banned' => true,
						'items' => []
					],
				],
			]]
		];
	}
}
