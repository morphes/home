<?php

/**
 * @brief This is the model class for table "unit".
 *
 * @details The followings are the available columns in table 'unit':
 * @param integer $id
 * @param string $name
 * @param integer $class_id
 * @param integer $status
 * @param string $data
 */
class Unit extends EActiveRecord
{
        // стандартный объем данный на странице администрирования юнита
        const PAGE_SIZE = 20;
        
        // статусы, используемые в юнитах
        const STATUS_DISABLED = 1;
        const STATUS_ENABLED = 2;
        const STATUS_SMALL = 3;
        const STATUS_LARGE = 4;
        const STATUS_SMALL_LARGE = 5;
        
        public static $statusLabel = array(
            self::STATUS_DISABLED => 'Отключен',
            self::STATUS_ENABLED => 'Включен',
        );

        public static $statusLabelForIdea = array(
            self::STATUS_DISABLED => 'Отключен',
            self::STATUS_ENABLED => 'Включен',
        );
	
	public static $statusLabelForPromo = array(
            self::STATUS_DISABLED => 'Отключен',
            self::STATUS_ENABLED => 'Включен',
        );
        
        public static $statusLabelForDesigner = array(
            self::STATUS_DISABLED => 'Отключен',
            self::STATUS_SMALL => 'Мелко',
            self::STATUS_LARGE => 'Крупно',
            self::STATUS_SMALL_LARGE => 'Крупно и мелко',
        );
        
        
        // возможные типы отображения информации в юнитах
        const OUTPUT_TYPE_RANDOM = 1;
        const OUTPUT_TYPE_POPULARITY = 2;
        
        public static $outputTypeLabel = array(
            self::OUTPUT_TYPE_RANDOM => 'Случайно из списка',
            self::OUTPUT_TYPE_POPULARITY => 'По популярности',
        );
        

        // classes of units
        const CLASS_HOMEPAGE = 1;
        const CLASS_IDEA = 2;
        // etc.
        
        public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}
	
	/**
	 * Update update_time in object
	 */
	public function setDate()
	{
		$this->update_time=time();
	}
	
        /**
         * Возвращает DataProvider с набором юниов для запрошенного класса
         * @param int $class_id
         * @return CActiveDataProvider 
         */
        public static function getUnitsForClass($class_id)
        {
                $criteria = new CDbCriteria();
                $criteria->condition = 'class_id = :class';
                $criteria->select = 'name, class_id, status, alias';
                $criteria->params = array(':class' => $class_id);
                $criteria->limit = 30;

                $dataProvider = new CActiveDataProvider('Unit', array('criteria' => $criteria));

                return $dataProvider;
        }

        /**
         * Смена статус юнита
         * @param string $unit_name
         * @return boolean 
         */
        public static function switchUnitStatus($unit_name, $status1 = self::STATUS_ENABLED, $status2 = self::STATUS_DISABLED)
        {
                $unit = self::model()->findByPk($unit_name);
                if ($unit) {

                        if ($unit->status == $status2) {
                                $unit->status = $status1;
                        } else {
                                $unit->status = $status2;
                        }
                        
                        $unit->save();
                        return true;
                }
                return false;
        }
        

        /**
         * Возвращает объект юнита
         * @param string $unit_name
         * @return object 
         */
        public static function getUnitSettings($unit_name)
        {
                return self::model()->findByPk($unit_name);   
        }
        
        
        /**
         * Сохранение конфигурации юнита
         * @param object $unit - объект юнита для сохранения настроек
         * @param array $data - массив новой конфигурации
         * @return object Unit 
         */
        public static function setUnitData($unit, $data)
        {
                if(is_object($unit) && is_array($data) && isset($unit->data)){
                        $unit->data = serialize($data);
                        $unit->save();
                }
                return $unit;
        }
        

        /**
         * Returns the static model of the specified AR class.
         * @return Unit the static model class
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
                return 'unit';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('name, alias, class_id', 'required'),
                    array('class_id, status, update_time', 'numerical', 'integerOnly' => true),
                    array('name, alias', 'length', 'max' => 255),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
                    'alias' => 'Название',
                    'status' => 'Состояние',
                );
        }
	
	/**
	 * Convert array to stdClass
	 * @param array $data 
	 * @return stdClass
	 */
	public static function convertArray(array $data)
	{
		$object = new stdClass();
		foreach ($data as $key => $value) {
			$object->$key = $value;
		}
		return $object;
	}

}