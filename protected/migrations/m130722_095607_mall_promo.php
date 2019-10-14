<?php

class m130722_095607_mall_promo extends CDbMigration
{
	public function up()
	{
		$this->createTable('mall_promo', array(
			'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
			'mall_id' => 'INT(11) NOT NULL',
			'user_id' => 'INT(11) NOT NULL',
			'position' => 'INT(11) NOT NULL DEFAULT 0',
			'status' => 'TINYINT(1) NOT NULL',
			'image_id' => 'INT(11) NOT NULL DEFAULT 0',
			'name' => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'url' => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'create_time' => 'INT(11) NOT NULL',
			'update_time' => 'INT(11) NOT NULL, PRIMARY KEY (`id`)',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');
	}

	public function down()
	{
		$this->dropTable('mall_promo');
	}

}