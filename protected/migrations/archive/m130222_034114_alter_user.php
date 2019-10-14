<?php

class m130222_034114_alter_user extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('user', 'firstname', 'VARCHAR(75) NOT NULL DEFAULT ""');
	}

	public function down()
	{
		$this->alterColumn('user', 'firstname', 'VARCHAR(45) NOT NULL DEFAULT ""');
	}

}