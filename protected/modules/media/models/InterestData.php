<?php

/**
 * This is the model class for table "interest_data".
 *
 * The followings are the available columns in table 'interest_data':
 * @property integer $id
 * @property integer $model_id
 * @property string $model
 * @property integer $status
 * @property integer $update_time
 * @property integer $create_time
 */
class InterestData extends CActiveRecord
{
	//Товар
	const MODEL_PRODUCT  = 1;

	//Знание
	const MODEL_KNOWLEDGE = 2;

	//Интерьер
	const MODEL_INTERIOR = 3;

	const PAGE_LIMIT = 18;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return InterestData the static model class
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
		return 'interest_data';
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model_id, status, update_time, create_time', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, model_id, model, status, update_time, create_time', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'model_id' => 'Model',
			'model' => 'Model',
			'status' => 'Status',
			'update_time' => 'Update Time',
			'create_time' => 'Create Time',
		);
	}


	/**
	 * Метод возвращает html код
	 * итема в зависимости от его типа
	 * В качестве второго параметра может
	 * быть указано, должен ли итем быть большим
	 * @param      $model
	 * @param bool $bigItem
	 *
	 * @return mixed
	 */
	public static function getItemHtml($model, $bigItem = false)
	{
		Yii::import('application.modules.idea.models.*');
		Yii::import('application.modules.catalog.models.*');
		Yii::import('application.modules.catalog.models.*');
		$html = null;
		$modelType = $model->model;
		switch ($modelType) {
			case self::MODEL_KNOWLEDGE :
				$knowledgeModel = MediaKnowledge::model()->findByPk($model->model_id);
				if ($knowledgeModel) {
					if ($bigItem) {
						$html = Yii::app()->controller->renderPartial('//media/interest/_knowledgeBigItem', array('model' => $knowledgeModel), true);
					} else {
						$html = Yii::app()->controller->renderPartial('//media/interest/_knowledgeItem', array('model' => $knowledgeModel), true);
					}
				}
				break;
			case self::MODEL_INTERIOR :
				$modelInterior = Interior::model()->findByPk($model->model_id);
				if ($modelInterior) {
					if ($bigItem) {
						$html = Yii::app()->controller->renderPartial('//media/interest/_interiorBigItem', array('model' => $modelInterior), true);
					} else {
						$html = Yii::app()->controller->renderPartial('//media/interest/_interiorItem', array('model' => $modelInterior), true);
					}
				}
				break;

			case self::MODEL_PRODUCT :
				$productModel = Product::model()->findByPk($model->model_id);
				if ($productModel) {
					$html = Yii::app()->controller->renderPartial('//media/interest/_productItem', array('product' => $productModel), true);
				}
				break;
		}

		return $html;
	}

	public static function getBigItemHtml($model)
	{
		Yii::import('application.modules.idea.models.*');

		$modelType = $model->model;

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
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}