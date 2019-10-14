<?php

/**
 * This is the model class for table "cat_store_geo".
 *
 * The followings are the available columns in table 'cat_online_store_geo':
 * @property integer $store_id
 * @property integer $type
 * @property integer $geo_id
 */
Yii::import('catalog2.models.Store');
class StoreGeo extends Catalog2ActiveRecord
{
	// константы типов гео-привязок
	const TYPE_CITY = 1;
	const TYPE_REGION = 2;
	const TYPE_COUNTRY = 3;

	/**
	 * @var array лейблы для типов гео-привязок
	 */
	static public $types = array(
		self::TYPE_CITY=>'Город',
		self::TYPE_REGION=>'Регион',
		self::TYPE_COUNTRY=>'Страна',
	);

	// Флаг, при выставлении которого выполнение события afterSave прерывается
	public $afterSaveCommit = false;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OnlineStoreGeo the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function init()
	{
		parent::init();
		$this->onAfterValidate = array($this, 'validateGeo');
		$this->onAfterSave = array($this, '_resetStoreCity');
		$this->onAfterDelete = array($this, '_resetStoreCity');
	}

	/**
	 * Переопределяем метод, чтобы все события по afterSave запускались только вручную.
	 * Это нужно для тех случаев, когда выполняется сохранение пользователя с транзакцией.
	 * @param bool $manualRun
	 */
	public function afterSave($manualRun = false)
	{
		if ($this->afterSaveCommit == false || ($this->afterSaveCommit == true && $manualRun == true))
			parent::afterSave();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cat_store_geo';
	}

	public function _resetStoreCity()
	{
		$storeId = intval($this->store_id);
		$transaction = Yii::app()->dbcatalog2->beginTransaction();
		try {
			$sql = 'DELETE FROM cat_store_city WHERE store_id=:sid';
			Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':sid', $storeId)->execute();
			$sql = 'INSERT IGNORE INTO cat_store_city '
				.'( '
				.'select c.id as city_id, g.store_id  from cat_store_geo as g '
				.'INNER JOIN city as c ON c.id=g.geo_id AND g.`type`=1 '
				.'WHERE g.store_id=:sid '
				.') '
				.'UNION '
				.'( '
				.'select c2.id as city_id, g.store_id from cat_store_geo as g '
				.'INNER JOIN city as c2 ON c2.region_id=g.geo_id AND g.`type`=2 '
				.'WHERE g.store_id=:sid '
				.') '
				.'UNION '
				.'( '
				.'select c3.id as city_id, g.store_id from cat_store_geo as g '
				.'INNER JOIN city as c3 ON c3.country_id=g.geo_id AND g.`type`=3 '
				.'WHERE g.store_id=:sid '
				.')';
			Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':sid', $storeId)->execute();
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollback();
			throw $e;
		}
	}

