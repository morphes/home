<?php

class m130319_090614_create_index_product_brand extends CDbMigration
{
	public function up()
	{
		// Таблица для хранения фотографий для вкладок
		$this->createTable('index_product_brand', array(
			'id'          => 'pk',
			'type'        => 'TINYINT(1) NOT NULL DEFAULT 0',
			'image_id'    => 'integer',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 0',
			'name'        => 'VARCHAR(50) NOT NULL DEFAULT ""',
			'create_time' => 'integer',
			'update_time' => 'integer'
		));

		// Таблица связок табов и картинок
		$this->createTable('index_product_brand_tab', array(
			'brand_id' => 'integer',
			'tab_id'   => 'integer, PRIMARY KEY (`brand_id`, `tab_id`)'
		));
	}

	public function down()
	{
		$this->dropTable('index_product_brand_tab');
		$this->dropTable('index_product_brand');
	}
}