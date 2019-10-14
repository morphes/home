<?php

class m130313_033604_seo_rewrite extends CDbMigration
{
	public function up()
	{
		$this->createTable('seo_rewrite', array(
			'seo_md5' => 'CHAR(32) NOT NULL',
			'seo_url' => 'VARCHAR(255) NOT NULL',
			'status' => 'TINYINT(1) NOT NULL',
			'path' => 'VARCHAR(255) NOT NULL',
			'normal_md5' => 'CHAR(32) NOT NULL',
			'param' => 'VARCHAR(3000) NOT NULL DEFAULT ""',
			'desc' => 'VARCHAR(512) NOT NULL DEFAULT ""',
			'create_time' => 'integer not null',
			'update_time' => 'integer not null, PRIMARY KEY (`seo_md5`)'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('normal_md5', 'seo_rewrite', 'normal_md5');

	}

	public function down()
	{
		$this->dropTable('seo_rewrite');
	}


}