<?php

namespace app\controllers;

use app\models\Task;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TaskController extends Controller
{
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'contentNegotiator' => [
				'class' => ContentNegotiator::class,
				'formats' => ['application/json' => Response::FORMAT_JSON],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'index' => ['GET'],
					'view' => ['GET'],
					'create' => ['POST'],
					'update' => ['PUT'],
					'delete' => ['DELETE'],
				],
			],
		]);
	}

	public function actionIndex(
		$status = null,
		$priority = null,
		$due_date_from = null,
		$due_date_to = null,
		$q = null,
		$sort = 'created_at',
		$order = 'desc',
		$limit = 10,
		$offset = 0
	) {
		$query = Task::find();

		if ($status) $query->andWhere(['status' => $status]);
		if ($priority) $query->andWhere(['priority' => $priority]);
		if ($due_date_from) $query->andWhere(['>=', 'due_date', $due_date_from]);
		if ($due_date_to) $query->andWhere(['<=', 'due_date', $due_date_to]);
		if ($q) $query->andWhere(['like', 'title', $q]);

		$query->orderBy([$sort => $order === 'asc' ? SORT_ASC : SORT_DESC]);

		$page = ($limit > 0) ? floor($offset / $limit) : 0;

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => (int)$limit,
				'page' => (int)$page,
			],
		]);
	}


	public function actionView($id)
	{
		return $this->findModel($id);
	}

	protected function findModel($id)
	{
		if (($model = Task::findOne($id)) !== null) {
			return $model;
		}
		throw new NotFoundHttpException("Task not found");
	}

	public function actionCreate()
	{
		$model = new Task();
		$model->load(Yii::$app->request->bodyParams, '');
		if ($model->save()) {
			Yii::$app->response->statusCode = 201;
			return $model;
		}
		Yii::$app->response->statusCode = 422;
		return ['errors' => $model->getErrors()];
	}

	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$model->load(Yii::$app->request->bodyParams, '');
		if ($model->save()) {
			return $model;
		}
		Yii::$app->response->statusCode = 422;
		return ['errors' => $model->getErrors()];
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$model->delete();
		return ['message' => 'Deleted'];
	}
}
