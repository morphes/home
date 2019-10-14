<?php

/**
 * This is the model class for table "cat_contractor".
 *
 * The followings are the available columns in table 'cat_contractor':
 * @property integer $id
 * @property integer $status
 * @property integer $worker_id
 * @property string $name
 * @property integer $create_time
 * @property integer $update_time
 * @property string $comment
 * @property string $site
 * @property string $legal_person
 * @property string $legal_address
 * @property string $actual_address
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property integer $bank_id
 * @property string $current_account
 * @property string $taxation_system
 * @property string $office_phone
 * @property string $office_fax
 * @property string $email
 */
class Contractor extends Catalog2ActiveRecord
{
	const STATUS_IN_WORK = 1;
	const STATUS_IN_FINALIZING = 2;
	const STATUS_POSTPONED = 3;
	const STATUS_PROCESSED = 4;

	const STATUS_DELETED = 5;

	public static $statusNames = array(
		self::STATUS_IN_WORK => 'В работе',
		self::STATUS_IN_FINALIZING => 'В доработке',
		self::STATUS_POSTPONED => 'Отложен',
		self::STATUS_PROCESSED => 'Обработан',
		self::STATUS_DELETED => 'Удален',
	);

	const NALOG_NONE = 0;
	const NALOG_NDS = 1;
	const NALOG_NO_NDS = 2;

	public static $nalogNames = array(
		self::NALOG_NONE => '',
		self::NALOG_NDS => 'С НДС',
		self::NALOG_NO_NDS => 'Без НДС',
	);

	/** FOR FILTERING BY TIME */
	public $start_time = null;
	public $end_time = null;

	/**
	 * Удаление связанных данных, call before change status to deleted
	 */
	public function removeLinkedData()
	{
		VendorContractor::model()->deleteAllByAttributes(array('contractor_id'=>$this->id));
		Store::model()->updateAll(array('contractor_id'=>null), 'contractor_id=:cid', array(':cid'=>$this->id));
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, 'updateSphinx');
	}

	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:contractor', $this->id);
	}

	public function setDate()
	{
		if ($this->isNewRecord) {
			$this->create_time = $this->update_time = time();
		} else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Contractor the static model class
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
		return 'cat_contractor';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('worker_id, name', 'required'),
			array('status, worker_id, bank_id, inn, kpp, ogrn, current_account',  'numerical', 'integerOnly'=>true),
			array('name, site, legal_person, legal_address, actual_address, office_phone, office_fax', 'length', 'max'=>255),
			array('inn', 'length', 'max'=>12, 'min'=>10),
			array('kpp', 'length', 'max'=>9, 'min'=>9),
			array('taxation_system', 'in', 'range'=>array(self::NALOG_NDS, self::NALOG_NO_NDS, self::NALOG_NONE)),
			array('ogrn', 'length', 'max'=>15, 'min'=>13),
			array('current_account', 'length', 'max'=>20, 'min'=>20),
			array('bank_id', 'exist', 'className'=>'Bank', 'attributeName'=>'id'),

			array('comment', 'length', 'max'=>3000),
			array('email', 'length', 'max'=>50),
			array('email', 'email'),
			array('site', 'url',
				'message' => 'Неправильный URL сайта',
				'pattern'=>'/^(http(s?)\:\/\/)?(([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)(\.[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)+(\/[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)*(\/?(\?([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1}(&[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1})*){0,1})?))$/i',
			),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, start_time, end_time, worker_id, name, create_time, update_time, email', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'status' => 'Статус',
			'worker_id' => 'Кто связывается',
			'name' => 'Название',
			'site' => 'Сайт',
			'legal_person' => 'Юр. Лицо',
			'comment' => 'Комментарий',
			'legal_address' => 'Юр. Адрес',
			'actual_address' => 'Факт. адрес',
			'inn' => 'ИНН',
			'kpp' => 'КПП',
			'ogrn' => 'ОГРН',
			'current_account' => 'Рассчетный счет',
			'taxation_system' => 'Система налогооблажения',
			'office_phone' => 'Телефон офиса',
			'office_fax' => 'Факс офиса',
			'email' => 'Email',

			'bank_id' => 'Банк',

			'create_time' => 'Дата создания',
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
		$criteria->compare('status',$this->status);
		$criteria->compare('worker_id',$this->worker_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('email',$this->email,true);

		if ($this->start_time)
			$criteria->compare('t.create_time', '>='.$this->start_time);
		if ($this->end_time)
			$criteria->compare('t.create_time', '<='.($this->end_time+86400));

		if ($this !== self::STATUS_DELETED) {
			$criteria->addNotInCondition('status', array(self::STATUS_DELETED));
		}

		$sort = new CSort();
		$sort->defaultOrder = array('id' => CSort::SORT_DESC);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>$sort,
			'pagination'=>array(
				'pageSize'=>30,
			),
		));
	}

	public function getPublicStatuses()
	{
		$data = self::$statusNames;
		unset($data[self::STATUS_DELETED]);
		return $data;
	}

	/**
	 * Получение имени связывающегося с контрагентом
	 * @return string
	 */
	public function getWorkerName()
	{
		if (is_null($this->worker_id))
			return '';
		$worker = User::model()->findByPk($this->worker_id);
		if (is_null($worker))
			return '';
		return $worker->name;
	}

	/**
	 * Список производителей контрагента
	 * @return array|mixed|null
	 */
	public function getVendors()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=t.id';
		$criteria->condition = 'cvc.contractor_id=:cid';
		$criteria->params = array(':cid'=>$this->id);

		return Vendor::model()->findAll($criteria);
	}

	/**
	 * Список магазинов контрагента
	 * @return array|mixed|null
	 */
	public function getStores()
	{
		return Store::model()->findAllByAttributes(array('contractor_id'=>$this->id));
	}

	/**
	 * @static
	 * Получение списка сэйлзов для дропдауна
	 */
	public static function getSalesList()
	{
		$criteria = new CDbCriteria();
		$criteria->compare('role', array(User::ROLE_SALEMANAGER, User::ROLE_MODERATOR));
		$criteria->compare('status', User::STATUS_ACTIVE);
		$salesList = User::model()->findAll($criteria);
		return array(''=>'Все')+CHtml::listData($salesList, 'id', 'name');
	}


	public function getProductCount()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=t.vendor_id';
		$criteria->condition = 'cvc.contractor_id=:cid AND t.status=:st';
		$criteria->params = array(':cid'=>intval($this->id), ':st'=>Product::STATUS_ACTIVE);

		return Product::model()->count($criteria);
	}

	/**
	 * Строка с данными о банке
	 */
	public function getBankData()
	{
		if (is_null($this->bank_id))
			return '';
		$bank = Bank::model()->findByPk($this->bank_id);
		return is_null($bank) ? '' : $bank->name.' (БИК: '.$bank->bic.' Корр.счет: '.$bank->corr_account.')';
	}
}