<?php

class m130315_043026_alter_seo_rewrite extends CDbMigration
{
	public function up()
	{
		$this->addColumn('seo_rewrite', 'normal_url', 'TEXT');
		$this->addColumn('seo_rewrite', 'subdomain', 'VARCHAR(255) NOT NULL DEFAULT "" after path');

	}

	public function down()
	{
		$this->dropColumn('seo_rewrite', 'normal_url');
		$this->dropColumn('seo_rewrite', 'subdomain');
	}
}