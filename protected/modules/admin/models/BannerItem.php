<?php

/**
 * Модель таблицы "banner_item".
 *
 * The followings are the available columns in table 'banner_item':
 * @property integer $id
 * @property integer $user_id
 * @property string $customer
 * @property integer $type_id
 * @property integer $status
 * @property integer $file_id
 * @property integer $swf_file_id
 * @property string $htmlcode
 * @property integer $create_time
 * @property integer $update_time
 * @property string $url
 *
 * @author Roman Kuzakov
 * @version $Id$
 * @since 3.1
 */
class BannerItem extends CActiveRecord
{
	// константы обозначают префиксы имен ключей в редисе
	// по которым хранится статистика показов и кликов баннера
	const REDIS_STAT_VIEWS_VAR = 'banner_views_';
	const REDIS_STAT_CLICKS_VAR = 'banner_clicks_';

	private $_statViews;
	private $_statClicks;

	// типы баннеров
	const TYPE_HORIZONTAL = 1;
	const TYPE_VERTICAL = 2;

	/**
	 * @var CUploadedFile инстанс файла для загрузки
	 */
	public $swfFile;
	public $imageFile;

	public $errors;

	/**
	 * @var array лейблы типов
	 */
	static public $typeLabels = array(
		self::TYPE_HORIZONTAL=>'Горизонтальный',
		self::TYPE_VERTICAL=>'Вертикальный',
	);

	// статусы баннера
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;
	const STATUS_DELETED = 3;

	/**
	 * @var array лейблы для статусов
	 */
	static public $statusLabels = array(
		self::STATUS_ACTIVE=>'Активен',
		self::STATUS_INACTIVE=>'Не активен',
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return BannerItem the static model class
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
		return 'banner_item';
	}

