<?php

class m130627_103552__alter_forum_answer extends CDbMigration
{
	public function up()
	{
		$this->addColumn('forum_answer','author_ip','BIGINT(11) NOT NULL after author_id');
	}

	public function down()
	{
		$this->dropColumn('forum_answer','author_ip');

	}
}