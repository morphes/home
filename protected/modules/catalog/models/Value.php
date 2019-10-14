<?php

/**
 * This is the model class for table "cat_value".
 *
 * The followings are the available columns in table 'cat_value':
 * @property integer $id
 * @property integer $option_id
 * @property integer $product_id
 * @property integer $category_id
 * @property string $value
 */

/**
 * value row has next values for some types of options:
 * option_type = input  - in value column saved text
 * option_type = checkbox  - in value column saved boolean 0,1 or null
 * option_type = textarea  - in value column saved text
 * option_type = select  - in value column saved vale_id
 * option_type = select multiple  - in value column saved serialized array of value_id's. also stored in cat_value with parent_id,
 *      where column parent_id - id of current value and value column - id of selected value
 * option_type = color  - in value column saved color_id for table cat_color
 * option_type = style  - in value column saved style_id for table cat_style
 * option_type = image - value column not initialized (null). data stored in cat_value_file (value_id, image_id)
 */
class Value extends EActiveRecord
{
        const TYPE_STRING = 1; // Строка
        const TYPE_IMAGE = 2; // Файл изображения
        const TYPE_FILE = 3; // Любой файл

        private $_option;

        /**
         * @var временное хранилище мультизначений опции
         * нужно для валидации нескольких значений опции без фактического сохранения в базу
         */
        private $_multivalue;

        private $_multiImages;

        private $_multiColor;

        public function __set($name, $value)
        {
                $setter = 'set' . $name;
                if (method_exists($this, $setter))
                        return $this->$setter($value);
                else
                        return parent::__set($name, $value);
        }

        public function __get($name)
        {
                $getter = 'get' . $name;
                if (method_exists($this, $getter))
                        return $this->$getter();

                return parent::__get($name);
        }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Value the static model class
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
		return 'cat_value';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
                        // Валидаторы для допустимых значений опций
                        array('option_id, value', 'required', 'on'=>'optionValue'),
                        array('value', 'length', 'max'=>255,'on'=>'optionValue'),

                        // Валидаторы для значений опций товаров
                        array('product_id', 'required', 'on'=>'update, init'),
                        array('value', 'valueDataValidator', 'on'=>'update, init'),
                        array('value', 'valueRequiredValidator', 'on'=>'update'),

