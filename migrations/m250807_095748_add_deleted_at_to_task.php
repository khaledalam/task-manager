<?php

use yii\db\Migration;

class m250807_095748_add_deleted_at_to_task extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('{{%task}}', 'deleted_at', $this->timestamp()->null()->after('updated_at'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250807_095748_add_deleted_at_to_task cannot be reverted.\n";

	    $this->dropColumn('{{%task}}', 'deleted_at');

	    return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250807_095748_add_deleted_at_to_task cannot be reverted.\n";

        return false;
    }
    */
}