//	public function _updateStoreCity()
//	{
//		// Поддержание актуальности
//		if ($this->getIsNewRecord()) {
//			if ($this->type == self::TYPE_CITY) {
//				$sql = 'INSERT IGNORE INTO cat_store_city (`city_id`, `store_id` ) VALUES ('.intval($this->geo_id).','.intval($this->store_id).')';
//				Yii::app()->dbcatalog2->createCommand($sql)->execute();
//				return;
//			}
//			if ($this->type == self::TYPE_REGION) {
//				$sql ='SELECT g.store_id, c.id as city_id FROM cat_store_geo as g '
//					.'INNER JOIN city as c ON c.region_id=g.geo_id  WHERE g.type=2 AND c.region_id='.intval($this->geo_id);
//				$data = Yii::app()->dbcatalog2->createCommand($sql)->queryAll();
//
//			} elseif ($this->type == self::TYPE_COUNTRY) {
//				$sql ='SELECT g.store_id, c.id as city_id FROM cat_store_geo as g '
//					.'INNER JOIN city as c ON c.region_id=g.geo_id  WHERE g.type=3 AND c.country_id='.intval($this->geo_id);
//				$data = Yii::app()->dbcatalog2->createCommand($sql)->queryAll();
//			}
//
//			if (!empty($data)) {
//				$sql = 'INSERT IGNORE INTO cat_store_city (`city_id`, `store_id` ) VALUES ';
//				$cnt = 0;
//				$storeId = intval($this->store_id);
//				foreach ($data as $item) {
//					if ($cnt!==0)
//						$sql .= ',';
//					else
//						$cnt++;
//					$sql .= '('.$item['city_id'].','.$storeId.')';
//				}
//				Yii::app()->dbcatalog2->createCommand($sql)->execute();
//			}
//		}
//	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('store_id, type, geo_id', 'required'),
			array('store_id, type, geo_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('store_id, type, geo_id', 'safe', 'on'=>'search'),
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
			'store'=>array(self::BELONGS_TO, 'Store', 'store_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'store_id' => 'Магазин',
			'type' => 'Тип гео-привязки',
			'geo_id' => 'Значение гео-привязки',
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

		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('geo_id',$this->geo_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Валидация на корректность указанной географии
	 * @return null|void
	 */
	public function validateGeo()
	{
		if ( !in_array($this->type, array_keys(self::$types)) )
			return $this->addError('type', 'Некорректный тип географии охвата');

		switch( $this->type ) {
			case self::TYPE_CITY: $class = 'City'; break;
			case self::TYPE_COUNTRY: $class = 'Country'; break;
			case self::TYPE_REGION: $class = 'Region'; break;
			default: return null;
		}

		if ( !$class::model()->exists('id=:id', array(':id'=>$this->geo_id)) )
			return $this->addError('geo_id', 'Выбранный объект не существует');

		if ($this->isNewRecord && self::model()->exists('store_id=:sid and type=:t and geo_id=:gid', array(
			':sid' => $this->store_id,
			':t'   => $this->type,
			':gid' => $this->geo_id,
		)) )
		{
			return $this->addError('geo_id', 'Выбранный объект уже существует');
		}
	}

	/**
	 * Возвращает лейбл для текущего объекта географии охвата
	 * @return null|string
	 */
	public function getLabel()
	{
		switch( $this->type ) {
			case self::TYPE_CITY: $class = 'City'; break;
			case self::TYPE_COUNTRY: $class = 'Country'; break;
			case self::TYPE_REGION: $class = 'Region'; break;
			default: return null;
		}

		$object = $class::model()->findByPk($this->geo_id);

		if ( !$object )
			return null;

		return self::$types[$this->type] . ': ' . $object->name;
	}

	/**
	 * Получаем массив id магазинов в городе
	 * @param $cityId
	 * @return array
	 */
	public static function getStoreList($cityId)
	{
		$sql = 'SELECT store_id FROM cat_store_city WHERE city_id='.intval($cityId);
		return Yii::app()->dbcatalog2->createCommand($sql)->queryColumn();
	}

    public static function GetStoresCity($storeIds, $conn = null)
    {
        if (is_null($conn)) {
            $conn = Yii::app()->db_catalog2;
        }
        if ($storeIds) {
            $sql = 'SELECT city_id FROM cat_store_city WHERE store_id IN ('.$storeIds.') GROUP BY city_id';
            return $conn->createCommand($sql)->queryColumn();
        }
        return array();
    }

	/**
	 * Временное решение, пока нет вывода онлайн магазинов
	 * в дальнейшем заменить на getStoreList
	 * Получаем массив id магазинов в городе
	 * @param $cityId
	 * @return array
	 * @deprecated
	 */
	public static function getOfflineStoreList($cityId)
	{
		$sql = 'SELECT store_id FROM cat_store_city as c '
			.'INNER JOIN cat_store as s ON s.id=c.store_id AND s.type='.Store::TYPE_OFFLINE.' '
			.'WHERE city_id='.intval($cityId);
		return Yii::app()->dbcatalog2->createCommand($sql)->queryColumn();
	}
}