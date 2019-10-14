<?php

/**
 * This is the model class for table "copyright_file".
 *
 * The followings are the available columns in table 'copyright_file':
 * @property integer $id
 * @property integer $number
 * @property string $name
 * @property integer $author_id
 * @property integer $interior_id
 * @property integer $update_time
 * @property integer $create_time
 *
 * The followings are the available model relations:
 * @property Interior $interior
 * @property User $author
 */
class Copyrightfile extends EActiveRecord
{
	
	public function beforeSave()
	{
		if (parent::beforeSave())
		{
                        if ($this->isNewRecord)
                                $this->create_time = $this->update_time = time();
                        else
                                $this->update_time = time();

                        return true;
                }
                else
                        return false;
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Copyrightfile the static model class
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
		return 'copyright_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('number, author_id, interior_id, update_time, create_time', 'numerical', 'integerOnly'=>true),
			array('name, path', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, number, name, path, author_id, interior_id, update_time, create_time', 'safe', 'on'=>'search'),
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
			'interior' => array(self::BELONGS_TO, 'Interior', 'interior_id'),
			'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'number' => 'Номер',
			'name' => 'Название',
			'author_id' => 'Автор',
			'path' => 'Путь до файла PDF-свидетельства',
			'interior_id' => 'ID проекта',
			'update_time' => 'Дата обновления',
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
		$criteria->compare('number',$this->number);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('path',$this->path,true);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('interior_id',$this->interior_id);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Возвращает $limit последних записей указанного в $intid интерьера
	 * @param integer $intid ID интерьера
	 * @param interger $limit Кол-во последний PDF-сертификатов
	 * @return array Массив сертификатов 
	 */
	public function getHistory($intid = NULL, $limit = NULL)
	{
		$arrResult = array();
		$limit = (is_null($limit)) ? 3 : intval($limit);
	
		if ( ! Yii::app()->user->isGuest) {
		
			$models = $this->findAllByAttributes(array(
				'interior_id' => (int)$intid,
				'author_id' => Yii::app()->user->model->id
			), new CDbCriteria(array(
				'order' => 'create_time DESC',
				'limit' => $limit
			)));

			$arrResult = $models;
		}
		
		return $arrResult;
	}
}