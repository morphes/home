<?php

/**
 * This is the model class for table "cat_option_heap".
 *
 * The followings are the available columns in table 'cat_option_heap':
 * @property integer $id
 * @property integer $category_id
 * @property integer $parent_id
 * @property integer $type_id
 * @property integer $grup_id
 * @property integer $position
 * @property integer $filterable
 * @property integer $forminimized
 * @property integer $minicard
 * @property integer $required
 * @property string $key
 * @property string $name
 * @property string $param
 * @property string $desc
 */

/**
 * value row has next values for some types of options:
 * option_type = input  - not available values
 * option_type = checkbox  - not available values
 * option_type = textarea  - not available values
 * option_type = select  - available values stored in cat_value, where product_id is null and option_id - id of current option
 * option_type = select multiple  - available values stored in cat_value with parent_id, where product_id is null and option_id - id of current option
 * option_type = color  - in value column saved color_id for table cat_color
 * option_type = style  - in value column saved style_id for table cat_style
 * option_type = image - value column not initialized (null). data stored in cat_value_file (value_id, image_id)
 */
class Option extends Catalog2ActiveRecord
{
        const TYPE_INPUT = 1;
        const TYPE_CHECKBOX = 2;
        const TYPE_SELECT = 3;
        const TYPE_IMAGE = 4; // Input file
        const TYPE_TEXTAREA = 5;
        const TYPE_SELECTMULTIPLE = 6; // Select multiple
        const TYPE_STYLE = 7;
        const TYPE_COLOR = 8;
        const TYPE_SIZE = 9; // input

        const MAX_FILTERABLE_OPTION_QT = 12; // максимальное кол-во опций типа "Габарит", по которым можно фильтровать

        private $_availableValues;

        /**
         * Наименования типов опций товаров и категорий
         * @var array
         */
        public static $types = array(
                self::TYPE_INPUT => 'Поле ввода',
                self::TYPE_CHECKBOX => 'Флаг',
                self::TYPE_SELECT => 'Выпадающий список',
                self::TYPE_SELECTMULTIPLE => 'Группа значений',
                self::TYPE_TEXTAREA => 'Текстовая область',
                self::TYPE_IMAGE => 'Изображение',
                self::TYPE_STYLE => 'Стиль',
                self::TYPE_COLOR => 'Цвет',
                self::TYPE_SIZE => 'Габарит',
        );

        /**
         * Параметры типов опций
         * @var array
         */
        public static $typeParams = array(

                self::TYPE_INPUT => array(
                        'valueList'=>false, // Нет списка доступных значений
                        'valueType'=>Value::TYPE_STRING, // Хранимое значение опции - строка
                        'multiValue'=>false, // Несколько значений для одной опции недопустимо
                ),
                self::TYPE_CHECKBOX => array(
                        'valueList'=>false,
                        'valueType'=>Value::TYPE_STRING,
                        'multiValue'=>false,
                ),
                self::TYPE_SELECT => array(
                        'valueList'=>true,
                        'valueType'=>Value::TYPE_STRING,
                        'multiValue'=>false,
                ),
                self::TYPE_SELECTMULTIPLE => array(
                        'valueList'=>true, // Есть список доступных значений
                        'valueType'=>Value::TYPE_STRING, // Тип значения - строка
                        'multiValue'=>true, // Можно выбрать несколько значений
                ),
                self::TYPE_TEXTAREA => array(
                        'valueList'=>false,
                        'valueType'=>Value::TYPE_STRING,
                        'multiValue'=>false,
                ),
                self::TYPE_IMAGE => array(
                        'valueList'=>false, // Нет списка доступных значений
                        'valueType'=>Value::TYPE_IMAGE, // Тип значения - изображение (UploadedFile ID)
                        'multiValue'=>true, // Можно выбрать несколько файлов (значений)
                ),
                self::TYPE_STYLE => array(
                        'valueList'=>false,
                        'valueType'=>Value::TYPE_STRING,
                        'multiValue'=>false,
                ),
                self::TYPE_COLOR => array(
                        'valueList'=>false,
                        'valueType'=>Value::TYPE_STRING,
                        'multiValue'=>true,
                ),
                self::TYPE_SIZE => array(
                        'valueList'=>false,
                        'valueType'=>Value::TYPE_STRING,
                        'multiValue'=>false,
                        'paramsEditable'=>true,
                ),
        );

