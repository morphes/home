<?php

/**
 * This is the model class for table "mall_promo".
 *
 * The followings are the available columns in table 'mall_promo':
 * @property integer $id
 * @property integer $mall_id
 * @property integer $user_id
 * @property integer $position
 * @property integer $status
 * @property integer $image_id
 * @property string $name
 * @property string $url
 * @property integer $create_time
 * @property integer $update_time
 */
class MallPromo extends Catalog2ActiveRecord implements IUploadImage
{

	const STATUS_ACTIVE = 1;
	const STATUS_DISABLED = 2;
	const STATUS_DELETED = 3;

	public static $statusNames = array(
		self::STATUS_ACTIVE => 'Активен',
		self::STATUS_DISABLED => 'Отключен',
		self::STATUS_DELETED => 'Удален',
	);

	public static $preview = array(
		'crop_220'=>array(220, 220, 'crop', 90),
		'crop_330x140'=>array(330, 140, 'crop', 80),
		'crop_940x400'=>array(940, 400, 'crop', 80),
	);

	private $_imageType=null;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MallPromo the static model class
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
		return 'mall_promo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('mall_id, user_id', 'required'),
			array('mall_id, user_id, image_id', 'numerical', 'integerOnly'=>true),
			array('name, url', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, mall_id, user_id, status, name, url, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'mall_id' => 'Mall',
			'user_id' => 'User',
			'status' => 'Status',
			'name' => 'Name',
			'url' => 'Url',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior'=>array(
				'class' => 'application.components.ModelTimeBehavior',
			),
			'PositionBehavior'=>array(
				'class' => 'application.components.PositionBehavior',
				'whereLimitField'=>'user_id',
			),
		);
	}

	public function getPreview($config)
	{
		if (!is_null($this->image_id)) {
			$uploadedFile = UploadedFile::model()->findByPk($this->image_id);
			if (!is_null($uploadedFile)) {
				$previewFile = $uploadedFile->getPreviewName($config);
				return $previewFile;
			}
		}
		$name = $config[0].'x'.$config[1];
		return UploadedFile::getDefaultImage('default', $name);
	}

	/**
	 * Получение пути для сохранения файла, в формате для uploadedFile
	 * @return string | false для новых записей
	 */
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'mallpromo': return 'mallpromo/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	/**
	 * Получение имени для сохранения файла, в формате для uploadedFile
	 * @return string | false для новых записей
	 */
	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'mallpromo': return 'promo_'.$this->id.'_'.time();
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->user_id;
	}

	/**
	 * Проверка доступа к объекту пользователем
	 * @return bool true-имеет доступ
	 */
	public function checkAccess()
	{
		return ( $this->user_id!==null && $this->user_id == Yii::app()->getUser()->getId() );
	}

	/**
	 * Установка типа загружаемого изображения для модели
	 * @return mixed
	 */
	public function setImageType($name)
	{
		$this->_imageType = $name;
	}

	/**
	 * Сброс установленного типа изображения
	 * @return mixed
	 */
	public function flushImageType()
	{
		$this->_imageType = null;
		return true;
	}

	/**
	 * Конфиг для получения превью в конкретной модели
	 * @return array
	 */
	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'mallpromo': return array(
				'realtime' => array(
					self::$preview['crop_220'],
				),
				'background' => array(
					self::$preview['crop_330x140'],
					self::$preview['crop_940x400'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
}