<?php

class m130320_032012_alter_category extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('cat_category', 'popular', 'TEXT');
	}

	public function down()
	{
		$this->alterColumn('cat_category', 'popular', 'VARCHAR(3000) NOT NULL DEFAULT ""');
	}


}