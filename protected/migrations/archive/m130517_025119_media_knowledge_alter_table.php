<?php

class m130517_025119_media_knowledge_alter_table extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge','selected_category','varchar(250) NOT NULL after cat_category_name ');
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'selected_category');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}