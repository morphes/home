<?php

class m130725_013841_alter_table_cat_category extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_category', 'seo_top_desc', 'VARCHAR(3000) after product_qt');
		$this->addColumn('cat_category', 'seo_bottom_desc', 'VARCHAR(3000) after seo_top_desc');
	}

	public function down()
	{
		$this->dropColumn('cat_category', 'seo_top_desc');
		$this->dropColumn('cat_category', 'seo_bottom_desc');
	}

}