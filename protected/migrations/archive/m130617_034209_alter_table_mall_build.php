<?php

class m130617_034209_alter_table_mall_build extends CDbMigration
{
	public function up()
	{
		$this->addColumn('mall_build', 'admin_id', 'integer after `city_id`');
	}

	public function down()
	{
		$this->dropColumn('mall_build','admin_id');
	}


}