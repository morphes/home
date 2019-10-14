<?php

class m130930_041423_alter_banner extends CDbMigration
{
	public function up()
	{
		$this->addColumn('banner_item', 'htmlcode', 'varchar(3000) not null default ""');
	}

	public function down()
	{
		$this->dropColumn('banner_item', 'htmlcode');
	}
}