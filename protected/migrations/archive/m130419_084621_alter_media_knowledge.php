<?php

class m130419_084621_alter_media_knowledge extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('media_knowledge', 'read_more', 'VARCHAR(3000) NOT NULL DEFAULT ""');
	}

	public function down()
	{
		return true;
	}
}