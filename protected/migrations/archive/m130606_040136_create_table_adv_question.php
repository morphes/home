<?php

class m130606_040136_create_table_adv_question extends CDbMigration
{
	public function up()
	{
		$this->createTable('adv_question', array(
			'id'       => 'pk',
			'author_name'=> 'VARCHAR(60)',
			'email' => 'VARCHAR(60)',
			'question' => 'VARCHAR(3000)',
			'status'    => 'TINYINT(1) NOT NULL',
			'create_time' => 'integer',
			'update_time' => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

	}

	public function down()
	{
		$this->dropTable('adv_question');
	}

}