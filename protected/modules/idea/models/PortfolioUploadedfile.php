<?php

/**
 * This is the model class for table "portfolio_uploadedfile".
 *
 * The followings are the available columns in table 'portfolio_uploadedfile':
 * @property integer $item_id
 * @property integer $file_id
 */
class PortfolioUploadedfile extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PortfolioUploadedfile the static model class
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
		return 'portfolio_uploadedfile';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('item_id, file_id', 'required'),
			array('item_id, file_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('item_id, file_id', 'safe', 'on'=>'search'),
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
                        'file' => array(self::BELONGS_TO, 'UploadedFile', 'file_id'),
                        'portfolio' => array(self::BELONGS_TO, 'Portfolio', 'item_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'item_id' => 'Item',
			'file_id' => 'File',
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

		$criteria->compare('item_id',$this->item_id);
		$criteria->compare('file_id',$this->file_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}