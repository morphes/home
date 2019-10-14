<?php

class m130426_041508_alter_cat_category extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_category', 'status', 'TINYINT(1) NOT NULL DEFAULT 0');
	}

	public function down()
	{
		$this->dropColumn('cat_category', 'status');
	}

}