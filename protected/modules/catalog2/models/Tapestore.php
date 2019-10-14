<?php

/**
 * This is the model class for table "cat_tapestore".
 *
 * The followings are the available columns in table 'cat_tapestore':
 * @property integer $id
 * @property integer $status
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $image_id
 * @property integer $position
 * @property integer $city_id
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $create_time
 * @property integer $update_time
 */
class Tapestore extends Catalog2ActiveRecord implements IUploadImage
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;
	const STATUS_DELETED = 3;

	public static $statusNames = array(
		self::STATUS_ENABLED => 'Включен',
		self::STATUS_DISABLED => 'Выключен',
		self::STATUS_DELETED => 'Удален',
	);

	public static $preview = array(
		'resize_90' => array(90, 90, 'resize', 80), // in update page
		'resize_148x68' => array(148, 68, 'resize', 80)
	);
	// Логотип
	public $file;

	private $_store=false;
	private $_image=false;
	private $_imageType = null;

	public function init()
	{
		parent::init();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tapestore the static model class
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
		return 'cat_tapestore';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, user_id, start_time, end_time, city_id', 'required'),
			array('status, user_id, start_time, end_time, create_time, update_time, city_id', 'numerical', 'integerOnly'=>true),
			array('file', 'file', 'types'=> 'jpg, bmp, png, jpeg', 'maxFiles'=> 1, 'maxSize' => 104857600000, 'allowEmpty' => true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, store_id, user_id, start_time, end_time, city_id', 'safe', 'on'=>'search'),
		);
	}

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'city' => array(self::BELONGS_TO, 'City', 'city_id'),
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
			'store_id' => 'Магазин',
			'user_id' => 'Автор',
			'image_id' => 'Превью',
			'city_id'     =>'Город показов',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'start_time' => 'Дата нач. показов',
			'end_time' => 'Дата оконч. показов',
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
		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('start_time',$this->start_time);
		$criteria->compare('end_time',$this->end_time);

		$criteria->order = 'position ASC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return UploadedFile|null
	 */
	public function getImage()
	{
		if ($this->_image !== false)
			return $this->_image;

		$this->_image = UploadedFile::model()->findByPk($this->image_id);
		return $this->_image;
	}

	/**
	 * Получение пути до привязанной фоточки, если нет - дефолтная
	 * @return string
	 */
	public function getImageByConfig($config)
	{
		$image = $this->getImage();
		if ( $image===null )
			$image = new UploadedFile();

		return $image->getPreviewName($config);
	}

	/**
	 * Возваращает статус для записи в виде html строки,
	 * с расцветкой взависимости от статуса.
	 *
	 * @return string
	 */
	public function getStatusHtml()
	{
		$html = '';
		if (isset(self::$statusNames[$this->status])) {

			switch($this->status) {
				case self::STATUS_ENABLED:
					$cls = 'success';
					break;
				case self::STATUS_DISABLED:
					$cls = 'important';
					break;
				default:
					$cls = '';
			}
			$html .= CHtml::tag(
				'span',
				array(
					'class' => 'item-status label ' . $cls,
					'data-id' => $this->id
				),
				self::$statusNames[$this->status]
			);

		} else {
			$html .= CHtml::tag('span', array('class'=>'item-status', 'data-id'=>$this->id), 'N/A');
		}

		return $html;
	}

	/**
	 * @return Store
	 */
	public function getStore()
	{
		if ($this->_store !== false)
			return $this->_store;

		$this->_store = Store::model()->findByPk($this->store_id);

		return $this->_store;
	}

	/**
	 * Построение дерева
	 *
	 * Новая реализация построителя дерева
	 * Строит по выборке без подзапросов
	 * @note Использует только целые или строковые ключи
	 * @param $models
	 * @return array
	 */
	public static function getTree($selected=array(),$expand=true) {

		$models = Category::model()->findAll(array('order'=>'lft'));

		$levels = array();
		$items = array();
		$tree = array();

		foreach ($models as $node) {
			$primary = $node->id;
			$currentLevel = $node->level;
			$item = array(
				'key' => $primary,
				'title' => $node->name,
				'select' => isset( $selected[$primary] ) , //true,
				'noLink' => true,
				'checkbox' => false,
				'expand' => $expand,
			);
			$items[ $primary ] = $item;

			$levels[ $currentLevel ] = $primary;

			if ( $node->isRoot() ) {
				$tree[] = &$items[ $primary ];
				continue;
			}

			$parent = &$items[ $levels[ $currentLevel-1 ] ];
			$parent['children'][] = &$items[ $primary ];
			//$parent['isFolder'] = true;
		}

		return $tree;
	}

	public static function getSelectedCategories($itemId)
	{
		$sql = 'SELECT category_id, 1 FROM cat_tapestore_category WHERE tapestore_id=:uid';
		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':uid', $itemId)->setFetchMode(PDO::FETCH_KEY_PAIR)->queryAll();
		return $data;
	}

	public static function updateCategories($selected, $itemId)
	{
		$sql = 'DELETE FROM cat_tapestore_category WHERE tapestore_id=:uid';
		Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':uid', $itemId)->execute();

		if (!empty($selected)) {
			$sql = 'INSERT INTO  cat_tapestore_category (`category_id`, `tapestore_id`) VALUES ';
			$sqlValues = array();
			foreach ($selected as $key => $item) {
				$sqlValues[] = '('.intval($key).','.$itemId.')';
			}

			$sql .= implode(',', $sqlValues);

			Yii::app()->dbcatalog2->createCommand($sql)->execute();
		}
	}

	/**
	 * Получение пути для сохранения файла, в формате для uploadedFile
	 * @return string
	 */
	public function getImagePath()
	{
		switch ($this->_imageType) {
			case 'logo': return '/catalog/tapestore';
			default: throw new CException('Invalid upload image type');
		}
	}

	/**
	 * Получение имени для сохранения файла, в формате для uploadedFile
	 * @return string
	 */
	public function getImageName()
	{
		switch ($this->_imageType) {
			case 'logo': return 'logo_' . time() . '_' . rand(0,99);
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		if ($this->user_id !== null)
			return $this->user_id;

		return Yii::app()->getUser()->getId();
	}

	/**
	 * Проверка доступа к объекту пользователем
	 * @return bool true-имеет доступ
	 */
	public function checkAccess()
	{
		return false;
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
			case 'logo': return array(
				'realtime' => array(
					self::$preview['resize_90'],
				),
				'background' => array(
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
}