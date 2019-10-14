<?php

class m190808_060715_create_table_company extends CDbMigration
{
	public function up()
	{
        $this->createTable('company', array(
            'id' => 'pk',
            'name' => 'string NOT NULL',
            'email' => 'string',
            'password' => 'string NOT NULL',
            'inn' => 'string NOT NULL',
            'city_id' => 'integer',
            'phone' => 'string',
            'phone_search' => 'string',
            'address' => 'string',
            'status' => 'boolean',
        ));
	}

	public function down()
	{
		$this->dropTable('company');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}