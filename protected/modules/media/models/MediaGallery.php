<?php

/**
 * This is the model class for table "media_gallery".
 *
 * The followings are the available columns in table 'media_gallery':
 * @property integer $id
 * @property integer $author_id
 * @property string $model
 * @property integer $model_id
 * @property integer $image_id
 * @property string $description
 * @property integer $create_time
 */
class MediaGallery extends EActiveRecord implements IUploadImage
{


	public $upload;

	public static $preview = array(
		'crop_60' => array(60, 60, 'crop', 80),
		'crop_80' => array(80, 80, 'crop', 80), // in view
		'crop_210' => array(210, 210, 'crop', 80), // preview
		'crop_280x200' => array(280, 200, 'crop', 80),
		'crop_300x213' => array(300, 213, 'crop', 80),
		'crop_700x450' => array(700, 450, 'crop', 90),
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
		if ($this->getIsNewRecord())
			$this->create_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaGallery the static model class
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
		return 'media_gallery';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model_id, image_id, create_time', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>100),
			array('description', 'length', 'max'=>3000),
			array('model, model_id, image_id', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, model, model_id, image_id, description, create_time', 'safe', 'on'=>'search'),
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
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'model' => 'Model',
			'model_id' => 'Model',
			'image_id' => 'Image',
			'description' => 'Description',
			'create_time' => 'Create Time',
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
		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			// Путь до папки с фотографиями галереи
			case 'photo': return 'media/gallery/'.intval($this->id / 10000); break;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			// Имя изображения в галерее
			case 'photo': return 'gallery'.$this->id.'_'.time(); break;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->author_id;
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
		return !is_null($this->author_id) && $this->author_id == Yii::app()->user->id;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'photo': return array(
				'realtime' => array(
					self::$preview['crop_80'],
				),
				'background' => array(
					self::$preview['crop_60'],
					self::$preview['crop_700x450'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}

	// end IUploadImage
}