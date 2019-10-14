<?php

class m130724_042017_alter_cat_store extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store', 'bg_class', 'VARCHAR(50) NOT NULL DEFAULT "" after about');
	}

	public function down()
	{
		$this->dropColumn('cat_store', 'bg_class');
	}
}