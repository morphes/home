<?php

class m130725_061545_add_country extends CDbMigration
{
	public function up()
	{
		$sql = 'INSERT INTO country (id, name, eng_name, pos) VALUES (7716096, \'Абхазия\', \'abhazia\',0)';
		Yii::app()->db->createCommand($sql)->execute();

		$sql = 'UPDATE city SET country_id=7716096 WHERE region_id=1281';
		Yii::app()->db->createCommand($sql)->execute();

		$sql = 'UPDATE region SET country_id=7716096 WHERE id=1281';
		Yii::app()->db->createCommand($sql)->execute();
	}

	public function down()
	{
		$sql = 'UPDATE city SET country_id=1280 WHERE region_id=1281';
		Yii::app()->db->createCommand($sql)->execute();

		$sql = 'UPDATE region SET country_id=1280 WHERE id=1281';
		Yii::app()->db->createCommand($sql)->execute();

		$sql = 'DELETE FROM country WHERE id=7716096';
		Yii::app()->db->createCommand($sql)->execute();
	}
}