<?php

class m130819_033845_alter_userdata extends CDbMigration
{
	public function up()
	{
		$this->addColumn('user_data', 'service_rating', 'DOUBLE(15,2) NOT NULL DEFAULT 0');
//		$this->addColumn('user_data', 'project_qt', 'INT(11) NOT NULL DEFAULT 0');
	}

	public function down()
	{
		$this->dropColumn('user_data', 'service_rating');
//		$this->dropColumn('user_data', 'project_qt');
	}

}