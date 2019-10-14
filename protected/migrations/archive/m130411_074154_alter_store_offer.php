<?php

class m130411_074154_alter_store_offer extends CDbMigration
{
	public function up()
	{
		$sql = "ALTER TABLE `cat_store_offer` CHANGE COLUMN `city_id` `city_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `company`";
		Yii::app()->db->createCommand($sql)->execute();
	}

	public function down()
	{
		return true;
	}
}