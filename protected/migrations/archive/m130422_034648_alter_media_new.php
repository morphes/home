<?php

class m130422_034648_alter_media_new extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_new', 'read_more', 'VARCHAR(3000) NOT NULL DEFAULT "" after content');
	}

	public function down()
	{
		$this->dropColumn('media_new', 'read_more');
	}

}