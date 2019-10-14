<?php

class m130226_035126_create_store_feedback extends CDbMigration
{
	public function up()
	{
                $this->createTable('cat_store_feedback', array(
                        'id' => 'pk',
                        'store_id' => 'integer not null',
                        'user_id' => 'integer not null',
                        'parent_id' => 'integer not null',
                        'mark' => 'integer',
                        'message' => 'varchar(3000)',
                        'create_time' => 'integer not null',
                        'update_time' => 'integer not null'
                ), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

                $this->addColumn('cat_store', 'average_rating', 'tinyint(1) after `contractor_id`');
                $this->createIndex('idx_store_id', 'cat_store_feedback', 'store_id');
                $this->createIndex('idx_user_id', 'cat_store_feedback', 'user_id');
                $this->createIndex('idx_parent_id', 'cat_store_feedback', 'parent_id');
	}

	public function down()
	{
                $this->dropTable('cat_store_feedback');
                $this->dropColumn('cat_store', 'average_rating');
	}

}