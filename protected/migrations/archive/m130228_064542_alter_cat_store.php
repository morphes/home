<?php

class m130228_064542_alter_cat_store extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.catalog.models.Store');

		// Добавляем в "cat_store" новые поля
		$this->addColumn('cat_store', 'mall_build_id', 'INT(11) after average_rating');
		$this->addColumn('cat_store', 'floor_id', 'INT(11) after mall_build_id');
		$this->addColumn('cat_store', 'sect_name', 'VARCHAR(10) after floor_id');

		// Переносим данные из "mall_build_store" в "cat_store"
		$buildStore = Yii::app()->db->createCommand()
			->select('*')
			->from('mall_build_store')
			->queryAll();
		echo "    > Перенос записей из mall_build_store в cat_store: ".count($buildStore)." шт.\n";
		if ($buildStore) {
			foreach($buildStore as $item) {
				Store::model()->UpdateByPk($item['store_id'], array(
					'mall_build_id' => $item['mall_build_id'],
					'floor_id'      => $item['floor_id'],
					'sect_name'     => $item['sect_name']
				));
			}
		}

		// Переименовываем "mall_build_store" для возможности отката
		$this->renameTable('mall_build_store', 'deleted_mall_build_store');
	}

	public function down()
	{
		// Удаляем новые поля из "cat_store"
		$this->dropColumn('cat_store', 'mall_build_id');
		$this->dropColumn('cat_store', 'floor_id');
		$this->dropColumn('cat_store', 'sect_name');

		// Переименовываем "deleted_mall_build_store" обратно
		$this->renameTable('deleted_mall_build_store', 'mall_build_store');
	}
}