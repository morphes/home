<?php

/**
 * This is the model class for table "media_people".
 *
 * The followings are the available columns in table 'media_people':
 * @property integer $id
 * @property integer $status
 * @property string $fio
 * @property string $job
 * @property string $message
 * @property string $url
 * @property integer $image_id
 * @property integer $create_time
 * @property integer $update_time
 */
class MediaPeople extends EActiveRecord implements IUploadImage
{
	// --- СТАТУСЫ ---
	const STATUS_PUBLIC 	= 1; // Опубикован
	const STATUS_HIDE 	= 2; // Скрыт
	const STATUS_DELETED 	= 3; // Удален

	public static $statusNames = array(
		self::STATUS_PUBLIC => 'Опубликован',
		self::STATUS_HIDE => 'Скрыт'
	);

	public static $preview = array(
		'crop_80' => array(80, 80, 'crop', 80),
		'crop_120' => array(120, 120, 'crop', 80),
		'crop_210' => array(210, 210, 'crop', 80),
	);

	// Тип изображения для загрузки
	private $_imageType = null;

	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Update create_time and update_time in object
	 */
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
	 * @return MediaPeople the static model class
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
		return 'media_people';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fio, message, job, url, image_id', 'required'),
			array('status, image_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('fio, message, url', 'length', 'max'=>255),
			array('job', 'length', 'max'=>500),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, fio, job, message, url, image_id, create_time, update_time', 'safe', 'on'=>'search'),
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
			'photo'=>array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'status' => 'Статус',
			'fio' => 'ФИО',
			'job' => 'Специализация',
			'message' => 'Цитата',
			'url' => 'Ссылка на статью',
			'image_id' => 'Фотография',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
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
		$criteria->compare('status',$this->status);
		$criteria->compare('fio',$this->fio,true);
		$criteria->compare('job',$this->job,true);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('update_time' => CSort::SORT_DESC);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort' => $sort
		));
	}


	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			// Фотка превью для всей статьи
			case 'photo': return 'media/people/'.intval($this->id % 10); break;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			// Имя превью файла
			case 'photo': return 'people'.$this->id.'_'.time(); break;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return Yii::app()->user->id;
	}

	public function setImageType($name)
	{
		$this->_imageType = $name;
	}

	public function flushImageType()
	{
		$this->_imageType = null;
		return true;
	}

	public function checkAccess()
	{
		return true;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'photo': return array(
				'realtime' => array(
					self::$preview['crop_80'],
					self::$preview['crop_120'],
					self::$preview['crop_210'],
				),
				'background' => array(

				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}

	// end of IUploadImage
}