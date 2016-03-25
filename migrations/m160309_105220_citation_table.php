<?php

use yii\db\Migration;

class m160309_105220_citation_table extends Migration
{
    public function up()
    {
        $this->createTable('citation', [
            'id' => $this->primaryKey(),
            'user_id' => $this->string()->notNull(),
            'h_index' => $this->integer()->defaultValue(0),
            'bib_ref' => $this->integer()->defaultValue(0),
            'missing' => $this->boolean()->defaultValue(1),
            'updated_at' => $this->dateTime()
        ]);
    }

    public function down()
    {
        $this->dropTable('citation');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
