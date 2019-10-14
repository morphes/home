<?php

class m130521_100814__alter_table_media_theme_select extends CDbMigration
{
	public function up()
	{
		$this->createIndex('modelId','media_theme_select','model_id');
	}

	public function down()
	{
		$this->dropIndex('modelId','media_theme_select');
	}


}