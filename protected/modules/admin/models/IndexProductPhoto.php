<?php

/**
 * This is the model class for table "index_product_photo".
 *
 * The followings are the available columns in table 'index_product_photo':
 * @property integer $id
 * @property integer $image_id
 * @property integer $type
 * @property integer $status
 * @property integer $price
 * @property string $name
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexProductPhoto extends CActiveRecord
{
	// Типы фотографий
	const TYPE_PROMO = 1;
	const TYPE_PREVIEW = 2;

	// Статусы
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	const RATIO_PROMO = 1.384615385;
	const RATIO_PREVIEW = 1.189189189;

	// Имена статусов
	public static $statusName = array(
		self::STATUS_ACTIVE => 'активен',
		self::STATUS_INACTIVE => 'не активен'
	);

	// Названия типов
	public static $typeName = array(
		self::TYPE_PROMO => 'Промо (большая)',
		self::TYPE_PREVIEW => 'Превью (мал.справа)'
	);

	// Набор табов для фотографий.
	public $tabIds;

	public static $preview = array(
		'crop_540x390' => array(540, 390, 'crop', 80),
		'crop_220x185' => array(220, 185, 'crop', 80),
		// Используется для вывода в админке для JCrop'а картинки товара
		'resize_210' => array(210, 210, 'resize', 80),
		'resize_540' => array(540, 540, 'resize', 80),
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IndexProductPhoto the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
		);
	}


	public function init()
	{
		$this->onAfterSave = array($this, 'saveTabs');
		$this->onAfterSave = array($this, 'clearCache');

		parent::init();
	}


	/**
	 * Удаляет из кеша данные по фоткам.
	 */
	public function clearCache()
	{
		$tabs = IndexProductTab::getActiveTabs();
		if (!empty($tabs)) {

			foreach ($tabs as $tab) {

				// Удаляем кеши для больших фотографий
				Yii::app()->cache->delete(
					self::getCacheKeyBig($tab['id'])
				);

				// Удаляем кеши для малых фотографий
				Yii::app()->cache->delete(
					self::getCacheKeySmall($tab['id'])
				);
			}
		}
		
	}


	public function afterFind()
	{
		$this->tabIds = array_keys($this->tabs);
		parent::afterFind();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'index_product_photo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, status', 'required'),
			array('image_id, product_id, product_id, type, status, price, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>50),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, image_id, type, status, price, name, create_time, update_time', 'safe', 'on'=>'search'),
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
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'tabs' => array(self::MANY_MANY, 'IndexProductTab', 'index_product_photo_tab(photo_id, tab_id)', 'index' => 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'image_id'    => 'Изображение',
			'product_id'  => 'ID товара',
			'type'        => 'Тип',
			'status'      => 'Статус',
			'price'       => 'Цена',
			'name'        => 'Название',
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
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
		$criteria->compare('price',$this->price);
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
					'class'   => 'item_status label ' . $cls,
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
	 * Возвращает путь по которому будут сохраняться изображения для промоблока
	 * @return string
	 */
	public static function getImagePath()
	{
		return '/uploads/public/indexpage/promo/product';
	}

	public function getImageFullPath()
	{
		$image = $this->image;
		return self::getImagePath() . '/' . $image->name . '.' . $image->ext;
	}


	/**
	 * Сохраняет список указанных вкладок для текущей фотографии.
	 * Данные по вкладкам берутся из $_POST['IndexProductPhoto']['tabIds']
	 */
	public function saveTabs()
	{

		$tabIds = isset($_POST['IndexProductPhoto']['tabIds'])
		          ? $_POST['IndexProductPhoto']['tabIds']
			  : array();

		if ( ! empty($tabIds)) {

			Yii::app()->db
				->createCommand('DELETE FROM index_product_photo_tab WHERE photo_id = :phid')
				->bindValue(':phid', $this->id)
				->execute();


			foreach ($tabIds as $tid) {
				Yii::app()->db
					->createCommand("INSERT INTO index_product_photo_tab (`photo_id`, `tab_id`) VALUES ('".$this->id."', '".intval($tid)."')")
					->execute();
			}
		}
	}


	/**
	 * Возвращает список больших фотографий для вкладки $tabId.
	 *
	 * @param $tab_id Идентификатор вкладки
	 *
	 * @return array Массив моделей IndexProductPhoto
	 */
	public static function getBigPhotos($tab_id, $no_cache = false)
	{
		$tabId = (int)$tab_id;

		if (!IndexProductTab::model()->exists(
			'`id` = :id',
			array(':id' => $tabId)
		)) {
			throw new CHttpException(404);
		}

		$result = Yii::app()->cache->get(self::getCacheKeyBig($tabId));

		if ($no_cache === true) {
			$result = false;
		}

		if ($result === false) {

			$result = array();

			$models = IndexProductPhoto::model()->findAll(array(
				'join'      => 'INNER JOIN index_product_photo_tab ipt'
					. ' ON ipt.photo_id = t.id',
				'condition' => 'status = :st AND type = :tp AND ipt.tab_id = :tab',
				'params'    => array(
					':st' => IndexProductPhoto::STATUS_ACTIVE,
					':tp' => IndexProductPhoto::TYPE_PROMO,
					':tab'=> $tabId
				),
			));

			if ($models) {

				shuffle($models);

				$result = $models;
			}


			Yii::app()->cache->set(self::getCacheKeyBig($tabId), $result, Cache::DURATION_MAIN_PAGE);
		}

		return $result;
	}


	/**
	 * Возвращает список маленьких фотографий для вкладки $tabId.
	 *
	 * @param $tab_id Идентификатор вкладки
	 *
	 * @return array Массив моделей IndexProductPhoto
	 */
	public static function getSmallPhotos($tab_id, $no_cache = false)
	{
		$tabId = (int)$tab_id;

		if (!IndexProductTab::model()->exists(
			'id = :id',
			array(':id' => $tabId)
		)) {
			throw new CHttpException(404);
		}

		$result = Yii::app()->cache->get(self::getCacheKeySmall($tabId));

		if ($no_cache === true) {
			$result = false;
		}

		if ($result === false) {

			$result = array();

			$models = IndexProductPhoto::model()->findAll(array(
				'join'      => 'INNER JOIN index_product_photo_tab ipt'
					. ' ON ipt.photo_id = t.id',
				'condition' => 'status = :st AND type = :tp AND ipt.tab_id = :tab',
				'params'    => array(
					':st' => IndexProductPhoto::STATUS_ACTIVE,
					':tp' => IndexProductPhoto::TYPE_PREVIEW,
					':tab'=> $tabId
				),
			));

			if ($models) {
				shuffle($models);

				$result = array_slice($models, 0, 2);
			}


			Yii::app()->cache->set(self::getCacheKeySmall($tabId), $result, Cache::DURATION_MAIN_PAGE);
		}


		return $result;
	}


	/**
	 * Возвращает ключ для кеша, в котором хранятся большие фотографии
	 * для указанной вкладки $tabId
	 *
	 * @param $tabId Идентификатор вкладки
	 *
	 * @return string Ключ кеша для хранения данных.
	 * @throws CHttpException Вызывает ошибку 400, если $tabId <= 0
	 */
	private static function getCacheKeyBig($tabId)
	{
		if (intval($tabId) <= 0) {
			throw new CHttpException(
				400,
				'Параметр $tabId должен быть положительным числом'
			);
		}

		return 'INDEX:PROMO_PRODUCT:BIG_PHOTO:' . $tabId;
	}


	/**
	 * Возвращает ключ для кеша, в котором хранятся малые фотографии
	 * для указанной вкладки $tabId
	 *
	 * @param $tabId Идентификатор вкладки.
	 *
	 * @return string Ключ кеша для хранения данных.
	 * @throws CHttpException Вызывает ошибку 400, если $tabId <= 0
	 */
	private static function getCacheKeySmall($tabId)
	{
		if (intval($tabId) <= 0) {
			throw new CHttpException(
				400,
				'Параметр $tabId должен быть положительным числом'
			);
		}

		return 'INDEX:PROMO_PRODUCT:SMALL_PHOTO:' . $tabId;
	}

}