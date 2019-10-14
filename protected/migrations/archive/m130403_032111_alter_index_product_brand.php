<?php

class m130403_032111_alter_index_product_brand extends CDbMigration
{
	public function up()
	{
		$this->addColumn('index_product_brand', 'item_id', 'INT(11) after `type`');
	}

	public function down()
	{
		$this->dropColumn('index_product_brand', 'item_id');
	}



}