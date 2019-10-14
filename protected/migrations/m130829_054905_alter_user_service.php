<?php

class m130829_054905_alter_user_service extends CDbMigration
{
	public function up()
	{
		$this->addColumn('service', 'seo_top_desc', 'VARCHAR(3000) after name');
		$this->addColumn('service', 'seo_bottom_desc', 'VARCHAR(3000) after seo_top_desc');
	}

	public function down()
	{
		$this->dropColumn('service', 'seo_top_desc');
		$this->dropColumn('service', 'seo_bottom_desc');
	}
}