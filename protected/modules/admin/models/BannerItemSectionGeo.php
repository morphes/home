<?php

/**
 * Модель таблицы "banner_item_section_geo".
 *
 * The followings are the available columns in table 'banner_item_section_geo':
 * @property integer $id
 * @property integer $item_id
 * @property integer $section_id
 * @property integer $city_id
 * @property integer $region_id
 * @property integer $item_section_id
 * @property integer $country_id
 *
 * @author Roman Kuzakov
 * @version $Id$
 * @since 3.1
 */
class BannerItemSectionGeo extends CActiveRecord
{
	/**
	 * @var связанный объект BannerItemSection
	 */
	private $_itemSection;

	/**
	 * @var null атрибут используется для добавления ошибок валидации в случаях, когда
	 * валидатор сообщает об ошибках модели "в целом", а не об ошибке конкретного атрибута
	 */
	protected $error;

	// тип гео-таргетинга баннера
	const GEO_TYPE_CITY = 'city_id';
	const GEO_TYPE_REGION = 'region_id';
	const GEO_TYPE_COUNTRY = 'country_id';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return BannerItemSectionGeo the static model class
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
		return 'banner_item_section_geo';
	}

	public function init()
	{
		parent::init();
		$this->onAfterValidate = array($this, 'abilityToInsert');
		$this->onAfterSave = array($this, 'updateRotation');
	}

	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if($this->isNewRecord)
			$this->create_time=$this->update_time=time();
		else
			$this->update_time=time();
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('section_id, item_section_id', 'required'),
			array('item_id, section_id, item_section_id, city_id, region_id, country_id', 'numerical', 'integerOnly'=>true),
			array('item_id', 'exist', 'className'=>'BannerItem', 'attributeName'=>'id'),
			array('section_id', 'in', 'range'=>Config::$sections),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, item_id, item_section_id, section_id, city_id, region_id, country_id', 'safe', 'on'=>'search'),
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
			'city_id' => 'Город',
			'region_id' => 'Регион',
			'country_id' => 'Страна',
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
		$criteria->compare('item_id',$this->item_id);
		$criteria->compare('section_id',$this->section_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('region_id',$this->region_id);
		$criteria->compare('country_id',$this->country_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Возвращает связанный BannerItemSection
	 * @return CActiveRecord
	 */
	public function getItemSection()
	{
		if (!$this->_itemSection)
			$this->_itemSection = BannerItemSection::model()->findByPk($this->item_section_id);
		return $this->_itemSection;
	}

	/**
	 * Устанавливает объект связанного BannerItemSection
	 * Требуется для вызова валидации BannerItemSectionGeo без сохранения в базу изменений  BannerItemSection
	 * (валидация Geo перед сохранением изменений в ItemSection)
	 */
	public function setItemSection($itemSection)
	{
		if ($itemSection instanceof BannerItemSection)
			$this->_itemSection = $itemSection;
	}

	/**
	 * Сбрасывает _itemSection, установленный методом setItemSection
	 */
	public function resetItemSection()
	{
		$this->_itemSection = null;
	}

	/**
	 * Функция-валидатор, определяет возможность сохранения гео-привязки баннера в указанном разделе
	 * Вставка возможна тогда, когда в текущем разделе сайта и регионе ротации баннера нет других баннеров, или,
	 * суммарный объем потребляемого трафика которых не превышает трафика, предусмотренного тарифом текущего баннера.
	 */
	public function abilityToInsert()
	{
		$conn = Yii::app()->db;


		// поиск всех ротаций, которые авктивны в то же время, что и текущая
		$condition = '((start_time < :et and :et <= end_time) or (:st <= start_time and start_time < :et) or '
			. '(:st < end_time and end_time <= :et))';

		$condition.= ' and type_id=:type_id';
		$condition.= ' and status=:status';

		$params = array(
			':st'=>$this->itemSection->start_time,
			':et'=>$this->itemSection->end_time,
			':sid'=>$this->getItemSection()->section_id,
			':allsid'=>Config::SECTION_ALL,
			':type_id'=>$this->item->type_id,
			':status'=>BannerItem::STATUS_ACTIVE,
		);

		// исключение текущей ротации из списка аналогичных
		if ( !$this->isNewRecord ) {
			$condition.= ' and (geo_id<>:gid)';
			$params[':gid']=$this->id;
		}

		// поиск всех ротаций, транслирующихся в том же разделе, что и текущая
		// note: так же добавлено условие поиска ротаций, транслирующихся по всем разделам сайта, т.к.
		// такие ротации так же транслируются на разделе текущей ротации
		$condition.=' and (section_id=:sid or section_id=:allsid)';

		// если указан город для ротациии, ищем ротации с таким же городом, регионом и страной
		if ( $this->city_id ) {
			$city = City::model()->findByPk($this->city_id);
			if ( !$city ) {
				$this->addError('city_id', 'Указанный город не существует');
				return false;
			}
			$condition.=' and (city_id=:city_id or region_id=:region_id or country_id=:country_id)';
			$params[':city_id']=$city->id;
			$params[':region_id']=$city->region_id;
			$params[':country_id']=$city->country_id;

			// если указан регион для ротации, ищем ротации с таким же регионом и страной
		} elseif ( $this->region_id ) {
			$region = Region::model()->findByPk($this->region_id);
			if ( !$region ) {
				$this->addError('region_id', 'Указанный регион не существует');
				return false;
			}
			$condition.=' and (region_id=:region_id or country_id=:country_id)';
			$params[':region_id']=$region->id;
			$params[':country_id']=$region->country_id;

			// если указана страна для ротации, ищем ротации с такой же страной
		} elseif ( $this->country_id ) {
			$country = Country::model()->findByPk($this->country_id);
			if ( !$country ) {
				$this->addError('country_id', 'Указанная страна не существует');
				return false;
			}
			$condition.=' and (country_id=:country_id)';
			$params[':country_id']=$country->id;

			// если ничего не указано
		} else {
			$this->addError('error', 'Необходимо указать город, регион или страну ротации баннера');
			return false;
		}

		// поиск аналогичных ротаций
		// аналогичными считаются ротации, крутящиеся по тому же региону, в том же разделе и в тех же
		// временных интервалах
		$similarRotations = $conn->createCommand()->from('banner_rotation')
			->where($condition, $params)->queryAll();

		// если аналогичных ротаций нет, то нет и проблем
		if ( count($similarRotations) == 0 )
			return true;

		// подсчет уже используемого трафика в предполагаемый период создающейся ротации
		$usedTrafficPercent = 0;
		foreach ($similarRotations as $sr)
			$usedTrafficPercent+=$sr['tariff_id'];

		// проверка возможность включения ротации с учетом уже использованного трафика
		if ( ($this->itemSection->tariff_id + $usedTrafficPercent) > 100 ) {
			$this->addError('error', 'Выбранный процент показов превышает допустимое '
				. 'значение в указанном периоде и в указанном разделе сайта');
			return false;
		}

		return true;
	}

	/**
	 * Обновление вспомогательной таблицы banner_rotation
	 * @see http://doc.myhome.ru/p/site-banners
	 */
	public function updateRotation()
	{
		$this->item->updateRotation();
	}

	/**
	 * Возвращает строку формата "Город: Новосибирск" или "Страна: Россия"
	 */
	public function getLabel()
	{
		$label = '';
		$object = '';

		if ($this->city_id) {
			$label = 'Город: ';
			$object = City::model()->findByPk($this->city_id);
		}
		if ($this->country_id) {
			$label = 'Страна: ';
			$object = Country::model()->findByPk($this->country_id);
		}
		if ($this->region_id) {
			$label = 'Регион: ';
			$object = Region::model()->findByPk($this->region_id);
		}

		if ($object)
			return $label . $object->name;
		else
			return null;
	}

	/**
	 * Полное удаление текущего объекта
	 */
	public function forceDelete()
	{
		$this->delete();
		$this->updateRotation();
	}
}