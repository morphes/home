<?php

class m130429_023346_update_cat_category extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.catalog.models.Category');
		$this->update('cat_category',array('status'=>Category::STATUS_OPEN));
	}

	public function down()
	{
		$this->update('cat_category',array('status'=>0));

	}

}