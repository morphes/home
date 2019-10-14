<?php

class m130724_034544_alter_table_cat_folders extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_folders', 'description', 'VARCHAR(2000) after name');
	}

	public function down()
	{
		$this->dropColumn('cat_folders', 'description');
	}
}