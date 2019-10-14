<?php

class m130708_035857_alter_cat_store extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store', 'subdomain_id', 'INT(11) DEFAULT 0 NOT NULL after admin_id');
		$this->addColumn('cat_store', 'head_image_id', 'INT(11) after subdomain_id');
	}

	public function down()
	{
		$this->dropColumn('cat_store', 'subdomain_id');
		$this->dropColumn('cat_store', 'head_image_id');
	}


}