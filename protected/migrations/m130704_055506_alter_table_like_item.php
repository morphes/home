<?php

class m130704_055506_alter_table_like_item extends CDbMigration
{
	public function up()
	{
		$this->addColumn('like_item','guest_id','INT(11) NULL DEFAULT "0" AFTER `author_id`');
	}

	public function down()
	{
		$this->dropColumn('like_item','guest_id');
	}

}
