<?php

class m130729_071453_alter extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('cat_store_price', 'discount', 'FLOAT(7,3) NULL DEFAULT NULL AFTER `price`');
	}

	public function down()
	{

	}
}