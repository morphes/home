<?php

class m130329_081904_alter_cat_feedback extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_feedback', 'parent_id', 'integer after `product_id`');
	}

	public function down()
	{
		$this->dropColumn('cat_feedback', 'parent_id');
	}
}