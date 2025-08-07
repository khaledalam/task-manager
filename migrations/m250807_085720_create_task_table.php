<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m250807_085720_create_task_table extends Migration
{   
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'status' => "ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending'",
            'priority' => "ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium'",
            'due_date' => $this->date(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task}}');
    }
}
