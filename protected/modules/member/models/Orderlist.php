<?php

/**
 * This is the model class for table "orderlist".
 *
 * The followings are the available columns in table 'orderlist':
 * @property integer $id
 * @property integer $order_id
 * @property integer $product_id
 * @property integer $count
 * @property integer $shop_id
 * @property double $bonus
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Order $order
 * @property Product $product
 * @property User $shop
 */
class Orderlist extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Orderlist the static model class
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
		return 'orderlist';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('order_id, product_id, count, shop_id, create_time, update_time', 'required'),
			array('order_id, product_id, count, shop_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('bonus', 'numerical'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, order_id, product_id, count, shop_id, bonus, create_time, update_time', 'safe', 'on'=>'search'),
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
			'order' => array(self::BELONGS_TO, 'Order', 'order_id'),
			'product' => array(self::BELONGS_TO, 'Product', 'product_id'),
			'shop' => array(self::BELONGS_TO, 'User', 'shop_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'order_id' => 'Order',
			'product_id' => 'Product',
			'count' => 'Count',
			'shop_id' => 'Shop',
			'bonus' => 'Bonus',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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
		$criteria->compare('order_id',$this->order_id);
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('count',$this->count);
		$criteria->compare('shop_id',$this->shop_id);
		$criteria->compare('bonus',$this->bonus);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}