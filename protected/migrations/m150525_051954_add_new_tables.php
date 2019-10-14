<?php

class m150525_051954_add_new_tables extends CDbMigration
{
    const COPY_WITH_DATA = 1; // copy structure only
    const COPY_STRUCTURE = 2; // copy structure and data

    private $tablesToCopy = [
        'mall_build' => self::COPY_STRUCTURE,
        'mall_build_service' => self::COPY_STRUCTURE,
        'mall_floor' => self::COPY_STRUCTURE,
        'mall_promo' => self::COPY_STRUCTURE,
        'mall_service' => self::COPY_STRUCTURE,
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