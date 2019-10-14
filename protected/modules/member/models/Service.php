<?php

/**
 * This is the model class for table "service".
 *
 * The followings are the available columns in table 'service':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $type
 * @property string $name
 * @property string $desc
 * @property string $url
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property User[] $users
 */
class Service extends EActiveRecord
{
        /**
         * @var null если объект получен из CSphinxDataProvider (индекс service_synonym) в результате группировки с сохранением ключа синонима,
         * то в данном атрибуте хранится id синонима, по которому найдена текущая услуга
         */
        public $synonym_id = null;

        /**
         * @var int если объект получен из CSphinxDataProvider (индекс service_synonym) и найден по названию услуги, а не по синониму, то в данном
         * атрибуте будет установлен флаг со значением "1"
         */
        public $founded_by_name = 0;

	// Флаг популярности для услуги
	const POPULAR_YES = 1;
	const POPULAR_NO = 0;

	const CATEGORY_TYPE_1 = 1;

	const CATEGORY_TYPE_2 = 2;

	const CATEGORY_TYPE_3 = 3;

	const DURATION_SERVICE = 600;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Service the static model class
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
		return 'service';
        }

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onAfterSave = array($this, 'setSynonym');
                $this->onBeforeValidate = array($this, 'trimUrl');
        }

        /**
         * Update create_time and update_time in object
         */
        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = $this->update_time = time();
                else
                        $this->update_time = time();
        }

        /**
         * Добавление/обновление названия услуги в таблице синонимов
         */
        public function setSynonym()
        {
                if($this->isNewRecord)
                        Yii::app()->db->createCommand()->insert('service_synonym', array(
                                'service_id'=>$this->id,
                                'synonym'=>$this->name,
                                'is_servicename'=>1,
                        ));
                else
                        Yii::app()->db->createCommand()->update('service_synonym', array(
                                'service_id'=>$this->id,
                                'synonym'=>$this->name,
                                'is_servicename'=>1,
                        ), 'service_id=:sid and is_servicename=1', array(':sid'=>$this->id));

        }

        public function rules()
        {
                return array(
                        array('parent_id, type, name', 'required'),
                        array('parent_id, position, type, popular, create_time, update_time', 'numerical', 'integerOnly' => true),
                        array('name', 'length', 'max' => 255),
			array('url', 'unique'),
			array('url', 'length', 'max' => 50),
                        array('desc', 'length', 'max' => 2000),
                        array('seo_top_desc, seo_bottom_desc', 'length', 'max' => 3000),
                        array('parent_id, desc, create_time, update_time', 'default', 'setOnEmpty' => true, 'value' => null),
                        array('id, parent_id, type, name, desc, create_time, update_time', 'safe', 'on' => 'search'),
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
			'users' => array(self::MANY_MANY, 'User', 'user_service(service_id, user_id)'),
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
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('popular',$this->popular);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('seo_top_desc',$this->desc,true);
		$criteria->compare('seo_bottom_desc',$this->desc,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
			'id'              => 'ID',
			'parent_id'       => 'Родитель',
			'type'            => 'Тип', // Тип проекта (см Config::$projectTypes)
			'popular'         => 'Популярный',
			'url'             => 'ЧПУ',
			'name'            => 'Название',
			'desc'            => 'Описание',
			'seo_top_desc'    => 'SEO описание (верх)',
			'seo_bottom_desc' => 'SEO описание (низ)',
			'create_time'     => 'Создана',
			'update_time'     => 'Обновлена',
                );
        }

        /**
         * Возвращает список родительских услуг
         * @return array
         */
        static public function getParentList()
        {
                $services = self::model()->findAll(array(
                        'condition' => 'parent_id=0',
                        'limit' => 200,
                ));
                return CHtml::listData($services, 'id', 'name');
        }

        /**
         * Возвращает название услуги по ее id
         * @static
         */
        static public function getServiceName($service_id)
        {
                $service = self::model()->findByPk((int)$service_id);

                if($service)
                        return $service->name;
                else
                        return '';
        }
	
	/**
	 *Список основных разделов услуг 
	 */
	public static function getMainItems()
	{
		return self::model()->findAllByAttributes(array('parent_id'=>0));
	}
	
	/**
	 * Список дочерних услуг
	 * @param integer $parentId 
	 */
	public static function getChildrens($parentId)
	{
		if ($parentId == 0)
			return array();
		return self::model()->findAllByAttributes(array('parent_id'=>$parentId));
	}

        /**
         * @return array синонимы услуги
         */
        public function getSynonyms()
        {
                return Yii::app()->db->createCommand()->from('service_synonym')->where('service_id=:sid and is_servicename=0', array(':sid'=>$this->id))->queryAll();
        }

	public function trimUrl()
	{
		$this->url = trim($this->url, '/');
	}
}