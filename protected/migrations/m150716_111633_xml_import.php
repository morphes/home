<?php

class m150716_111633_xml_import extends CDbMigration
{
	public function up()
	{
        $this->setDbConnection(Yii::app()->dbcatalog2);

        $this->addColumn('cat_product', 'store_id', 'integer not null after `user_id`');
        $this->addColumn('cat_product', 'store_inner_id', 'integer not null after `store_id`');
        $this->addColumn('cat_store', 'xml_parser_id', 'tinyint(1) after `type`');
        $this->addColumn('cat_store', 'xml_url', 'varchar(1000) not null default ""');

	    $this->createTable('cat_xml', [
            'id' => 'pk',
            'user_id' => 'integer not null',
            'status' => 'tinyint(1) not null',
            'store_id' => 'integer not null',
            'file' => 'varchar(400)',
            'progress' => 'varchar(1000)',
            'create_time' => 'integer not null',
            'update_time' => 'integer not null',
        ], 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

        $this->createIndex('idx_store_id', 'cat_xml', 'store_id');
        $this->createIndex('idx_status', 'cat_xml', 'status');
        $this->createIndex('idx_user_id', 'cat_xml', 'user_id');

        $this->createIndex('idx_store_id', 'cat_product', 'store_id');
        $this->createIndex('idx_store_inner_id', 'cat_product', 'store_inner_id');
    }

	public function down()
	{
        $this->setDbConnection(Yii::app()->dbcatalog2);

        $this->dropColumn('cat_product', 'store_id');
        $this->dropColumn('cat_product', 'store_inner_id');
        $this->dropColumn('cat_store', 'xml_parser_id');
        $this->dropColumn('cat_store', 'xml_url');
        $this->dropTable('cat_xml');
	}
}