<?php

class m130410_035130_alter_banner extends CDbMigration
{
	public function up()
	{
		$this->addColumn('banner_item', 'url', 'varchar(500) after `customer`');
		$this->addColumn('banner_rotation', 'status', 'tinyint(1) after `type_id`');
		$this->createIndex('idx_banner_status', 'banner_rotation', 'status');
	}

	public function down()
	{
		$this->dropColumn('banner_item', 'url');
		$this->dropColumn('banner_rotation', 'status');
	}
}