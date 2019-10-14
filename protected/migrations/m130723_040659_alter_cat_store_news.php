<?php

class m130723_040659_alter_cat_store_news extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_store_news', 'count_comment', 'INT(11) NOT NULL DEFAULT 0 after rating');
	}

	public function down()
	{
		$this->dropColumn('cat_store_news', 'count_comment');
	}


}