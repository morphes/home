<?php

class m130521_072623_alter_media extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge', 'meta_desc', 'VARCHAR(255) NOT NULL default "" after lead');
		$this->addColumn('media_new', 'meta_desc', 'VARCHAR(255) NOT NULL default "" after lead');
		$this->addColumn('media_event', 'meta_desc', 'VARCHAR(255) NOT NULL default "" after name');
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'meta_desc');
		$this->dropColumn('media_new', 'meta_desc');
		$this->dropColumn('media_event', 'meta_desc');
	}
}