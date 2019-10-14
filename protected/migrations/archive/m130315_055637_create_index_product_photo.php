<?php

class m130315_055637_create_index_product_photo extends CDbMigration
{
	public function up()
	{
		// Таблица для хранения фотографий для вкладок
		$this->createTable('index_product_photo', array(
			'id'          => 'pk',
			'image_id'    => 'integer',
			'product_id'  => 'integer',
			'type'        => 'TINYINT(1) NOT NULL DEFAULT 0',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 0',
			'price'       => 'integer',
			'name'        => 'VARCHAR(50) NOT NULL DEFAULT ""',
			'create_time' => 'integer',
			'update_time' => 'integer'
		));

		// Таблица связок табов и картинок
		$this->createTable('index_product_photo_tab', array(
			'photo_id' => 'integer',
			'tab_id' => 'integer, PRIMARY KEY (`photo_id`, `tab_id`)'
		));
	}

	public function down()
	{
		$this->dropTable('index_product_photo_tab');
		$this->dropTable('index_product_photo');
	}


}