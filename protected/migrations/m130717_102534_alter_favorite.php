<?php

class m130717_102534_alter_favorite extends CDbMigration
{
	public function up()
	{
		$this->addColumn('favorite_item', 'data', 'varchar(1000) after `model_id`');
	}

	public function down()
	{
		$this->dropColumn('favorite_item', 'data');
	}
}