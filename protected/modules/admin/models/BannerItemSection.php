<?php

/**
 * Модель таблицы "banner_item_section".
 *
 * The followings are the available columns in table 'banner_item_section':
 * @property integer $id
 * @property integer $item_id
 * @property integer $section_id
 * @property integer $tariff_id
 * @property integer $start_time
 * @property integer $end_time
 *
 * @author Roman Kuzakov
 * @version $Id$
 * @since 3.1
 */
class BannerItemSection extends CActiveRecord
{
	/**
	 * @var null атрибут используется для добавления ошибок валидации в случаях, когда
	 * валидатор сообщает об ошибках модели "в целом", а не об ошибке конкретного атрибута
	 */
	protected $error;

	// процент показов для баннера (тариф)
	const TARIFF_5 = 5;
	const TARIFF_10 = 10;
	const TARIFF_33 = 33;
	const TARIFF_50 = 50;
	const TARIFF_66 = 66;
	const TARIFF_100 = 100;

	/**
	 * @var array лейблы для тарифов
	 */
	static public $tariffLabels = array(
		self::TARIFF_5 => '5% показов',
		self::TARIFF_10 => '10% показов',
		self::TARIFF_33 => '33,3% показов',
		self::TARIFF_50 => '50% показов',
		self::TARIFF_66 => '66,6% показов',
		self::TARIFF_100 => '100% показов',
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return BannerItemSection the static model class
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
		return 'banner_item_section';
	}

