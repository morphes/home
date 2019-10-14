<?php

class m130719_075327_alter_productonphotos extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_product_on_photos', 'type', 'TINYINT(1) NOT NULL DEFAULT 0 after model_id');
	}

	public function down()
	{
		$this->dropColumn('cat_product_on_photos', 'type');
	}
}