<?php

/**
 * This is the model class for table "cat_store_gallery".
 *
 * Реализует управление фотографиями для магазинов, у который
 * есть платный тариф. Как минимум TARIF_MINI_SITE.
 *
 * The followings are the available columns in table 'cat_store_gallery':
 * @property integer $id
 * @property integer $status
 * @property integer $user_id
 * @property integer $image_id
 * @property integer $store_id
 * @property integer $position
 * @property string $name
 * @property string $description
 * @property integer $create_time
 * @property integer $update_time
 */
class StoreGallery extends Catalog2ActiveRecord implements IUploadImage
{
	private $_imageType = null;

	const STATUS_NEW = 1;
	const STATUS_PUBLIC = 2;
	const STATUS_DELETE = 3;

	static public $statuses = array(
		self::STATUS_NEW      => 'Новая',
		self::STATUS_PUBLIC   => 'Опубликована',
		self::STATUS_DELETE   => 'Удалена пользователем',
	);

	public static $preview = array(
		'crop_60'          => array(60, 60, 'crop', 80),
		'crop_140'         => array(140, 140, 'crop', 80),
		'width_620'        => array(620, 0, 'resize', 80),
		'resize_1920x1080' => array(1920, 1080, 'resize', 80),
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StoreGallery the static model class
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
		return 'cat_store_gallery';
	}

	/**
	 * @return array validation rules for model attribute
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, store_id, status, image_id', 'required'),
			array('user_id, image_id, store_id, position, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('description', 'length', 'max'=>2000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, user_id, image_id, store_id, position, name, description, create_time, update_time', 'safe', 'on'=>'search'),
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
			'author' => array(self::BELONGS_TO, 'User', 'user_id'),
			'preview' => array(self::BELONGS_TO, 'UploadedFile', 'image_id')
		);
	}


	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
			'PositionBehavior'  => array(
				'class'           => 'application.components.PositionBehavior',
				'whereLimitField' => 'store_id'
			)
		);
	}



	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'user_id'     => 'Автор',
			'image_id'    => 'Изображение',
			'store_id'    => 'ID магазина',
			'position'    => 'Позиция',
			'name'        => 'Название',
			'description' => 'Описание',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('position',$this->position);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('create_time' => 'DESC');

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort'     => $sort
		));
	}

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'image': return 'storeGallery/'.intval($this->id / UploadedFile::PATH_SIZE + 1).'/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'image': return time() . 'image_' . rand(10, 99) . '_' . $this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->id;
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
		return !is_null($this->id) && $this->user_id == Yii::app()->user->id;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'image': return array(
				'realtime' => array(
					self::$preview['crop_60'],
					self::$preview['crop_140'],
				),
				'background' => array(
					self::$preview['width_620'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
	// end of IUploadImage

	/**
	 * Возваращает статус для записи в виде html строки,
	 * с расцветкой взависимости от статуса.
	 *
	 * @return string
	 */
	public function getStatusHtml()
	{
		$html = '';
		if (isset(self::$statuses[$this->status])) {

			switch($this->status) {
				case self::STATUS_NEW:
					$cls = 'default';
					break;
				case self::STATUS_PUBLIC:
					$cls = 'success';
					break;
				case self::STATUS_DELETE:
					$cls = 'important';
					break;
				default:
					$cls = '';
			}
			$html .= CHtml::tag(
				'span',
				array(
					'class'   => 'item_status label ' . $cls,
					'data-id' => $this->id
				),
				self::$statuses[$this->status]
			);

		} else {
			$html .= 'N/A';
		}

		return $html;
	}
}