	public function init()
	{
		parent::init();
		$this->onAfterValidate = array($this, 'abilityToSave');
		$this->onAfterValidate = array($this, 'rotationTimeValidator');
		$this->onAfterSave = array($this, 'updateRotation');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('item_id', 'required'),
			array('section_id, tariff_id, start_time, end_time', 'required', 'except'=>'init'),
			array('item_id', 'exist', 'className'=>'BannerItem', 'attributeName'=>'id'),
			array('section_id', 'in', 'range'=>Config::$sections, 'message'=>'Укажите раздел'),
			array('tariff_id', 'in', 'range'=>array_keys(self::$tariffLabels), 'message'=>'Укажите тариф'),
			array('item_id, section_id, tariff_id, start_time, end_time', 'numerical', 'integerOnly'=>true),
			array('humanStartTime, humanEndTime', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, item_id, section_id, tariff_id, start_time, end_time', 'safe', 'on'=>'search'),
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
			'item'=>array(self::BELONGS_TO, 'BannerItem', array('item_id'=>'id')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'item_id' => 'Баннер',
			'section_id' => 'Раздел сайта',
			'tariff_id' => 'Тариф (% показов)',
			'start_time' => 'Начало ротации',
			'end_time' => 'Конец ротации',
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
		$criteria->compare('section_id',$this->section_id);
		$criteria->compare('tariff_id',$this->tariff_id);
		$criteria->compare('start_time',$this->start_time);
		$criteria->compare('end_time',$this->end_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Добавление региона/города/страны для ротации баннера на текущем разделе
	 * @param $geoType - тип гео-таргетинга (город/регион/страна). Допустимыми значениями являются значения
	 * констант BannerItemSectionGeo::GEO_TYPE_*
	 * @param $geoValue
	 * @return bool
	 */
	public function assignToGeo($geoType, $geoValue)
	{
		if ( $this->isNewRecord )
			return false;

		$itemSectionGeo = new BannerItemSectionGeo();
		$itemSectionGeo->item_id = $this->item_id;
		$itemSectionGeo->section_id = $this->section_id;
		$itemSectionGeo->{$geoType} = $geoValue;
		$itemSectionGeo->item_section_id = $this->id;

		if ($itemSectionGeo->validate(null, false))
			$itemSectionGeo->save(false);
		return $itemSectionGeo;
	}

	/**
	 * Проверяет наличие привязки к региону текущей связки "баннер-раздел сайта"
	 * @return bool
	 */
	public function geoExist()
	{
		return BannerItemSectionGeo::model()->exists('item_section_id=:item_section_id', array(
			':item_section_id'=>$this->id
		));
	}

	/**
	 * Запускает валидаторы для всех BannerItemSectionGeo текущего объекта
	 * @return bool
	 */
	public function abilityToSave()
	{
		foreach($this->itemSectionGeos as $geo) {
			$geo->setItemSection($this);
			$result = $geo->validate();
			$geo->resetItemSection();
			if ( $result === false ) {
				$this->addError('error', $geo->getError('error'));
				return false;
			}
		}
		return true;
	}

	/**
	 * Возвращает все гео-привязки баннера на текущем разделе сайта
	 * @return array
	 */
	public function getItemSectionGeos()
	{
		return BannerItemSectionGeo::model()->findAllByAttributes(array(
			'item_section_id'=>$this->id,
		));
	}

	/**
	 * Валидатор времени показов
	 */
	public function rotationTimeValidator()
	{
		$time = $this->end_time - $this->start_time;

		if ( $time <= 0 )
			$this->addError('error', 'Время показов указано не верно');

//		if ( $time % (10 * 24 * 3600) != 0 )
//			$this->addError('error', 'Время показов должно быть кратно 10 дням');
	}

	/**
	 * Удаление связки баннер-раздел и связанных гео и ротаций
	 */
	public function forceDelete()
	{
		$conn = Yii::app()->db;

		$conn->createCommand()->delete('banner_rotation', 'item_section_id=:item_section_id', array(
			':item_section_id'=>$this->id,
		));
		$conn->createCommand()->delete('banner_item_section_geo', 'item_section_id=:item_section_id', array(
			':item_section_id'=>$this->id,
		));

		$this->delete();

		return true;
	}

	/**
	 * Возвращает человекопонятное время начала показов
	 * @return string
	 */
	public function getHumanStartTime()
	{
		return date('d.m.Y', $this->start_time);
	}

	/**
	 * Возвращает человекопонятное время окончания показов
	 * @return string
	 */
	public function getHumanEndTime()
	{
		return date('d.m.Y', $this->end_time);
	}

	/**
	 * Сохраняет start_time, приводя человекопонятную дату в unix timestamp
	 * @param $value
	 */
	public function setHumanStartTime($value)
	{
		$this->start_time = strtotime($value);
	}

	/**
	 * Сохраняет end_time, приводя человекопонятную дату в unix timestamp
	 * @param $value
	 */
	public function setHumanEndTime($value)
	{
		$this->end_time = strtotime($value);
	}

	/**
	 * Обновление вспомогательной таблицы banner_rotation
	 * @see http://doc.myhome.ru/p/site-banners
	 */
	public function updateRotation()
	{
		BannerItem::model()->findByPk($this->item_id)->updateRotation();
	}


	/**
	 * Получение секции, соответствующей текущему запросу
	 * @param $controller CController
	 * @return integer
	 */
	static public function getSection($controller)
	{
		// сбор информации о текущем запросе
		$mName = isset($controller->module->id) ? $controller->module->id : null;
		$cName = isset($controller->id) ? $controller->id : null;
		$aName = isset($controller->action->id) ? $controller->action->id : null;

		// определение секции, соответствующей запросу
		if ( $mName === null && $cName === 'site' && $aName === 'index')
			return Config::SECTION_HOME;

		if ( $mName === 'catalog' )
			return Config::SECTION_CATALOG;

		if ( $mName === 'social' )
			return Config::SECTION_FORUM;

		if ( $mName === 'idea' )
			return Config::SECTION_IDEA;

		if ( $mName === 'media' )
			return Config::SECTION_JOURNAL;

		if ( $mName === 'member' && $cName === 'specialist')
			return Config::SECTION_SPECIALIST;

        if ($mName === 'member' && $cName === 'profile')
            return Config::SECTION_PROFILE;

        if ($mName === 'tenders' && $cName === 'tender' && $aName === 'list')
            return Config::SECTION_TENDER_LIST;

        if ($mName === 'tenders' && $cName === 'tender' && $aName === 'view')
            return Config::SECTION_TENDER_ITEM;

		return false;
	}


	/**
	 * Возврат списка допустимых разделов сайта в зависимости от типа баннера
	 * @param integert $type_id тип баннера
	 *
	 * @return array
	 */
	static public function getAvailableSections($type_id)
	{
		if ( $type_id == BannerItem::TYPE_HORIZONTAL )
			return array(
				Config::SECTION_ALL => 'Все разделы',
				Config::SECTION_HOME => 'Главная страница сайта',
				Config::SECTION_CATALOG => 'Каталог товаров (сквозной, все страницы)',
				Config::SECTION_IDEA => 'Идеи (сквозной, все страницы)',
				Config::SECTION_SPECIALIST => 'Специалисты (сквозной, все страницы)',
				Config::SECTION_JOURNAL => 'Журнал (сквозной, на главную стр. журнала, новости, знания, события)',
				Config::SECTION_FORUM => 'Форум (сквозной, на все страницы)',
                Config::SECTION_PROFILE => 'Профиль пользователя',
                Config::SECTION_TENDER_LIST => 'Список заказов',
                Config::SECTION_TENDER_ITEM => 'Страница заказа',
            );

		if ( $type_id == BannerItem::TYPE_VERTICAL )
			return array(
				Config::SECTION_ALL => 'Все разделы',
				Config::SECTION_HOME => 'Главная страница сайта',
				Config::SECTION_IDEA => 'Идеи (на страницах списка)',
				Config::SECTION_SPECIALIST => 'Специалисты (сквозной, на все страницы)',
				Config::SECTION_FORUM => 'Форум (главная страница)',
				Config::SECTION_NEWS => 'Новости (на страницах списка)',
				Config::SECTION_KNOWLEDGE => 'Знания (на страницах списка)',
				Config::SECTION_EVENT => 'События (на страницах списка)',
				Config::SECTION_CATALOG => 'Каталог товаров (на страницах списка)',

			);
	}


	/**
	 * Актуализирует section_id в BannerItemSectionGeo
	 * @return bool
	 */
	public function updateGeo()
	{
		return Yii::app()->db->createCommand()
			->update(BannerItemSectionGeo::model()->tableName(),
			array('section_id'=>$this->section_id), 'item_section_id=:isid', array(':isid'=>$this->id));
	}
}