<?php

class m130910_030336_create_table_specialist_rate extends CDbMigration
{
	public function up()
	{
		$this->createTable('specialist_rate', array(
			'id'	=> 'pk',
			'name'     => 'varchar(255)',
			'packet_3'     => 'integer',
			'discount_3'     => 'integer',
			'packet_7'     => 'integer',
			'discount_7'     => 'integer',
			'packet_14'     => 'integer',
			'discount_14'     => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

	}

	public function down()
	{
		$this->dropTable('specialist_rate');
	}
}