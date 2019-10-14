<?php

class m130517_104418_alter_table_media_new extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_new','selected_category','varchar(250) NOT NULL after read_more ');
	}

	public function down()
	{
		$this->dropColumn('media_new','selected_category');
	}

}