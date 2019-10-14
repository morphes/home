<?php

/**
 * This is the model class for table "media_theme_select".
 *
 * The followings are the available columns in table 'media_theme_select':
 * @property string $model
 * @property integer $model_id
 * @property integer $theme_id
 */
class MediaThemeSelect extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaThemeSelect the static model class
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
		return 'media_theme_select';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model_id, theme_id', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('model, model_id, theme_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'model' => 'Model',
			'model_id' => 'Model',
			'theme_id' => 'Theme',
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

		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('theme_id',$this->theme_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}