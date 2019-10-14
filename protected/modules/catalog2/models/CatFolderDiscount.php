<?php

/**
 * This is the model class for table "cat_folder_discount".
 *
 * The followings are the available columns in table 'cat_folder_discount':
 * @property integer $model_id
 * @property integer $store_id
 * @property double $discount
 * @property integer $date_start
 * @property integer $date_end
 * @property integer $status
 */
class CatFolderDiscount extends Catalog2ActiveRecord
{
	//Задача на включение выключение скидки активна
	const STATUS_ACTIVE =1;

	//Задача на включение выключения скидки не активна
	const STATUS_DEACTIVATE =2;


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CatFolderDiscount the static model class
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
		return 'cat_folder_discount_task';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model_id, store_id, date_start, date_end, status', 'numerical', 'integerOnly'=>true),
			array('discount', 'numerical'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('model_id, store_id, discount, date_start, date_end, status', 'safe', 'on'=>'search'),
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
			'model_id' => 'Model',
			'store_id' => 'Store',
			'discount' => 'Discount',
			'date_start' => 'Date Start',
			'date_end' => 'Date End',
			'status' => 'Status',
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

		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('discount',$this->discount);
		$criteria->compare('date_start',$this->date_start);
		$criteria->compare('date_end',$this->date_end);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}