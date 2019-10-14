<?php

class m130906_075804_create_table_spec_priority extends CDbMigration
{

	public function up()
	{
		$this->createTable('user_service_priority', array(
			'id'	=> 'pk',
			'user_id'     => 'integer',
			'service_id'     => 'integer',
			'city_id'     =>  'integer',
			'date_start'   => 'integer',
			'date_end'   => 'integer',
			'status'     => 'TINYINT',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

	}

	public function down()
	{
		$this->dropTable('user_service_priority');
	}

}