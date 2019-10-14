<?php

class m140511_032238_alter_table_cat_store extends CDbMigration
{
	public function up()
	{
        $this->addColumn('cat_store', 'anchor', 'varchar(3000) default NULL');
    }

	public function down()
	{
		echo "m140511_032238_alter_table_cat_store does not support migration down.\n";
        $this->dropColumn('cat_store', 'anchor');
        return false;
	}
}