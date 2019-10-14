<?php

class m130529_043023_alter_cat_vendor extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.catalog.models.Vendor');

		$this->addColumn('cat_vendor', 'name_translit', 'VARCHAR(255) NOT NULL DEFAULT "" after name');

		/** @var $vendors Vendor[] */
		$vendors = Vendor::model()->findAll();

		foreach ($vendors as $vend) {
			$vend->name_translit = Amputate::translitYandex($vend->name);
			$vend->save(false);
		}
	}

	public function down()
	{
		$this->dropColumn('cat_vendor', 'name_translit');
	}

}