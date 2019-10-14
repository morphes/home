<?php

class m130606_021819_alter_cat_store_offer extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store_offer','selected_services','VARCHAR(100) NOT NULL after comment ');
	}

	public function down()
	{
		$this->dropColumn('cat_store_offer','selected_services');

	}


}