<?php

class m130313_020010_create_includes extends CDbMigration
{
	public function up()
	{
		$this->createTable('includes', array(
			'id'          => 'pk',
			'key'         => 'VARCHAR(32) NOT NULL',
			'text'        => 'VARCHAR(3000) DEFAULT ""',
			'create_time' => 'integer',
			'update_time' => 'integer'
		));
	}

	public function down()
	{
		$this->dropTable('includes');
	}
}