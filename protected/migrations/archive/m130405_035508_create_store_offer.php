<?php

class m130405_035508_create_store_offer extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_store_offer', array(
			'id'            => 'pk',
			'company'       => 'VARCHAR(50)',
			'city_id'       => 'integer',
			'company_phone' => 'VARCHAR(50)',
			'email'         => 'VARCHAR(50)',
			'name'          => 'VARCHAR(70)',
			'job'           => 'VARCHAR(50)',
			'site'          => 'VARCHAR(255)',
			'comment'       => 'TEXT(1000)',
			'accept_rule'   => 'TINYINT(1) DEFAULT 0',
			'status'        => 'TINYINT(1) DEFAULT 0',
			'create_time'   => 'integer',
			'update_time'   => 'integer'
		));
	}

	public function down()
	{
		$this->dropTable('cat_store_offer');
	}
}