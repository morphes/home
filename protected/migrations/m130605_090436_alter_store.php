<?php

class m130605_090436_alter_store extends CDbMigration
{
	public function up()
	{
		// колонка типа магазина и индекс по ней
		$this->addColumn('cat_store', 'type', 'tinyint(1) after `id`');
		$this->createIndex('idx_type', 'cat_store', 'type');

		// таблица привязки интернет-магазинов к стране/региону/городу
		// и индекс по id магазина
		$this->createTable('cat_online_store_geo', array(
			'store_id'=>'integer not null',
			'type'=>'tinyint(1) not null',
			'geo_id'=>'integer not null'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');
		$this->createIndex('idx_store_id', 'cat_online_store_geo', 'store_id');

		// присвоение всем существующим магазинам типа offline
		Yii::import('application.modules.catalog.models.Store');
		$this->update('cat_store', array('type'=>Store::TYPE_OFFLINE));
	}

	public function down()
	{
		$this->dropColumn('cat_store', 'type');
		$this->dropTable('cat_online_store_geo');
	}
}