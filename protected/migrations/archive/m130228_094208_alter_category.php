<?php

class m130228_094208_alter_category extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_category', 'image_id', 'INT(11) after user_id');
		$this->dropColumn('cat_category', 'css');

		$this->createTable('cat_category_room', array(
			'category_id' => 'INT(11) NOT NULL',
			'room_id' => 'INT(11) NOT NULL, PRIMARY KEY (`category_id`, `room_id`)',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');
		$this->addColumn('cat_category', 'popular', 'VARCHAR(3000) NOT NULL DEFAULT "" after `desc`');

		$data = serialize(array());
		$sql = 'UPDATE cat_category SET popular=:p';
		Yii::app()->db->createCommand($sql)->bindParam(':p', $data)->execute();
	}

	public function down()
	{
		$this->dropColumn('cat_category', 'image_id');
		$this->addColumn('cat_category', 'css', 'VARCHAR(50) NOT NULL DEFAULT "" after `desc`');
		$this->dropTable('cat_category_room');
		$this->dropColumn('cat_category', 'popular');
	}

}