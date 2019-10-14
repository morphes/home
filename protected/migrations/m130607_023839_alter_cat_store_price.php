<?php

class m130607_023839_alter_cat_store_price extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store_price', 'url', 'varchar(255) after `by_vendor`');
	}

	public function down()
	{
		$this->dropColumn('cat_store_price', 'url');
	}
}