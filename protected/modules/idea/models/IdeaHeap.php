<?php

/**
 * This is the model class for table "idea_heap".
 *
 * The followings are the available columns in table 'idea_heap':
 * @property integer $id
 * @property integer $idea_type_id
 * @property string $option_key
 * @property string $option_value
 * @property string $parent_id
 * @property string $desc
 *
 * The followings are the available model relations:
 * @property IdeaType $ideaType
 */
class IdeaHeap extends EActiveRecord
{
	// Количество соответвующих элементов для объектов
	public $style_cnt;
	public $room_cnt;
	public $color_cnt;
	public $building_type_cnt;
	public $floor_cnt;
	public $material_cnt;
	public $supp_room_cnt;

	const DURATION = 86400;
	
	public function init()
	{
		parent::init();
		$this->attachEventHandler('onBeforeSave', array($this, 'clearCache'));
	}
	
	/**
	 *  Clear cache from retrieval. Do not call directly
	 */
	public function clearCache()
	{
		Yii::app()->cache->delete(self::getRetrievalKey($this->attributes));
	}
	
	/**
	 * Generate key for retrieval.
	 * @param array $attributes
	 * @return string
	 */
	protected static function getRetrievalKey($attributes)
	{
		$parentId = empty($attributes['parent_id']) ? '' : $attributes['parent_id'];
		return 'IdeaHeap.'.$attributes['option_key'].'.'.$attributes['idea_type_id'].'.'.$parentId;
	}


	/**
	 * Returns the static model of the specified AR class.
	 * @return IdeaHeap the static model class
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
		return 'idea_heap';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('idea_type_id, option_key, option_value', 'required'),
			array('idea_type_id', 'numerical', 'integerOnly'=>true),
			array('option_key, parent_id', 'length', 'max'=>45),
			array('option_value, param', 'length', 'max'=>255),
			array('desc', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, idea_type_id, option_key, option_value, parent_id, desc, param', 'safe', 'on'=>'search'),
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
			'ideaType' => array(self::BELONGS_TO, 'IdeaType', 'idea_type_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'		=> 'ID',
			'idea_type_id'	=> 'Idea Type',
			'option_key'	=> 'Ключ',
			'option_value'	=> 'Название',
			'parent_id'	=> 'Parent',
			'desc'		=> 'Синонимы',
			'building_type_cnt' => 'Тип объекта',
			'style_cnt'	=> 'Стиль',
			'room_cnt'	=> 'Помещение',
			'color_cnt'	=> 'Цвет',
			'param'		=> 'Доп. параметр',
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
		$criteria->compare('idea_type_id',$this->idea_type_id);
		$criteria->compare('option_key',$this->option_key,true);
		$criteria->compare('option_value',$this->option_value,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('parent_id',$this->parent_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Выдает список детей того свойства, чей идентификатор указан в $parent_id.
	 * Если $parent_id == 0, то выдает список главных (независимых) свойств.
	 * @param type $id 
	 */
	public function getChildrenByParentId($parent_id, $idea_type_id = 0)
	{
		
		$parent_id = (int)$parent_id;
		$idea_type_id = (int)$idea_type_id;
		$criteria = new CDbCriteria;
		$criteria->condition = 'parent_id = '.$parent_id;
		if ($idea_type_id > 0)
			$criteria->condition .= ' and idea_type_id = '.$idea_type_id;
		$criteria->order = 'option_key ASC';
		
		$criteria->params = array();
		$heap = self::model()->findAll($criteria);
		
		return $heap;
	}
	
	/**
	 * Get rooms for selected ideaType and parentId.
	 * @param integer $ideaType
	 * @param integer $parentId
	 * @return array 
	 */
	public static function getRooms($ideaType, $parentId = null, $useCache = true)
	{
		$attributes = array('option_key' => 'room', 'idea_type_id' => $ideaType);
		if(!is_null($parentId))
			$attributes['parent_id'] = $parentId;
		
		$key = self::getRetrievalKey($attributes);

                if($useCache)
		        $data = Yii::app()->cache->get($key);
                else
                        $data = null;

		if (!$data) {
			$data = self::model()->findAllByAttributes($attributes, array('order' => 'position ASC', 'index'=>'id'));

                        if($useCache)
                                Yii::app()->cache->set($key, $data, self::DURATION);

		}
		return $data;
	}
	
