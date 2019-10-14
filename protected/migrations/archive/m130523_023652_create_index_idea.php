<?php

class m130523_023652_create_index_idea extends CDbMigration
{
	public function up()
	{
		// Таблица для ссылок над превьюшками идей
		$this->createTable('index_idea_link', array(
			'id'          => 'pk',
			'name'        => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'url'         => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'position'    => 'integer',
			'create_time' => 'integer',
			'update_time' => 'integer',
		));

		// Таблица для фоток идей
		$this->createTable('index_idea_photo', array(
			'id'          => 'pk',
			'image_id'    => 'integer',
			'model_id'    => 'integer',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 0',
			'name'        => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'user_id'     => 'integer',
			'create_time' => 'integer',
			'update_time' => 'integer'
		));
	}

	public function down()
	{
		$this->dropTable('index_idea_link');

		$this->dropTable('index_idea_photo');
	}


}