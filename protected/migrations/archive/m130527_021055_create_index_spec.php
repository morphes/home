<?php

class m130527_021055_create_index_spec extends CDbMigration
{
	public function up()
	{
		// Таблица для ссылок над превьюшками идей
		$this->createTable('index_spec_block', array(
			'id'          => 'pk',
			'name'        => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'url'         => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'position'    => 'integer',
			'create_time' => 'integer',
			'update_time' => 'integer',
		));

		// Таблица для фоток идей
		$this->createTable('index_spec_photo', array(
			'id'          => 'pk',
			'image_id'    => 'integer',
			'model_id'    => 'integer',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 0',
			'name'        => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'create_time' => 'integer',
			'update_time' => 'integer'
		));

		$this->createTable('index_spec_photo_block', array(
			'block_id' => 'integer',
			'photo_id' => 'integer, PRIMARY KEY (`block_id`, `photo_id`)'
		));
	}

	public function down()
	{
		$this->dropTable('index_spec_block');

		$this->dropTable('index_spec_photo');

		$this->dropTable('index_spec_photo_block');
	}

}