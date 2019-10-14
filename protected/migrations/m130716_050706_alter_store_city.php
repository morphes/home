<?php

class m130716_050706_alter_store_city extends CDbMigration
{
	public function up()
	{
		$this->renameTable('cat_online_store_geo', 'cat_store_geo');
		$sql = 'ALTER TABLE `cat_store_geo` ADD PRIMARY KEY (`store_id`, `type`, `geo_id`)';
		Yii::app()->db->createCommand($sql)->execute();

		$sql = 'SELECT id, city_id FROM cat_store WHERE type=1';
		$data = Yii::app()->db->createCommand($sql)->queryAll();

		if (!empty($data)) {
			$sql = 'INSERT INTO cat_store_geo (`store_id`, `type`, `geo_id` ) VALUES ';
			$cnt=0;
			foreach ($data as $item) {
				if ($cnt !== 0) {
					$sql .= ',';
				} else {
					$cnt++;
				}
				$sql .= '('.$item['id'].',1,'.$item['city_id'].')';
			}
			Yii::app()->db->createCommand($sql)->execute();
		}

		$this->dropColumn('cat_store', 'city_id');
	}

	public function down()
	{
		echo "m130716_050706_alter_store_city does not support migration down.\n";
		return false;
	}

}