<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tag".
 *
 * @property int $id
 * @property string $name
 * @property string|null $created_at
 *
 * @property Tag[] $taskTags
 * @property Task[] $tasks
 */
class Tag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'created_at' => 'Created At',
        ];
    }

	public function fields()
	{
		return ['id', 'name'];
	}


	/**
     * Gets query for [[TaskTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskTags()
    {
        return $this->hasMany(Task::class, ['tag_id' => 'id']);
    }

	/**
	 * Gets query for [[Tasks]].
	 *
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
	        ->viaTable('task_tag', ['tag_id' => 'id']);
    }

}
