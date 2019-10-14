<?php

/**
 * This is the model class for table "cat_export_csv".
 *
 * The followings are the available columns in table 'cat_export_csv':
 * @property integer $id
 * @property integer $user_id
 * @property integer $vendor_id
 * @property integer $status
 * @property string $progress
 * @property string $import_file
 * @property integer $create_time
 * @property integer $update_time
 */
class CatImportCsv extends Catalog2ActiveRecord
{

	const STATUS_NEW = 1;
	const STATUS_IN_PROGRESS = 2;
	const STATUS_FINISHED = 3;
	const STATUS_FAILED = 4;


	static $statuses = array(
		self::STATUS_NEW         => 'Новый',
		self::STATUS_IN_PROGRESS => 'В процессе',
		self::STATUS_FINISHED    => 'Завершенный',
		self::STATUS_FAILED      => 'Ошибка',
	);

	public function init()
	{
		$this->onBeforeSave = array($this, 'setDate');

		return parent::init();
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CatExportCsv the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cat_import_csv';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, vendor_id', 'required'),
			array('user_id, vendor_id, status, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('progress', 'safe'),
			array('import_file', 'length', 'max'=>400),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, vendor_id, status, progress, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'               => 'ID',
			'user_id'          => 'Автор инициации экспорта',
			'vendor_id'        => 'Производитель',
			'status'           => 'Статус',
			'progress'         => 'Прогресс',
			'import_file'      => 'Файл',
			'create_time'      => 'Create Time',
			'update_time'      => 'Update Time',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('vendor_id',$this->vendor_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('progress',$this->progress,true);
		$criteria->compare('import_file',$this->import_file,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}