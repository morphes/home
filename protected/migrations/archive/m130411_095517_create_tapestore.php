<?php

class m130411_095517_create_tapestore extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_tapestore', array(
			'id' => 'pk',
			'status' => 'INT(11) NOT NULL',
			'store_id' => 'INT(11) NOT NULL',
			'user_id' => 'INT(11) NOT NULL',
			'image_id' => 'INT(11) NOT NULL',
			'position' => 'INT(11) NOT NULL DEFAULT 0',
			'start_time' => 'INT(11) not null',
			'end_time' => 'INT(11) not null',
			'create_time' => 'INT(11) not null',
			'update_time' => 'INT(11) not null',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		// Таблица связок табов и картинок
		$this->createTable('cat_tapestore_category', array(
			'tapestore_id' => 'INT(11) NOT NULL',
			'category_id'   => 'INT(11), PRIMARY KEY (`tapestore_id`, `category_id`)'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');
	}

	public function down()
	{
		$this->dropTable('cat_tapestore');
		$this->dropTable('cat_tapestore_category');
	}

}