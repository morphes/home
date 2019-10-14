<?php

class m130709_050914_update_review extends CDbMigration
{
	public function up()
	{

		$sql = 'UPDATE review SET rating = 5 WHERE rating =1 OR rating = 3';

		Yii::app()->db->createCommand($sql)->execute();
	}


	public function down()
	{
		return true;
	}
}