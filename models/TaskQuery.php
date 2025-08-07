<?php

namespace app\models;

use yii\db\ActiveQuery;

class TaskQuery extends ActiveQuery
{
	private $withDeleted = false;

	public function withDeleted($value = true)
	{
		$this->withDeleted = $value;
		return $this;
	}

	public function prepare($builder)
	{
		if (!$this->withDeleted) {
			$this->andWhere(['deleted_at' => null]);
		}

		return parent::prepare($builder);
	}
}
