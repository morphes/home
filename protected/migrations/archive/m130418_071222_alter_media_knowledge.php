<?php

class m130418_071222_alter_media_knowledge extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge', 'read_more', 'VARCHAR(500) NOT NULL DEFAULT "" after content');
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'read_more');
	}

}