<?php

class m130404_045353_alter_media_knowledge extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge', 'rss', 'TINYINT(1) NOT NULL DEFAULT 1 after whom_interest');
		$this->addColumn('media_new', 'rss', 'TINYINT(1) NOT NULL DEFAULT 1 after whom_interest');
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'rss');
		$this->dropColumn('media_new', 'rss');
	}
}