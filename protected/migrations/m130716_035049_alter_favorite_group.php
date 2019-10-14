<?php

class m130716_035049_alter_favorite_group extends CDbMigration
{
	public function up()
	{
		$this->createTable('shared', array(
			'id' => 'pk',
			'type' => 'tinyint(1) not null',
			'object_id' => 'integer not null',
			'user_id' => 'integer not null',
			'create_time' => 'integer not null',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('idx_share_uniq', 'shared', 'type, object_id, user_id', true);
	}

	public function down()
	{
		$this->dropTable('shared');
	}
}