<?php

class m160327_133545_alter_product extends CDbMigration
{
	public function up()
	{
		$this->setDbConnection(Yii::app()->dbcatalog2);
		$this->alterColumn('cat_product', 'store_inner_id', 'string');
	}

	public function down()
	{
        $this->setDbConnection(Yii::app()->dbcatalog2);
        $this->alterColumn('cat_product', 'store_inner_id', 'integer');
	}
}