			array('option_id, product_id, category_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, option_id, product_id, value, _multivalue, category_id', 'safe', 'on'=>'search'),
		);
	}

        /**
         * Переопределение метода сохранения
         * @param bool $runValidation
         * @param null $attributes
         * @return bool
         */
        public function save($runValidation=true,$attributes=null)
        {
                if(!$runValidation || $this->validate($attributes)) {
                        if($this->getIsNewRecord())
                                return $this->insert($attributes);
                        else {
                                /**
                                 * Сохранение мультизначений с очисткой предыдущего набора
                                 */
                                if($this->product_id && Option::$typeParams[$this->option->type_id]['multiValue']) {
                                        $command = Yii::app()->db->createCommand();
                                        $command->update('cat_value', array('value'=>serialize($this->value)), 'id=:vid', array(':vid'=>$this->id));
                                        return true;
                                }

                                return $this->update($attributes);
                        }
                } else
                        return false;
        }

        /**
         * Проверка на наличие значения опции
         * @param $attribute
         * @param $params
         */
        public function valueRequiredValidator($attribute, $params)
        {
                /**
                 * Если опция не обязательна для заполнения или скрыта, то заполнять не обязательно
                 */
                if(!$this->option->required || $this->option->hide)
                        return true;

                if(!isset($params['message']) || empty($params['message']))
                        $params['message'] = 'Отсутствует значение';

                switch($this->option->type_id) {
                        case Option::TYPE_INPUT :
                                if(mb_strlen($this->value) == 0)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_TEXTAREA :
                                if(mb_strlen($this->value) == 0)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_CHECKBOX :
                                /**
                                 * Чекбокс может быть не отмеченным
                                 */
                                break;

                        case Option::TYPE_SELECT :
                                if(!$this->value)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_SELECTMULTIPLE :
                                if(!$this->value)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_STYLE :
                                if(!$this->value)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_COLOR :
                                if(!$this->value)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_IMAGE :
                                if(count($this->value) == 0)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_SIZE :
                                if(!$this->value)
                                        $this->addError($attribute, $params['message']);
                                break;
                }
        }


        /**
         * Валидатор для значений всех типов опций
         * @param $attribute
         * @param $value
         */
        public function valueDataValidator($attribute, $params)
        {
                if(!isset($params['message']) || empty($params['message']))
                        $params['message'] = 'Некорректное значение';

                switch($this->option->type_id) {
                        case Option::TYPE_INPUT :
                                /**
                                 * Любое значение
                                 */
                                break;

                        case Option::TYPE_TEXTAREA :
                                /**
                                 * Любое значение
                                 */
                                break;

                        case Option::TYPE_CHECKBOX :
                                if(!empty($this->value) && preg_match("/^\d+$/", $this->value) == 0)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_SELECT :
                                if(!empty($this->value) && preg_match("/^\d+$/", $this->value) == 0)
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_SELECTMULTIPLE :
                                if(is_array($this->value)) {
                                        foreach($this->value as $val) {
                                                if(preg_match("/^\d+$/", $val) == 0) {
                                                        $this->addError($attribute, $params['message']);
                                                        break;
                                                }
                                        }
                                }
                                break;

                        case Option::TYPE_STYLE :
                                if(!empty($this->value) && !Style::model()->exists('id=:id', array(':id'=>(int)$this->value)))
                                        $this->addError($attribute, $params['message']);
                                break;

                        case Option::TYPE_COLOR :
                                if(is_array($this->value)) {
                                        foreach($this->value as $val) {
                                                if(preg_match("/^\d+$/", $val) == 0) {
                                                        $this->addError($attribute, $params['message']);
                                                        break;
                                                }
                                        }
                                }
                                break;

                        case Option::TYPE_IMAGE :
                                if(is_array($this->value)) {
                                        foreach($this->value as $val) {
                                                if(preg_match("/^\d+$/", $val) == 0) {
                                                        $this->addError($attribute, $params['message']);
                                                        break;
                                                }
                                        }
                                }
                                break;

                        case Option::TYPE_SIZE :
                                if(!empty($this->value) && preg_match("/^[\d,.]+$/", $this->value) == 0)
                                        $this->addError($attribute, $params['message']);
                                break;
                }
        }

        public function setValue($value)
        {
                switch($this->option->type_id) {
                        case Option::TYPE_INPUT :
                                return parent::__set('value', CHtml::encode($value));

                        case Option::TYPE_TEXTAREA :
                                return parent::__set('value', CHtml::encode($value));

                        case Option::TYPE_CHECKBOX :
                                if(!$value) $value = 0;
                                return parent::__set('value', $value);

                        case Option::TYPE_SELECT :
                                return parent::__set('value', $value);

                        case Option::TYPE_STYLE :
                                return parent::__set('value', $value);

                        case Option::TYPE_COLOR :
                                // сохраняется допустимое значение опции (для категории)
                                if (!$this->product_id && !$this->_multiColor)
                                        return parent::__set('value', $value);

                                // если присваевается пустое значение опции товара, то конвертируем его в пустой массив
                                if(is_null($value))
                                        $value = array();

                                // сохраняется значение опции конкретного товара в промежуточную переменную
                                // без сохранения в базу
                                return $this->_multiColor = $value;

                        case Option::TYPE_SELECTMULTIPLE :

                                // сохраняется допустимое значение опции (для категории)
                                if (!$this->product_id && !$this->_multivalue)
                                        return parent::__set('value', $value);

                                // если присваевается пустое значение опции товара, то конвертируем его в пустой массив
                                if(is_null($value))
                                        $value = array();

                                // сохраняется значение опции конкретного товара в промежуточную переменную
                                // без сохранения в базу
                                return $this->_multivalue = $value;

                        case Option::TYPE_IMAGE :
                                return false;

                        case Option::TYPE_SIZE :
                                return parent::__set('value', $value);
                }
        }

        public function getValue()
        {
                switch($this->option->type_id) {
                        case Option::TYPE_INPUT :
                                return parent::__get('value');
                        case Option::TYPE_TEXTAREA :
                                return parent::__get('value');
                        case Option::TYPE_CHECKBOX :
                                return parent::__get('value');
                        case Option::TYPE_SELECT :
                                return parent::__get('value');
                        case Option::TYPE_STYLE :
                                return parent::__get('value');
                        case Option::TYPE_COLOR :
                                // возвращается допустимое значение опции (для категории)
                                if (!$this->product_id && !$this->_multiColor)
                                        return parent::__get('value');

                                // возвращается список значений опции конкретного товара
                                // если нет данных во временном хранилище $this->_multiColor, значит
                                // не было попытки сохранения данных из формы и нужно вернуть фактические значения из базы
                                elseif (!is_array($this->_multiColor) && $this->product_id)
                                {
                                        $this->_multiColor = self::serializeToArrray(parent::__get('value'));
                                }
                                return $this->_multiColor;

                        case Option::TYPE_SELECTMULTIPLE :
                                // возвращается допустимое значение опции (для категории)
                                if (!$this->product_id && !$this->_multivalue)
                                        return parent::__get('value');

                                // возвращается список значений опции конкретного товара
                                // если нет данных во временном хранилище $this->_multivalue, значит
                                // не было попытки сохранения данных из формы и нужно вернуть фактические значения из базы
                                elseif (!is_array($this->_multivalue) && $this->product_id)
                                {
                                        $this->_multivalue = self::serializeToArrray(parent::__get('value'));

                                }
                                return $this->_multivalue;

                        case Option::TYPE_IMAGE :
                                if (!is_array($this->_multiImages)){
                                        $this->_multiImages = array();
                                        $values = Yii::app()->db->createCommand()
                                                ->select('file_id')
                                                ->from('cat_value_file')
                                                ->where('value_id=:vid', array(':vid'=>$this->id))
                                                ->queryAll();
                                        foreach($values as $val)
                                                $this->_multiImages[] = $val['file_id'];
                                }
                                return $this->_multiImages;

                        case Option::TYPE_SIZE :
                                return parent::__get('value');
                }
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
			'option_id' => 'Опция',
			'product_id' => 'Товар',
			'value' => 'Значение',
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
		$criteria->compare('option_id',$this->option_id);
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('value',$this->value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

        /**
         * Возвращает опцию, которой принадлежит текущее значение
         * @return Option
         */
        public function getOption()
        {
                if(!$this->_option)
                        $this->_option = Option::model()->findByPk($this->option_id);

                return $this->_option;
        }

        public function saveMultiValue($value)
        {
                if(!is_array($value))
                        return null;

        }

        public function saveImageValue($value)
        {
                return null;
        }

        /**
         * Возвращает массив, сформированный из сериализованного значения
         * @param $value
         * @return array|mixed
         */
        static public function serializeToArrray($value)
        {
                if(empty($value{0}))
                        return array();

                try {
                        $array = @unserialize($value);
                } catch(Exception $e) {
                        $array = array();
                }

                if(is_array($array))
                        return $array;
                else
                        return array();
        }

}