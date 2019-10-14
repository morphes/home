<?php

class m130521_065721_alter_table_media_knowlrdge extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge','article_first','integer NOT NULL after selected_category ');
		$this->addColumn('media_knowledge','article_second','integer NOT NULL after article_first');
		$this->addColumn('media_knowledge','article_third','integer NOT NULL after article_second ');
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'article_first');
		$this->dropColumn('media_knowledge', 'article_second');
		$this->dropColumn('media_knowledge', 'article_third');

	}
}