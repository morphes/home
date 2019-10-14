<?php

/**
 * This is the model class for table "index_product_brand".
 *
 * The followings are the available columns in table 'index_product_brand':
 * @property integer $id
 * @property integer $type
 * @property integer $image_id
 * @property integer $status
 * @property string $name
 * @property integer $city_id
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexProductBrand extends CActiveRecord implements IUploadImage
{
	// Типы логотипа
	const TYPE_VENDOR = 1;
	const TYPE_STORE = 2;

	// Статусы
	const STATUS_ACTIVE = 1;	// Активен
	const STATUS_INACTIVE = 2;	// Не активен


	// Имена статусов
	public static $statusName = array(
		self::STATUS_INACTIVE => 'не активен',
		self::STATUS_ACTIVE => 'активен',
	);

	// Названия типов
	public static $typeName = array(
		self::TYPE_VENDOR => 'Производитель',
		self::TYPE_STORE => 'Магазин'
	);

	// Логотип
	public $file;

	private $_imageType = null;

	public static $preview = array(
		'resize_90' => array(90, 90, 'resize', 80),
	);

	// Набор табов элемента
	public $tabIds;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IndexProductBrand the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	public function init()
	{
		$this->onAfterSave = array($this, 'clearCache');

		parent::init();
	}


	/**
	 * Удаляет данные из кеша
	 */
	public function clearCache()
	{
		$tabs = IndexProductTab::getActiveTabs();
		if (!empty($tabs)) {

			foreach ($tabs as $tab) {
				// Чистим логотипы в кеше по влкадкам
				Yii::app()->cache->delete(
					self::getCacheKeyBrand($tab['id'])
				);
			}
		}
	}


	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
		);
	}


	public function afterFind()
	{
		// После нахождения любого элемента, получаем его вкалдки
		$this->tabIds = array_keys($this->tabs);
		parent::afterFind();
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'index_product_brand';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, item_id, image_id, status, city_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('item_id, name, city_id', 'required'),
			array('name', 'length', 'max'=>50),
			array('file, tabIds', 'safe'),
			array('file', 'file', 'types'=> 'jpg, bmp, png, jpeg', 'maxFiles'=> 1, 'maxSize' => 104857600000, 'allowEmpty' => true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, image_id, status, name, create_time, update_time, city_id', 'safe', 'on'=>'search'),
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
			'uploadedFile' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'city' => array(self::BELONGS_TO, 'City', 'city_id'),
			'tabs' => array(self::MANY_MANY, 'IndexProductTab', 'index_product_brand_tab(brand_id, tab_id)', 'index' => 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'type'        => 'Тип',
			'item_id'     => 'Производитель/Магазин',
			'image_id'    => 'Логотип',
			'file'        => 'Логотип вручную',
			'status'      => 'Статус',
			'name'        => 'Название',
			'create_time' => 'Дата создания',
			'city_id'     =>'Город показов',
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
		$criteria->compare('type',$this->type);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('name',$this->name,true);

		// Дата начала регитсрации
		if (($update_from = Yii::app()->request->getParam('update_from'))) {
			$criteria->compare('update_time', '>=' . strtotime($update_from));
		}

		// Дата окончания регистрации
		if (($update_to = Yii::app()->request->getParam('update_to'))) {
			$criteria->compare('update_time', '<' . strtotime('+1 day', strtotime($update_to)));
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Возваращает статус для записи в виде html строки,
	 * с расцветкой взависимости от статуса.
	 *
	 * @return string
	 */
	public function getStatusHtml()
	{
		$html = '';
		if (isset(self::$statusName[$this->status])) {

			switch($this->status) {
				case self::STATUS_ACTIVE:
					$cls = 'success';
					break;
				case self::STATUS_INACTIVE:
					$cls = 'important';
					break;
				default:
					$cls = '';
			}
			$html .= CHtml::tag(
				'span',
				array(
					'class' => 'item_status label ' . $cls,
					'data-id' => $this->id
				),
				self::$statusName[$this->status]
			);

		} else {
			$html .= 'N/A';
		}

		return $html;
	}


	/**
	 * Получение пути для сохранения файла, в формате для uploadedFile
	 * @return string | false для новых записей
	 * @throws CException
	 */
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'logo': return 'indexpage/brand';
			default: throw new CException('Invalid upload image type');
		}
	}

	/**
	 * Получение имени для сохранения файла, в формате для uploadedFile
	 * @return string | false для новых записей
	 * @throws CException
	 */
	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'logo': return $this->id . '_' . time() . '_' . rand(0,9);
			default: throw new CException('Invalid upload image type');
		}
	}

	/**
	 * @param name
	 * Установка типа загружаемого изображения для модели
	 */
	public function setImageType($name)
	{
		$this->_imageType = $name;
	}

	/**
	 * Сброс установленного типа изображения
	 * @return mixed
	 */
	public function flushImageType()
	{
		$this->_imageType = null;
		return true;
	}

	/**
	 * Получение ID владельца модели
	 */
	public function getAuthorId()
	{
		return Yii::app()->user->id;
	}

	/**
	 * Проверка доступа к объекту пользователем
	 * @return bool true-имеет доступ
	 */
	public function checkAccess()
	{
		return in_array(
			Yii::app()->user->model->role,
			array(
				BaseUser::ROLE_POWERADMIN,
				BaseUser::ROLE_ADMIN,
				BaseUser::ROLE_MODERATOR,
				BaseUser::ROLE_JUNIORMODERATOR,
				BaseUser::ROLE_SENIORMODERATOR
			)
		);
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'logo': return array(
				'realtime' => array(
					self::$preview['resize_90'],
				),
				'background' => array(
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}

	public function saveTabs($tabIds)
	{

		if ( ! empty($tabIds)) {

			Yii::app()->db
				->createCommand('DELETE FROM index_product_brand_tab WHERE brand_id = :bid')
				->bindValue(':bid', $this->id)
				->execute();

			foreach ($tabIds as $tid) {
				Yii::app()->db
					->createCommand("INSERT INTO index_product_brand_tab (`brand_id`, `tab_id`)"
						." VALUES ('".$this->id."', '".intval($tid)."')")
					->execute();
			}
		}
	}


	/**
	 * Возвращает список активных брендов (логотипов).
	 *
	 * @param integer $tab_id Идентификатор вкладки
	 * @param bool $no_cache Флаг, при выставлении которого, кеш не используется.
	 *
	 * @return array Массив данных.
	 * @throws CHttpException
	 */
	public static function getActiveBrands($tab_id, $no_cache = false, $city_id=null, $limit = 5)
	{
		Yii::import('application.modules.catalog.models.Vendor');

		$tabId = (int)$tab_id;

		if (!IndexProductTab::model()->exists(
			'`id` = :id',
			array(':id' => $tabId)
		)) {
			throw new CHttpException(404);
		}

		if ($no_cache === true)
			$result = false;
		else
			$result = Yii::app()->cache->get(self::getCacheKeyBrand($tabId));

		if ($result === false) {

			$result = array();

			$models = IndexProductBrand::model()->findAll(array(
				'join'      => 'INNER JOIN index_product_brand_tab ipt'
					. ' ON ipt.brand_id = t.id',
				'condition' => 'status = :st AND ipt.tab_id = :tab and city_id=:cityid',
				'params'    => array(
					':st' => IndexProductBrand::STATUS_ACTIVE,
					':tab'=> $tabId,
					':cityid'=> (int) $city_id,
				),
			));

			shuffle($models);

			foreach ($models as $mod) {

				switch ($mod->type) {
					case IndexProductBrand::TYPE_STORE:
						$url = Yii::app()->createUrl('/catalog/store/index', array('id' => $mod->item_id));
						break;

					case IndexProductBrand::TYPE_VENDOR:
						$url = Vendor::getLink($mod->item_id);
						break;

					default:
						$url = '#';
						break;
				}

				$result[] = array(
					'name'     => $mod->name,
					'url'      => $url,
					'srcImage' => $mod->uploadedFile->getPreviewName(self::$preview['resize_90'])
				);

				if (count($result) >= $limit) {
					break;
				}
			}

			if ($no_cache === false)
				Yii::app()->cache->set(self::getCacheKeyBrand($tabId), $result, Cache::DURATION_MAIN_PAGE);
		}

		return $result;
	}


	/**
	 * Возвращает ключ для кеша, по которому хранится список логотипов
	 * для указанной вкладки $tabId
	 *
	 * @param $tabId Идентификатор вкладки
	 *
	 * @return string Ключ для кеша
	 * @throws CHttpException Выкидивает ошибку 400, если $tabId <= 0
	 */
	private static function getCacheKeyBrand($tabId)
	{
		if (intval($tabId) <= 0) {
			throw new CHttpException(
				400,
				'Параметр $tabId должен быть положительным числом'
			);
		}

		return 'INDEX:PROMO_PRODUCT:BRANDS:' . $tabId;
	}

}