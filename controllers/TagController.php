<?php
namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class TagController extends ActiveController
{
	public $modelClass = 'app\models\Tag';

	public function behaviors()
	{
		$b = parent::behaviors();
		$b['contentNegotiator'] = [
			'class' => ContentNegotiator::class,
			'formats' => ['application/json' => Response::FORMAT_JSON],
		];
		return $b;
	}
}
