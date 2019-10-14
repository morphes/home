<?php

class m130715_054451_alter_store extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store', 'status', 'TINYINT(1) DEFAULT 1 after type');
	}

	public function down()
	{
		$this->dropColumn('cat_store', 'status');
	}

}