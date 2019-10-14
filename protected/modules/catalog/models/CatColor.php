<?php

/**
 * This is the model class for table "cat_color".
 *
 * The followings are the available columns in table 'cat_color':
 * @property integer $id
 * @property string $name
 * @property string $desc
 * @property string $param
 * @property string $tags
 * @property integer $create_time
 * @property integer $update_time
 */
class CatColor extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Color the static model class
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
		return 'cat_color';
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
			array('name, param', 'required'),
			array('create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('desc, tags', 'length', 'max'=>3000),
			array('param', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, desc, param, tags, create_time, update_time', 'safe', 'on'=>'search'),
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
			'name' => 'Название',
			'desc' => 'Описание',
			'param' => 'Параметры',
                        'tags' => 'Метки',
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
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('param',$this->param,true);
                $criteria->compare('tags',$this->tags,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Возвращает массив всех цветов
	 * @param $idAssoc возвращает массив формата id => name
	 * @return array
	 */
	static public function getAll($idAssoc=false)
	{
		$colors = Yii::app()->db->createCommand()
			->from(self::model()->tableName())
			->queryAll();

		if (!$idAssoc)
			return $colors;

		$idAssocArray = array();
		foreach($colors as $color)
			$idAssocArray[$color['id']] = $color['name'];

		return $idAssocArray;
	}
}