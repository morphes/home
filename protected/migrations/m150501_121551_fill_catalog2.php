<?php

class m150501_121551_fill_catalog2 extends CDbMigration
{
    const COPY_WITH_DATA = 1; // copy structure only
    const COPY_STRUCTURE = 2; // copy structure and data

    private $tablesToCopy = [
        'cat_category' => self::COPY_WITH_DATA,
        'cat_color' => self::COPY_WITH_DATA,
        'cat_category_room' => self::COPY_WITH_DATA,
        'cat_csv' => self::COPY_STRUCTURE,
        'cat_export_csv' => self::COPY_STRUCTURE,
        'cat_folders' => self::COPY_STRUCTURE,
        'cat_folder_discount_task' => self::COPY_STRUCTURE,
        'cat_folder_item' => self::COPY_STRUCTURE,
        'cat_import_csv' => self::COPY_STRUCTURE,
        'cat_chain' => self::COPY_STRUCTURE,
        'cat_chain_store' => self::COPY_STRUCTURE,
        'cat_contractor' => self::COPY_STRUCTURE,
        'cat_contractor_contact' => self::COPY_STRUCTURE,
        'cat_feedback' => self::COPY_STRUCTURE,
        'cat_group_operation' => self::COPY_STRUCTURE,
        'cat_option' => self::COPY_WITH_DATA,
        'cat_product' => self::COPY_STRUCTURE,
        'cat_product_image' => self::COPY_STRUCTURE,
        'cat_product_room' => self::COPY_STRUCTURE,
        'cat_product_on_photos' => self::COPY_STRUCTURE,
        'stat_store' => self::COPY_STRUCTURE,
        'cat_store' => self::COPY_STRUCTURE,
        'cat_store_feedback' => self::COPY_STRUCTURE,
        'cat_store_gallery' => self::COPY_STRUCTURE,
        'cat_store_geo' => self::COPY_STRUCTURE,
        'cat_store_news' => self::COPY_STRUCTURE,
        'cat_store_offer' => self::COPY_STRUCTURE,
        'cat_store_price' => self::COPY_STRUCTURE,
        'cat_store_vendor' => self::COPY_STRUCTURE,
        'cat_store_moderator' => self::COPY_STRUCTURE,
        'cat_style' => self::COPY_WITH_DATA,
        'cat_tapestore' => self::COPY_STRUCTURE,
        'cat_store_city' => self::COPY_STRUCTURE,
        'cat_tapestore_category' => self::COPY_STRUCTURE,
        'cat_value' => self::COPY_WITH_DATA,
        'cat_value_file' => self::COPY_STRUCTURE,
        'cat_vendor' => self::COPY_WITH_DATA,
        'cat_vendor_contractor' => self::COPY_STRUCTURE,
        'cat_vendor_collection' => self::COPY_STRUCTURE,
        'cat_similar_product' => self::COPY_STRUCTURE,
        'country' => self::COPY_WITH_DATA,
        'city' => self::COPY_WITH_DATA,
        'region' => self::COPY_WITH_DATA,
        'cat_main_room' => self::COPY_WITH_DATA,
        'cat_main_unit' => self::COPY_STRUCTURE,
        'cat_main_unit_category' => self::COPY_STRUCTURE,
        'cat_main_unit_room' => self::COPY_STRUCTURE,
    ];


	public function safeUp()
	{
        $catalog1Db = Yii::app()->db;
        $catalog2Db = Yii::app()->dbcatalog2;

        $catalog1DbName = $this->getDbName($catalog1Db);
        $catalog2DbName = $this->getDbName($catalog2Db);

        foreach ($this->tablesToCopy as $tableName => $copyType) {
            $catalog2Db->createCommand("CREATE TABLE {$catalog2DbName}.{$tableName} LIKE {$catalog1DbName}.{$tableName}")
                ->execute();

            if ($copyType === self::COPY_WITH_DATA) {
                if ($tableName !== 'cat_value') {
                    $catalog2Db->createCommand("INSERT INTO {$catalog2DbName}.{$tableName} SELECT * FROM {$catalog1DbName}.{$tableName}")
                        ->execute();
                } else {
                    $catalog2Db->createCommand("INSERT INTO {$catalog2DbName}.{$tableName} SELECT * FROM {$catalog1DbName}.{$tableName} WHERE {$tableName}.product_id IS NULL")
                        ->execute();
                }

            }
            echo "\n$tableName was copied";
        }
        echo "\n";
	}

	public function safeDown()
	{
        $this->setDbConnection(Yii::app()->dbcatalog2);
        foreach ($this->tablesToCopy as $tableName => $copyType) {
            $this->dropTable($tableName);
        }
	}


    /**
     * Get database name
     * @param CDbConnection $db
     * @return mixed
     */
    private function getDbName(CDbConnection $db)
    {
        $dsn = explode('=', $db->connectionString);
        return $dsn[2];
    }
}