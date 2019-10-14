<?php

/**
 * This is the model class for table "unit_product".
 *
 * The followings are the available columns in table 'unit_product':
 * @property integer $id
 * @property integer $product_id
 * @property integer $status
 * @property integer $create_time
 */
class UnitProduct extends EActiveRecord
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;

	static public $statusNames = array(
		self::STATUS_ENABLED => 'включен',
		self::STATUS_DISABLED => 'выключен',
	);

	const UPLOAD_IMAGE_DIR = 'uploads/public/unitProduct/';

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
			$this->create_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UnitProduct the static model class
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
		return 'unit_product';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('product_id, status', 'numerical', 'integerOnly'=>true),
			array('product_id, status', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, product_id, status, create_time', 'safe', 'on'=>'search'),
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
			'product' => array(self::BELONGS_TO, 'Product', 'product_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'product_id'  => 'ID товара',
			'status'      => 'Статус',
			'create_time' => 'Дата создания',
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
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Возвращает название статуса с html подсветкой
	 * @return string
	 */
	public function getHtmlStatus()
	{
		if (isset(self::$statusNames[$this->status]))
			$nameStatus = self::$statusNames[$this->status];
		else
			$nameStatus = 'N/A';

		switch($this->status)
		{
			case self::STATUS_ENABLED:
				$html = '<span class="label success">'.$nameStatus.'</span>';
				break;

			case self::STATUS_DISABLED:
				$html = '<span class="label important">'.$nameStatus.'</span>';
				break;

			default:
				$html = $nameStatus;
				break;
		}

		return $html;
	}

	/**
	 * Возвращает маленькую фотку привязанного товара.
	 * @return string
	 */
	public function getProdPhoto()
	{
		$src = $this->product->cover->getPreviewName(Product::$preview['crop_60']);

		$img = CHtml::image('/'.$src, '', array('width' => 60, 'height' => 60));

		return $img;
	}


	/**
	 * Возвращает случайный товар из загруженных для Юнита.
	 *
	 * @return mixed|null
	 */
	static public function getRandomProduct()
	{
		// Результурующий товар
		$productRes = null;
		$unitProd = null;

		// Атрибуты выборки из Юнита
		$attributes = array('status' => UnitProduct::STATUS_ENABLED);

		// Вычисляем общее кол-во элементов в юните и берем случайный
		$qnt = UnitProduct::model()->countByAttributes($attributes);

		if ($qnt > 0) {
			$unitProd = UnitProduct::model()->findByAttributes($attributes, array(
				'limit' => 1,
				'offset' => rand(0, $qnt - 1)
			));

			if ($unitProd && $unitProd->product)
				$productRes = $unitProd->product;
		}


		return array('product' => $productRes, 'unit' => $unitProd);
	}
}