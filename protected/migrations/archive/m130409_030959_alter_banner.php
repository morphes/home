<?php

class m130409_030959_alter_banner extends CDbMigration
{
	public function up()
	{
		$this->addColumn('banner_item', 'swf_file_id', 'integer after `file_id`');
		$this->addColumn('banner_rotation', 'swf_file_id', 'integer after `file_id`');

	}

	public function down()
	{
		$this->dropColumn('banner_item', 'swf_file_id');
		$this->dropColumn('banner_rotation', 'swf_file_id');
	}
}