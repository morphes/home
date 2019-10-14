<?php

class m130823_035746_alter_cat_category extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_category', 'image_format', 'tinyint(1) default 1');
	}

	public function down()
	{
		$this->dropColumn('cat_category', 'image_format');
	}
}