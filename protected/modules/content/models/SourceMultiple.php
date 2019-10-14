<?php

/**
 * This is the model class for table "source_multiple".
 *
 * The followings are the available columns in table 'source_multiple':
 * @property integer $source_id
 * @property string $model
 * @property integer $model_id
 * @property integer $create_time
 */
class SourceMultiple extends EActiveRecord
{
	public function init()
	{
		$this->onBeforeSave = array($this, 'setDate');
	}
	
	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = time();
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return SourceMultiple the static model class
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
		return 'source_multiple';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model, model_id', 'required'),
			array('model_id', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>100),
			array('source_name, source_url', 'length', 'max'=>255),
			array('source_url', 'url'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, model, model_id, source_name, source_url, create_time', 'safe', 'on'=>'search'),
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
			'source_name' => 'Имя источника',
			'source_url' => 'URL источника',
			'model' => 'Имя модели',
			'model_id' => 'ID из модели',
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

		$criteria->compare('source_id',$this->source_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}