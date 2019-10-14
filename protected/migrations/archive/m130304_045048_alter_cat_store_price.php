<?php

class m130304_045048_alter_cat_store_price extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store_price', 'by_vendor', 'TINYINT(1) DEFAULT 0 after price_type');
	}

	public function down()
	{
		$this->dropColumn('cat_store_price', 'by_vendor');
	}
}