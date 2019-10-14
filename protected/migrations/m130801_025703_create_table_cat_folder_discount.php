<?php

class m130801_025703_create_table_cat_folder_discount extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_folder_discount_task', array(
			'id'		=> 'pk',
			'model_id'     => 'integer',
			'store_id'     => 'integer',
			'discount'     =>  'FLOAT(7,3) NULL DEFAULT NULL ',
			'date_start'   => 'integer',
			'date_end'   => 'integer',
			'status'     => 'TINYINT',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('date_start', 'cat_folder_discount_task', 'date_start');
		$this->createIndex('date_end', 'cat_folder_discount_task', 'date_end');
		$this->createIndex('status', 'cat_folder_discount_task', 'status');
	}

	public function down()
	{
		$this->dropIndex('date_start', 'cat_folder_discount_task');
		$this->dropIndex('date_end', 'cat_folder_discount_task');
		$this->dropIndex('status', 'cat_folder_discount_task');
		$this->dropTable('cat_folder_discount_task');
	}
}