        /**
         * @var array Единицы измерения для опции SIZE
         */
        static public $units = array(
                1=>'мм',
                2=>'см',
                3=>'м',
                4=>'л',
                5=>'м2',
                6=>'м3',
                7=>'кг',
                8=>'кг/м2',
                9=>'Вт',
                10=>'л/м2',
                11=>'ч',
                12=>'г/м2',
                13=>'кВт',
		14=>'В',
		15=>'А',
		16=>'куб.м',
		17=>'куб.м/ч',
		18=>'л/мин',
		19=>'л/сутки',
		20=>'дБ',
		21=>'С',
		22=>'Н*м',
		23=>'Дж',
        );

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Option the static model class
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
		return 'cat_option';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
                        array('category_id, type_id', 'required', 'on'=>'init'),
                        array('category_id, parent_id', 'required', 'on'=>'useParent'),
                        array('category_id, key, type_id, name, group_id', 'required', 'on'=>'update'),
                        array('type_id', 'optionValidator', 'on'=>'update'),
			array('category_id, parent_id, type_id, group_id, position, filterable, forminimized, minicard, required, miniform, hide', 'numerical', 'integerOnly'=>true),
			array('key, name, param', 'length', 'max'=>255),
			array('desc', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, category_id, parent_id, type_id, group_id, position, filterable, forminimized, minicard, miniform, hide, required, key, name, param, desc', 'safe', 'on'=>'search'),
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
                        'values'=>array(self::HAS_MANY, 'Value', 'option_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category_id' => 'Категория',
			'parent_id' => 'Родитель',
			'type_id' => 'Тип',
                        'group_id' => 'Группа',
			'position' => 'Позиция',
			'filterable' => 'В развернутом',
			'forminimized' => 'В свернутом',
                        'minicard'=>'На миникарте',
                        'required'=>'Обязательное',
			'key' => 'Ключ',
                        'name' => 'Наименование',
			'param' => 'Параметры',
                        'hide' => 'Скрыть из формы добавления',
			'desc' => 'Описание',
                        'miniform' => 'В краткой форме ЛК',
		);
	}

