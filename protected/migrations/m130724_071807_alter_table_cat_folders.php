<?php

class m130724_071807_alter_table_cat_folders extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_folder_item', 'position', 'INT(11) after model_id');
	}

	public function down()
	{
		$this->dropColumn('cat_folder_item', 'position');

	}

}