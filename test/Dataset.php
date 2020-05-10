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
					(object)[
						'id' => 1,
						'banned' => false
					],
					(object)[
						'id' => 3,
						'banned' => true
					],
					(object)[
						'id' => 4,
						'banned' => true
					],
				]
			], [
				'users' => [
					(object)[
						'id' => 2,
						'banned' => true
					],
				],
			]]
		];
	}
}