	/**
	 * Get colors for selected ideaType and parentId.
	 * @param integer $ideaType
	 * @param integer $parentId
	 * @return array 
	 */
	public static function getColors($ideaType, $parentId = null)
	{
		$attributes = array('option_key' => 'color', 'idea_type_id' => $ideaType);
		if(!is_null($parentId))
			$attributes['parent_id'] = $parentId;
		
		$key = self::getRetrievalKey($attributes);
		$data = Yii::app()->cache->get($key);
		if (!$data) {
			$data = self::model()->findAllByAttributes($attributes, array('order' => 'position ASC', 'index'=>'id'));
			Yii::app()->cache->set($key, $data, self::DURATION);
		}
		return $data;
	}
	
	/**
	 * Get styles for selected ideaType and parentId.
	 * @param integer $ideaType
	 * @param integer $parentId
	 * @return array 
	 */
	public static function getStyles($ideaType, $parentId = null)
	{
		$attributes = array('option_key' => 'style', 'idea_type_id' => $ideaType);
		if(!is_null($parentId))
			$attributes['parent_id'] = $parentId;
		
		$key = self::getRetrievalKey($attributes);
		$data = Yii::app()->cache->get($key);
		if (!$data) {
			$data = self::model()->findAllByAttributes($attributes, array('order' => 'position ASC', 'index'=>'id'));
			Yii::app()->cache->set($key, $data, self::DURATION);
		}
		return $data;
	}
	
	/**
	 * Get objects for selected ideaType
	 * @param integer $ideaType
	 * @return array 
	 */
	public static function getObjects($ideaType)
	{
		$attributes = array('option_key' => 'object', 'idea_type_id' => $ideaType, 'parent_id' => 0);
		$key = self::getRetrievalKey($attributes);
		$data = Yii::app()->cache->get($key);
		if (!$data) {
			$data = self::model()->findAllByAttributes($attributes, array('order' => 'position ASC', 'index'=>'id'));
			Yii::app()->cache->set($key, $data, self::DURATION);
		}
		return $data;
	}


	/**
	 * Метод написан специально, чтобы заменить все методы типа getStyles, getColors и т.д.
	 * Возвращает массив объектов IdeaHeap
	 *
	 * @static
	 * @param $ideaTypeId Идентификатор типа идеи (Интерьеры, Архитекутра и т.д.)
	 * @param $parentId Родительская запись, которой принадлежит указанное свойство $optionKey
	 * @param $optionKey Ключ значения которого нужно вернуть
	 * @param bool $useCache Флаг использованеия кеширования результата. По-умлочанию включен.
	 * @return array|mixed
	 */
	public static function getListByOptionKey($ideaTypeId, $parentId, $optionKey, $useCache = true)
	{
		$attributes = array('option_key' => $optionKey, 'idea_type_id' => $ideaTypeId);
		if(!is_null($parentId))
			$attributes['parent_id'] = $parentId;

		$key = self::getRetrievalKey($attributes);

		if ($useCache)
		{
			$data = Yii::app()->cache->get($key);
			if ( ! $data) {
				$data = self::model()->findAllByAttributes($attributes, array('order' => 'position ASC', 'index'=>'id'));
				Yii::app()->cache->set($key, $data, self::DURATION);
			}
		}
		else
		{
			$data = self::model()->findAllByAttributes($attributes, array('order' => 'position ASC', 'index'=>'id'));
		}
		return $data;
	}


	/**
	 * По названию тип строения возвращается идентификатор (константу)
	 * для типа построек, на которую потом опираемся в логике когда.
	 * @param $name Строка с названием типа объекта
	 * @param $ideaTypeId Идентификатор типа идеи
	 */
	static public function getBuildTypeByName($name, $ideaTypeId)
	{
		if ( ! is_string($name))
			throw new CHttpException(500, 'Неверно указано имя типа постройки');

		if ($ideaTypeId == Config::ARCHITECTURE)
		{
			switch($name) {
				case 'Дом, коттедж, особняк':
					$value = Architecture::BUILD_TYPE_HOUSE;
					break;
				case 'Хозяйственные постройки':
					$value = Architecture::BUILD_TYPE_OUTBUILDING;
					break;
				case 'Общественные здания':
					$value = Architecture::BUILD_TYPE_PUBLIC;
					break;
				default:
					throw new CHttpException(500, 'Неверно указан тип постройки');
			}
		}
		elseif ($ideaTypeId == Config::INTERIOR)
		{
			switch($name) {
				case 'Жилой':
					$value = Interior::BUILD_TYPE_LIVE;
					break;
				case 'Общественный':
					$value = Interior::BUILD_TYPE_PUBLIC;
					break;
				default:
					throw new CHttpException(500, 'Неверно указан тип интерьера');
			}
		}
		else
		{
			throw new CHttpException(500, 'Неверно указан тип идеи');
		}

		return $value;
	}
}