<?php

class m130313_070310_index_product_tab extends CDbMigration
{
	public function up()
	{
		$this->createTable('index_product_tab', array(
			'id'          => 'pk',
			'name'        => 'VARCHAR(32) NOT NULL',
			'url'         => 'VARCHAR(255) DEFAULT "#"',
			'position'    => 'integer',
			'rubric'      => 'VARCHAR(3000) NOT NULL',
			'create_time' => 'integer',
			'update_time' => 'integer',
		));
	}

	public function down()
	{
		$this->dropTable('index_product_tab');
	}
}