        public function __set($name, $value)
        {
                $setter = 'set' . $name;
                if (method_exists($this, $setter))
                        return $this->$setter($value);
                else
                        return parent::__set($name, $value);
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
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('type_id',$this->type_id);
                $criteria->compare('group_id',$this->group_id);
		$criteria->compare('position',$this->position);
		$criteria->compare('filterable',$this->filterable);
		$criteria->compare('forminimized',$this->forminimized);
                $criteria->compare('minicard',$this->forminimized);
                $criteria->compare('required',$this->forminimized);
		$criteria->compare('key',$this->key,true);
                $criteria->compare('name',$this->name,true);
		$criteria->compare('param',$this->param,true);
		$criteria->compare('desc',$this->desc,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

        public function init()
        {
                parent::init();
                $this->onBeforeDelete = array($this, 'deleteChild');
        }

        /**
         * Удаление опций, которые используют в качестве родителя текущую опцию
         */
        public function deleteChild()
        {
                self::model()->deleteAllByAttributes(array('parent_id'=>$this->id));
        }

        /**
         * Если текущая опция - дочерняя, то возвращаются родительские данные для некоторых полей
         * @param string $name
         * @return mixed
         */
        public function __get($name)
        {
                /**
                 * Проверка наличия родителя опции
                 */
                $parent_id = parent::__get('parent_id');


                if(!$parent_id)
                {
                        /**
                         * Если родителя нет, то возвращаем значения текущей опции
                         */
                        return parent::__get($name);
                }
                else
                {
                        /**
                         * Если родитель есть, то возвращаем все родительские значения, кроме текущего id и category_id
                         */
                        if(in_array($name, array('id', 'category', 'parent_id')))
                                return parent::__get($name);


                        $parent = self::model()->findByPk($parent_id);
                        if($parent)
                                return $parent->$name;
                }
        }

        /**
         * Проверка использования опции в товарах
         */
        public function checkUsage()
        {
                return Value::model()->exists('option_id=:oid AND product_id is not null', array(':oid'=>$this->id));
        }

        /**
         * Удаляет все значения текущей опции (включая товарные)
         */
        public function deleteValues()
        {
                return Value::model()->deleteAll('option_id=:oid', array(':oid'=>$this->id));
        }

        /**
         * Вставка пустых значений опции для товаров в случае добавления опции категории
         * уже после добавления товаров в данную категорию
         * @return bool
         */
        public function initProductValues()
        {
                $command = Yii::app()->dbcatalog2->createCommand();

                $products = $command->select('id')
                        ->from('cat_product')
                        ->where('category_id=:cid',array(':cid'=>$this->category_id))
                        ->queryAll();

                $values = '';
                $separator = ',';

                $qt = count($products);
                $count = 0;
                foreach($products as $key=>$product)
                {
                        if($count == $qt-1) $separator = ';';
                        $values.='(' . $this->id . ',' . $product['id'] . ')' . $separator;
                        $count++;
                }

                if(empty($values))
                        return true;

                $table_name = Value::model()->tableName();
                Yii::app()->dbcatalog2->createCommand("insert into {$table_name} (option_id, product_id) values {$values}")->execute();

                return true;
        }

        /**
         * Очищает значения опции у всех товаров, ее использующих. Удаляет допустимые значения опции.
         */
        public function clearValues()
        {
                Value::model()->deleteAll('option_id=:oid AND product_id is null', array(':oid'=>$this->id));
                Value::model()->updateAll(array('value'=>null, 'desc'=>''), 'option_id=:oid AND product_id is not null', array(':oid'=>$this->id));
                return true;
        }

        /**
         * Возвращает список допустимых значений для текущей опции (если тип опции предусматривает список допустимых значений)
         * @return mixed
         */
        public function getAvailableValues()
        {
                if(!$this->_availableValues) {
                        if($this->parent_id)
                                $oid = $this->parent_id;
                        else
                                $oid = $this->id;

                        $criteria = new CDbCriteria();
                        $criteria->condition = 'option_id=:oid AND product_id is null';
                        $criteria->params = array(':oid'=>$oid);
                        $criteria->order = 'position';

                        $this->_availableValues = Value::model()->findAll($criteria);
                }
                return $this->_availableValues;
        }

        /**
         * Возвращает допустимые значения для опции через DAO для облегчения обработки
         * @return array
         */
        public function getDaoAvailableValues()
        {
                return Yii::app()->dbcatalog2->createCommand()
                        ->from(Value::model()->tableName())
                        ->where('option_id=:oid AND product_id is null', array(':oid'=>empty($this->parent_id) ? $this->id : $this->parent_id))
                        ->queryAll();
        }

        /**
         * Возвращает true если опция должна иметь список допустимых значений
         * @return mixed
         */
        public function checkValueList()
        {
                return self::$typeParams[$this->type_id]['valueList'];
        }

        /**
         * Возвращает значение текущей опции для указанного товара
         * @param Product $product
         * @return CActiveRecord|null
         */
        public function getProductValue($product = null)
        {
                if(!$product || !($product instanceof Product))
                        return null;

                return Value::model()->findByAttributes(array('product_id'=>$product->id, 'option_id'=>$this->id));
        }


        /**
         * Возвращает html код формы для установки дополнительных параметров опции в зависимости от ее типа
         * @return null|string
         */
        public function getParamsForm()
        {
                if(!isset(self::$typeParams[$this->type_id]['paramsEditable']) || !self::$typeParams[$this->type_id]['paramsEditable'])
                        return null;

                $html = '';

                switch($this->type_id) {
                        case self::TYPE_SIZE :
                                $params = $this->getParamsArray();
                                $size_unit = isset($params['size_unit']) ? $params['size_unit'] : 1;
                                $html.= CHtml::dropDownList("Option[$this->id][params][size_unit]", $size_unit, self::$units, array('style'=>'width:105px;'));
                                break;

                        default :
                                return '';
                }

                return $html;
        }

        /**
         * Возвращает массив дополнительных параметров опции
         */
        public function getParamsArray()
        {
                if(empty($this->param{0}))
                         return array();

                $params = unserialize($this->param);

                if(is_array($params))
                        return $params;
                else
                        return array();
        }

        /**
         * Сеттер дополнительных параметров опции
         * @param $value
         * @return mixed|void
         */
        public function setParam($value) {
                if(is_array($value)) {
                        return parent::__set('param', serialize($value));
                }
                else {
                        try{
                                $decoded_value = unserialize($value);
                        } catch (Exception $e) {
                                return parent::__set('param', null);
                        }
                        if(is_array($decoded_value))
                                return parent::__set('param', $value);
                }
        }

        /**
         * Валидатор опций
         * @param $attribute
         * @param $params
         */
        public function optionValidator($attribute, $params)
        {
                if($this->type_id == self::TYPE_SIZE) {

                        /**
                         * Включить/выключить возможность фильтрации по опции типа "Габарит"
                         */
                        if($this->filterable || $this->forminimized)
                                $result = $this->pushToFilterable();
                        else
                                $result = $this->popFromFilterable();

                        if(!$result)
                                $this->addError('filterable', 'Ошибка изменения типа фильтрации по опции');
                }
        }

        /**
         * Включает возможность фильтрации по текущей опции
         * @return bool
         */
        public function pushToFilterable()
        {
                $category = Category::model()->findByPk($this->category_id);

                /**
                 * Параметры категории
                 */
                $catParams = $category->getParamsArray();

                /**
                 * Ключ для сохранения списка фильтруемых опций в массиве параметров категории
                 */
                $key = 'filterable_' . $this->type_id;

                /**
                 * Массив опций, на которые уже включена возможность фильтрации по данной категории
                 */
                $items = array();
                if(isset($catParams[$key]))
                        $items = $catParams[$key];

                /**
                 * Завершение, если опция уже фильтруемая
                 */
                if(in_array($this->id, $items))
                        return true;

                /**
                 * Завершение, если нет места для вставки новой опции
                 */
                if(count($items) >= self::MAX_FILTERABLE_OPTION_QT)
                        return false;

                /**
                 * Проход по массиву фильтруемых опций и вставка в свободное место текущей опции
                 */
                for($i = 1; $i < self::MAX_FILTERABLE_OPTION_QT + 1; $i++ ) {

                        /**
                         * Вставка новой опции для фильтрации по ней
                         */
                        if(!isset($items['opt_val_'.$i])) {
                                $items['opt_val_'.$i] = $this->id; break;
                        } else
                                continue;
                }

                /**
                 * Сохранение обновленного списка фильтруемых опций
                 */
                $catParams[$key] = $items;
                $category->params = serialize($catParams);
                $category->saveNode(false);
                return true;
        }

        /**
         * Выключает возможность фильтрации по текущей опции
         * @return bool
         */
        public function popFromFilterable()
        {
                $category = Category::model()->findByPk($this->category_id);

                /**
                 * Параметры категории
                 */
                $catParams = $category->getParamsArray();

                /**
                 * Ключ для сохранения списка фильтруемых опций в массиве параметров категории
                 */
                $key = 'filterable_' . $this->type_id;

                /**
                 * Массив опций, на которые уже включена возможность фильтрации по данной категории
                 */
                $items = array();
                if(isset($catParams[$key]))
                        $items = $catParams[$key];

                /**
                 * Завершение, если опция отсутствует в списке фильтруемых
                 */
                if(!in_array($this->id, $items))
                        return true;

                /**
                 * Проход по массиву фильтруемых опций и вставка в свободное место текущей опции
                 */
                for($i = 1; $i < self::MAX_FILTERABLE_OPTION_QT + 1; $i++ ) {

                        /**
                         * Удаление текущей опции из списка фильтруемых
                         */
                        if(isset($items['opt_val_'.$i]) && $items['opt_val_'.$i] == $this->id)
                                unset($items['opt_val_'.$i]);
                        else
                                continue;
                }

                /**
                 * Сохранение обновленного списка фильтруемых опций
                 */
                $catParams[$key] = $items;
                $category->params = serialize($catParams);
                $category->saveNode(false);
                return true;
        }
}