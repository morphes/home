<?php

class m130403_033556_alter_banner_tables extends CDbMigration
{
	public function up()
	{
		$this->addColumn('banner_rotation', 'item_section_id', 'integer after `section_id`');
		$this->addColumn('banner_item_section_geo', 'item_section_id', 'integer after `section_id`');
	}

	public function down()
	{
		return true;
	}
}