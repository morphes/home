<?php

class Catalog2ActiveRecord extends EActiveRecord
{

    private static $db_catalog2 = null;

    protected static function getCatalog2DbConnection()
    {
        if (self::$db_catalog2 !== null)
            return self::$db_catalog2;
        else
        {
            self::$db_catalog2 = Yii::app()->dbcatalog2;
            if (self::$db_catalog2 instanceof CDbConnection)
            {
                self::$db_catalog2->setActive(true);
                return self::$db_catalog2;
            }
            else
                throw new CDbException(Yii::t('yii','Active Record requires a "db" CDbConnection application component.'));
        }
    }

    public function getDbConnection()
    {
        return self::getCatalog2DbConnection();
    }
}