<?php

/**
 * This is the model class for table "product_on_photos".
 *
 * The followings are the available columns in table 'product_on_photos':
 * @property integer $ufile_id
 * @property integer $product_id
 * @property string $model
 * @property integer $model_id
 * @property integer $type
 * @property string $params
 * @property integer $create_time
 * @property integer $update_time
 */
class ProductOnPhotos extends Catalog2ActiveRecord
{
	const TYPE_SIMILAR = 0;
	const TYPE_PRODUCT = 1;

	public static $typeNames = array(
		self::TYPE_SIMILAR => 'Похожий товар',
		self::TYPE_PRODUCT => 'Товар',
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProductOnPhotos the static model class
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
		return 'cat_product_on_photos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ufile_id, product_id, model_id, create_time, update_time, type', 'numerical', 'integerOnly'=>true),
			array('params', 'safe'),
			array('model', 'length', 'max' => '40'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ufile_id, product_id, params, create_time, update_time, type', 'safe', 'on'=>'search'),
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
			'ufile_id'    => 'ID фотографии',
			'product_id'  => 'ID товара',
			'model'       => 'Модель',
			'model_id'       => 'ID модели',
			'type'	=> 'Тип товара',
			'params'      => 'Params',
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

		$criteria->compare('ufile_id',$this->ufile_id);
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('model',$this->model);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('params',$this->params,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Возвращает точные координаты точки в пикселя, относительно левого верхнего угла
	 *
	 * @param $iWidth Ширина в пикселях того изображения, на котором будем выводить иконку
	 * @param $iHeight Высота в пикселя того изображения, на котором будем выводить иконку
	 * @return array()
	 */
	public function getOffset($iWidth, $iHeight)
	{
		$data = unserialize($this->params);

		// Значения в базе хранятся в процентах.
		$perTop = $data['top'];
		$perLeft = $data['left'];

		// Переводим проценты в пиксели
		$top = round($iHeight * $perTop / 100);
		$left = round($iWidth * $perLeft / 100);

		return array('top' => $top, 'left' => $left);
	}

	/**
	 * Возвращает количество товаров связанных с фотографией
	 *
	 * @param $img
	 * @return string
	 */
	static public function getQntProducts($imgId)
	{
		Yii::import('application.modules.catalog2.models.ProductOnPhotos');

		$qnt = ProductOnPhotos::model()->countByAttributes(array(
			'ufile_id' => $imgId
		));

		return 'Связано товаров: <strong>'.intval($qnt).'</strong>';
	}
}