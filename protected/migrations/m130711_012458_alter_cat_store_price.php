<?php

class m130711_012458_alter_cat_store_price extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store_price', 'discount', 'INT(3) NOT NULL DEFAULT 0 after price');
	}

	public function down()
	{
		$this->dropColumn('cat_store_price', 'discount');
	}
}