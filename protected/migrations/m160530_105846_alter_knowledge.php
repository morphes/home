<?php

class m160530_105846_alter_knowledge extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge', 'section_url', 'varchar(255)');
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'section_url');
	}
}