<?php

class m130627_101931__alter_forum_topic extends CDbMigration
{
	public function up()
	{
		$this->addColumn('forum_topic','author_ip','BIGINT(11) NOT NULL after author_id');
	}

	public function down()
	{
		$this->dropColumn('forum_topic','author_ip');

	}
}