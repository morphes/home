<?php

/**
 * This is the model class for table "mall_service".
 *
 * The followings are the available columns in table 'mall_service':
 * @property integer $id
 * @property string $name
 * @property integer $pos
 * @property integer $create_time
 * @property integer $update_time
 */
class MallService extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MallService the static model class
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
		return 'mall_service';
	}


	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
			'PositionBehavior' => array(
				'class' => 'application.components.PositionBehavior',
				'positionName' => 'pos'
			),
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('pos, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, pos, create_time, update_time', 'safe', 'on'=>'search'),
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
			'id'          => 'ID',
			'name'        => 'Название',
			'pos'         => 'Позиция',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('pos',$this->pos);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('pos' => CSort::SORT_ASC);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort'     => $sort,
			'pagination' => array(
				'pageSize' => 20
			)
		));
	}

	/**
	 * Возвращает максимально допустимую позицию.
	 * По факту это количество записей в таблице услуг.
	 */
	public function getMaxPos()
	{
		return self::count() + 1;
	}
}