	public function init()
	{
		parent::init();
		$this->onAfterValidate = array($this, 'abilityToSave');
		$this->onBeforeSave = array($this, 'setDate');
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
			array('user_id, type_id, customer, status, url', 'required', 'except'=>'init'),
			array('status', 'in', 'range'=>array_keys(self::$statusLabels)),
			array('type_id', 'in', 'range'=>array_keys(self::$typeLabels)),
			array('user_id', 'exist', 'className'=>'User', 'attributeName'=>'id'),
			array('htmlcode', 'length', 'max'=>3000),
			array('user_id, status, file_id, swf_file_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('customer', 'length', 'max'=>255, 'min'=>3),
			array('url', 'length', 'max'=>500, 'min'=>1),
//			array('url', 'url', 'message' => 'Неправильный URL сайта',
//					     'pattern'=>'/^(http(s?)\:\/\/)?(([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)(\.[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)+(\/[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)*(\/?(\?([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1}(&[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1})*){0,1})?))$/i',
//			),
			array('swfFile', 'file', 'types'=>'swf', 'maxSize'=> 300 * 1000, 'allowEmpty'=>true),
			array('imageFile', 'file', 'types'=>'jpg, gif, png', 'maxSize'=> 300 * 1000, 'allowEmpty'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, errors, customer, status, file_id, url, swf_file_id, create_time, update_time', 'safe', 'on'=>'search'),
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
			'itemSections'=>array(self::HAS_MANY, 'BannerItemSection', 'item_id'),
			'swf' => array(self::BELONGS_TO, 'UploadedFile', 'swf_file_id'),
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'Добавил',
			'customer' => 'Заказчик',
			'status' => 'Статус',
			'type_id' => 'Тип баннера',
			'file_id' => 'Статический баннер',
			'swf_file_id' => 'Динамический баннер',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'url' => 'Ссылка баннера',
			'htmlcode' => 'HTML код',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('customer',$this->customer);
		$criteria->compare('status',$this->status);
		$criteria->compare('file_id',$this->file_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Валидатор всех itemSections текущего баннера
	 * @return bool
	 */
	public function abilityToSave()
	{
		if ($this->status != self::STATUS_ACTIVE)
			return false;

		$transaction = Yii::app()->db->beginTransaction();
		$this->save(false);

		foreach($this->itemSections as $is) {
			if ( !$is->validate() ) {
				$this->addError('errors', 'Невозможно сохранить баннер с текущими статусом и типом');
				break;
			}
		}

		$transaction->rollback();
		return false;
	}

	/**
	 * Привязка текущего баннера к разделу сайта
	 * @param $sectionId
	 * @param $tariffId
	 * @param $startTime
	 * @param $endTime
	 * @return BannerItemSection|bool
	 */
	public function createInSection($sectionId, $tariffId, $startTime, $endTime)
	{
		$itemSection = new BannerItemSection();
		$itemSection->item_id = $this->id;
		$itemSection->section_id = $sectionId;
		$itemSection->tariff_id = $tariffId;
		$itemSection->start_time = $startTime;
		$itemSection->end_time = $endTime;

		if ($itemSection->save())
			return $itemSection;
		else
			return false;
	}

	/**
	 * Инициализация пустой секции баннера
	 * @return BannerItemSection|bool
	 */
	public function createEmptySection()
	{
		$itemSection = new BannerItemSection('init');
		$itemSection->item_id = $this->id;
		$itemSection->start_time = time();
		$itemSection->end_time = $itemSection->start_time + 10 * 24 * 3600;

		if ($itemSection->save())
			return $itemSection;
		else {
			return false;

		}
	}

	/**
	 * Обновление вспомогательной таблицы ротаций
	 */
	public function updateRotation()
	{
		// очистка всех ротаций для текущего баннера
		BannerRotation::model()->deleteAllByAttributes(array('item_id'=>$this->id));

		// генерация ротаций текущего баннера
		foreach ($this->itemSections as $itemSection) {

			if ( !$itemSection->geoExist() )
				continue;

			foreach ($itemSection->itemSectionGeos as $geo) {

				$rotation = new BannerRotation();
				$rotation->section_id = $itemSection->section_id;
				$rotation->item_id = $this->id;
				$rotation->type_id = $this->type_id;
				$rotation->start_time = $itemSection->start_time;
				$rotation->end_time = $itemSection->end_time;
				$rotation->tariff_id = $itemSection->tariff_id;
				$rotation->status = $this->status;
				$rotation->file_id = $this->file_id;
				$rotation->swf_file_id = $this->swf_file_id;
				$rotation->geo_id = $geo->id;
				$rotation->item_section_id = $itemSection->id;

				if ($geo->city_id)
					$rotation->city_id = $geo->city_id;
				elseif ($geo->region_id)
					$rotation->region_id = $geo->region_id;
				elseif ($geo->country_id)
					$rotation->country_id = $geo->country_id;
				else
					continue;

				$rotation->save();
			}
		}
	}

	/**
	 * Полное удаление информации о баннере из базы
	 */
	public function forceDelete()
	{
		foreach ($this->itemSections as $is)
			$is->forceDelete();

		$this->delete();
	}

	/**
	 * Возвращает ссылку на статический файл баннера (изображение)
	 */
	public function getImageLink()
	{
		if (!$this->file_id)
			return null;

		$uf = UploadedFile::model()->findByPk($this->file_id);

		if (!$uf)
			throw new CHttpException(500);

		return Yii::app()->createAbsoluteUrl('/' . UploadedFile::PUBLIC_PREFIX . '/' . $uf->path . '/' . $uf->name . '.' . $uf->ext);
	}

	/**
	 * Возвращает ссылку на swf файл баннера (flash)
	 */
	public function getSwfLink()
	{
		if (!$this->swf_file_id)
			return null;

		$uf = UploadedFile::model()->findByPk($this->swf_file_id);

		if (!$uf)
			throw new CHttpException(500);

		return Yii::app()->createAbsoluteUrl('/' . UploadedFile::PUBLIC_PREFIX . '/' . $uf->path . '/' . $uf->name . '.' . $uf->ext);
	}


	/**
	 * Возвращает тип файла баннера (flash / image)
	 */
	public function getBannerFileType()
	{
		if (!$this->file)
			return false;

		if ($this->file->ext == 'swf')
			return 'flash';
		else
			return 'image';
	}


	/**
	 * Возвращает статистику показов баннера
	 * @return int
	 */
	public function getStatViews()
	{
		if ( !$this->_statViews )
			$this->_statViews = (int) Yii::app()->redis->get(BannerItem::REDIS_STAT_VIEWS_VAR . $this->id);
		return $this->_statViews;
	}


	/**
	 * Возвращает статистику кликов по баннеру
	 * @return int
	 */
	public function getStatClicks()
	{
		if ( !$this->_statClicks )
			$this->_statClicks = (int) Yii::app()->redis->get(BannerItem::REDIS_STAT_CLICKS_VAR . $this->id);
		return $this->_statClicks;
	}
}