<?php

class m130220_033149_update_cat_store extends CDbMigration
{
	public function up()
	{
                Yii::import('application.modules.catalog.models.Store');
                Yii::app()->db->createCommand()->update('cat_store', array('tariff_id'=>Store::TARIF_FREE));
	}

	public function down()
	{
		return true;
	}
}