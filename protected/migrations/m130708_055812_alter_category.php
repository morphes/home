<?php

class m130708_055812_alter_category extends CDbMigration
{
	public function up()
	{
		$this->dropColumn('cat_category', 'popular');
	}

	public function down()
	{
		echo "m130708_055812_alter_category does not support migration down.\n";
		return false;
	}

}