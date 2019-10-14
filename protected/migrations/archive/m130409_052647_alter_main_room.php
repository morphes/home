<?php

class m130409_052647_alter_main_room extends CDbMigration
{
	public function up()
	{
		$this->dropColumn('cat_main_room', 'eng_name');
		$this->addColumn('cat_main_room', 'genetive', 'VARCHAR(255) NOT NULL DEFAULT "" after name');

		$this->update('cat_main_room', array('genetive'=>'кухни'), 'id=:id', array(':id'=>2));
		$this->update('cat_main_room', array('genetive'=>'спальни'), 'id=:id', array(':id'=>3));
		$this->update('cat_main_room', array('genetive'=>'гостиной'), 'id=:id', array(':id'=>4));
		$this->update('cat_main_room', array('genetive'=>'кабинета'), 'id=:id', array(':id'=>5));
		$this->update('cat_main_room', array('genetive'=>'детской'), 'id=:id', array(':id'=>6));
		$this->update('cat_main_room', array('genetive'=>'ванной'), 'id=:id', array(':id'=>7));
		$this->update('cat_main_room', array('genetive'=>'прихожей'), 'id=:id', array(':id'=>8));
		$this->update('cat_main_room', array('genetive'=>'сада'), 'id=:id', array(':id'=>9));
		$this->update('cat_main_room', array('genetive'=>'столовой'), 'id=:id', array(':id'=>10));

	}

	public function down()
	{
		$this->addColumn('cat_main_room', 'eng_name', 'VARCHAR(255) NOT NULL DEFAULT "" after name');
		$this->dropColumn('cat_main_room', 'genetive');
	}
}