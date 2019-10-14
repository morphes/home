<?php

class m130718_021531_alter_cat_store extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store', 'activity', 'VARCHAR(255) NOT NULL DEFAULT "" after name');
	}

	public function down()
	{
		$this->dropColumn('cat_store', 'activity');
	}
}