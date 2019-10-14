<?php

class m130429_033552_alter_cat_store extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store', 'tariff_id_new', 'TINYINT(1) after tariff_id');
		$this->addColumn('cat_store', 'tariff_enable_date', 'INT(11) after tariff_id_new');
	}

	public function down()
	{
		$this->dropColumn('cat_store', 'tariff_id_new');
		$this->dropColumn('cat_store', 'tariff_enable_date');
	}
}