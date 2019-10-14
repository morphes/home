<?php

/**
 * This is the model class for table "index_idea_link".
 *
 * На главной странице в блоке "Идеи и архитектура" набор ссылок над
 * изображениями идей.
 *
 * The followings are the available columns in table 'index_idea_link':
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property integer $position
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexIdeaLink extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IndexIdeaLink the static model class
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
		return 'index_idea_link';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('position, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name, url', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, url, position, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
			'PositionBehavior' => array(
				'class' => 'application.components.PositionBehavior'
			)
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
			'url'         => 'Url',
			'position'    => 'Позиция',
			'create_time' => 'Дата созадания',
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
		$criteria->compare('url',$this->url,true);
		$criteria->compare('position',$this->position);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('position' => CSort::SORT_ASC);

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
	 * Счетчик увеличивается на единицу для случая, когда
	 * мы создаем новую запись.
	 */
	public function getMaxPos()
	{
		return (int)self::count() + 1;
	}

	static public function getLinks($limit = 6)
	{
		$limit = (int)$limit;
		$sql = "SELECT id, name, url FROM index_idea_link ORDER BY position ASC LIMIT :limit";
		$res = Yii::app()->db
			->createCommand($sql)
			->bindValue(':limit', $limit)
			->queryAll();

		return $res;
	}
}