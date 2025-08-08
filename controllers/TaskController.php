<?php

namespace app\controllers;

use app\models\Tag;
use app\models\Task;
use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\rest\Serializer;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TaskController extends Controller
{
	public $serializer = [
		'class' => Serializer::class,
		'collectionEnvelope' => 'items',
		'metaEnvelope' => '_meta',
		'linksEnvelope' => '_links',
	];

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

	/**
	 * @param null $status
	 * @param null $priority
	 * @param null $due_date_from
	 * @param null $due_date_to
	 * @param null $q
	 * @param string $sort
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @param int $show_deleted
	 * @return ActiveDataProvider
	 */
	public function actionIndex(
		$status = null,
		$priority = null,
		$due_date_from = null,
		$due_date_to = null,
		$q = null,
		string $sort = 'created_at',
		string $order = 'desc',
		int $limit = 10,
		int $offset = 0,
		int $show_deleted = 0
	) {
		$query = Task::find()->joinWith('tags');

		$tag = Yii::$app->request->get('tag');

		if ($tag) {
			$query->andWhere(['like', 'tag.name', $tag])->orWhere(['like', 'tag.id', $tag]);
		}

		if ($status) $query->andWhere(['status' => $status]);
		if ($priority) $query->andWhere(['priority' => $priority]);
		if ($due_date_from) $query->andWhere(['>=', 'due_date', $due_date_from]);
		if ($due_date_to) $query->andWhere(['<=', 'due_date', $due_date_to]);
		if ($q) $query->andWhere(['like', 'title', $q]);

		if ($show_deleted) {
			$query->withDeleted();
		}

		$query->orderBy([$sort => $order === 'asc' ? SORT_ASC : SORT_DESC]);

		$limit         = (int) Yii::$app->request->get('limit', 20);
		$offset        = (int) Yii::$app->request->get('offset', 0);

		$limit = $limit > 0 ? min($limit, 100) : 20;
		$page  = (int) floor($offset / $limit);

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => (int)$limit,
				'page' => (int)$page,
				'pageParam'      => null,
				'pageSizeParam'  => null,
				'params'         => [],
			],
		]);
	}

	/**
	 * @param $id
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionView($id): array
	{
		$model = $this->findModel($id);
		return $model->toArray([], ['tags']);
	}

	/**
	 * @param $id
	 * @return Task|null
	 * @throws NotFoundHttpException
	 */
	protected function findModel($id): ?Task
	{
		if (($model = Task::findOne($id)) !== null) {
			return $model;
		}
		throw new NotFoundHttpException("Task not found");
	}

	/**
	 * @return Task|array
	 * @throws Exception
	 */
	public function actionCreate()
	{
		$model = new Task();
		$model->load(Yii::$app->request->bodyParams, '');
		if ($model->save()) {
			$this->handleTags($model);
			Yii::$app->response->statusCode = 201;
			return $model;
		}
		Yii::$app->response->statusCode = 422;
		return ['errors' => $model->getErrors()];
	}

	/**
	 * @param $id
	 * @return string[]
	 * @throws Exception
	 */
	public function actionRestore($id): array
	{
		$model = Task::find()->withDeleted()->where(['id' => $id])->one();

		if (!$model) {
			Yii::$app->response->statusCode = 404;
			return ['error' => 'Task not found or already active.'];
		}

		if ($model->deleted_at === null) {
			return ['message' => 'Task is already active.'];
		}

		$model->deleted_at = null;

		if ($model->save(false, ['deleted_at'])) {
			return ['message' => 'Task restored successfully.'];
		}

		Yii::$app->response->statusCode = 500;
		return ['error' => 'Failed to restore task.'];
	}

	/**
	 * @param $id
	 * @return Task|array|null
	 * @throws NotFoundHttpException
	 * @throws Exception
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$model->load(Yii::$app->request->bodyParams, '');
		if ($model->save()) {
			$this->handleTags($model);
			return $model;
		}
		Yii::$app->response->statusCode = 422;
		return ['errors' => $model->getErrors()];
	}

	/**
	 * @param $id
	 * @return string[]
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws StaleObjectException
	 */
	public function actionDelete($id): array
	{
		$model = $this->findModel($id);
		$model->delete();
		return ['message' => 'Deleted'];
	}

	/**
	 * @param Task $task
	 * @throws Exception
	 */
	protected function handleTags(Task $task): void
	{
		$tags = Yii::$app->request->bodyParams['tags'] ?? [];
		if (!empty($tags)) {
			$tagIds = [];

			foreach ($tags as $tagName) {
				$tag = Tag::findOne(['name' => $tagName]) ?? new Tag(['name' => $tagName]);
				$tag->save();
				$tagIds[] = $tag->id;
			}

			$task->unlinkAll('tags', true);
			foreach ($tagIds as $tagId) {
				$task->link('tags', Tag::findOne($tagId));
			}
		}
	}
}
