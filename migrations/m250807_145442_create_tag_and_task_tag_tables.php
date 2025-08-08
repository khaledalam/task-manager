<?php

use yii\db\Migration;

class m250807_145442_create_tag_and_task_tag_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->createTable('{{%tag}}', [
		    'id' => $this->primaryKey(),
		    'name' => $this->string()->notNull()->unique(),
		    'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
	    ]);

	    // Pivot table
	    $this->createTable('{{%task_tag}}', [
		    'task_id' => $this->integer()->notNull(),
		    'tag_id' => $this->integer()->notNull(),
	    ]);

	    $this->addPrimaryKey('pk_task_tag', 'task_tag', ['task_id', 'tag_id']);
	    $this->addForeignKey('fk_task_tag_task', 'task_tag', 'task_id', 'task', 'id', 'CASCADE');
	    $this->addForeignKey('fk_task_tag_tag', 'task_tag', 'tag_id', 'tag', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250807_145442_create_tag_and_task_tag_tables cannot be reverted.\n";
	    $this->dropTable('{{%task_tag}}');
	    $this->dropTable('{{%tag}}');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250807_145442_create_tag_and_task_tag_tables cannot be reverted.\n";

        return false;
    }
    */
}
