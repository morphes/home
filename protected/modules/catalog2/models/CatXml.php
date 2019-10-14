<?php

/**
 * This is the model class for table "cat_xml".
 *
 * The followings are the available columns in table 'cat_xml':
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property integer $store_id
 * @property string $file
 * @property string $progress
 * @property integer $create_time
 * @property integer $update_time
 */
class CatXml extends CActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_FINISHED = 3;
    const STATUS_FAILED = 4;
    const STATUS_CANCELED = 5;

    static public $statuses = array(
        self::STATUS_NEW         => 'Новый',
        self::STATUS_IN_PROGRESS => 'В процессе',
        self::STATUS_FINISHED    => 'Завершенный',
        self::STATUS_FAILED      => 'Ошибка',
    );

	public function getDbConnection()
	{
		return Yii::app()->dbcatalog2;
	}

	public function tableName()
	{
		return 'cat_xml';
	}

	public function rules()
	{
		return array(
			array('user_id, status, store_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('file', 'length', 'max'=>400),
			array('progress', 'length', 'max'=>1000),
			array('id, user_id, status, store_id, file, progress, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

    public function relations()
    {
        return array(
            'author' => array(self::BELONGS_TO, 'User', 'user_id')
        );
    }

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'status' => 'Status',
			'store_id' => 'Store',
			'file' => 'File',
			'progress' => 'Progress',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}