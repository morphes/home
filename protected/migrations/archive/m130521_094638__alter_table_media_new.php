<?php

class m130521_094638__alter_table_media_new extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_new','article_first','integer NOT NULL after selected_category ');
		$this->addColumn('media_new','article_second','integer NOT NULL after article_first');
		$this->addColumn('media_new','article_third','integer NOT NULL after article_second ');
	}

	public function down()
	{
		$this->dropColumn('media_new', 'article_first');
		$this->dropColumn('media_new', 'article_second');
		$this->dropColumn('media_new', 'article_third');

	}
}