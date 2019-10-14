<?php

/**
 * This is the model class for table "cat_main_unit".
 *
 * The followings are the available columns in table 'cat_main_unit':
 * @property integer $id
 * @property integer $type_id
 * @property integer $status
 * @property integer $position
 * @property integer $file_id
 * @property integer $origin_id
 * @property integer $store_id
 * @property string $name
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $create_time
 * @property integer $update_time
 */
class MainUnit extends Catalog2ActiveRecord
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;
	const STATUS_DELETED = 3;

	public static $statusNames = array(
		self::STATUS_ENABLED => 'Включен',
		self::STATUS_DISABLED => 'Выключен',
		self::STATUS_DELETED => 'Удален',
	);

	const TYPE_VENDOR = 1;
	const TYPE_PRODUCT = 2;
	const TYPE_IDEA = 3;

	public static $preview = array(
		'crop_150' => array(150, 150, 'crop', 80), // in update page
		'crop_234x180' => array(234, 180, 'crop', 90), // Главная товаров - предложения
	);

	private $_origin=false;
	private $_image=false;

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MainUnit the static model class
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
		return 'cat_main_unit';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status', 'in', 'range'=>array(self::STATUS_ENABLED, self::STATUS_DISABLED, self::STATUS_DELETED), 'strict'=>false),

			array('type_id, origin_id', 'required', 'on'=>'vendor, product, idea'),
			array('type_id, status, file_id, origin_id, start_time, end_time', 'numerical', 'integerOnly'=>true, 'on'=>'vendor, product'),
			array('store_id', 'required', 'on'=>'product'),

			array('type_id, status, file_id, origin_id', 'numerical', 'integerOnly'=>true, 'on'=>'idea'),

			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, file_id, origin_id, store_id, name, start_time, end_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type_id' => 'Type',
			'status' => 'Статус',
			'file_id' => 'Файл',
			'origin_id' => 'ID оригинала',
			'store_id' => 'ссылка на магазин',
			'name' => 'Название',
			'start_time' => 'Дата нач. показов',
			'end_time' => 'Дата оконч. показов',
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
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('file_id',$this->file_id);
		$criteria->compare('origin_id',$this->origin_id);

		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('start_time',$this->start_time);
		$criteria->compare('end_time',$this->end_time);

		if (empty( $this->status )) {
			$criteria->addInCondition('status', array(self::STATUS_ENABLED, self::STATUS_DISABLED));
		} else {
			$criteria->compare('status',$this->status);
		}

		$criteria->order = 't.position ASC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function getVendorName()
	{
		if ($this->type_id != self::TYPE_VENDOR)
			return '';
		$vendor = $this->getOrigin();
		return is_null($vendor) ? '' : $vendor->name;
	}

	public function getOrigin()
	{
		if ($this->_origin !== false)
			return $this->_origin;

		switch ($this->type_id) {
			case self::TYPE_VENDOR: {
				$this->_origin = Vendor::model()->findByPk($this->origin_id);
			} break;
			case self::TYPE_PRODUCT: {
				$this->_origin = Product::model()->findByPk($this->origin_id);
			} break;
			case self::TYPE_IDEA: {
				$this->_origin = Interior::model()->findByPk($this->origin_id);
			} break;
			default: throw new CHttpException(500);
		}

		return $this->_origin;
	}

	/**
	 * @return UploadedFile|null
	 */
	public function getImage()
	{
		if ($this->_image !== false)
			return $this->_image;

		$this->_image = UploadedFile::model()->findByPk($this->file_id);
		return $this->_image;
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
	public static function getTree($selected=array()) {

		$models = Category::model()->findAll(array('order'=>'lft', 'condition'=> 'rgt-lft<>1'));

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
				'expand' => true,
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

}