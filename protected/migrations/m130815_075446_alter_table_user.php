<?php

class m130815_075446_alter_table_user extends CDbMigration
{
	public function up()
	{
		$sql = '
		ALTER TABLE `user`
			ALTER `email` DROP DEFAULT;
		ALTER TABLE `user`
			CHANGE COLUMN `email` `email` VARCHAR(50) NULL AFTER `password`;
		';
		Yii::app()->db->createCommand($sql)->execute();
	}

	public function down()
	{
		echo "m130815_075446_alter_table_user does not support migration down.\n";
		return false;
	}
}