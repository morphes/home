<?php

class m130726_044702_aleter_folder extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_folders', 'image_id', 'INT(11) after user_id');
	}

	public function down()
	{
		$this->dropColumn('cat_folders', 'image_id');
	}
}