<?php

/**
 * This is the model class for table "cat_product".
 *
 * The followings are the available columns in table 'cat_product':
 * @property integer $id
 * @property integer $category_id
 * @property integer $status
 * @property integer $vendor_id
 * @property integer $collection_id
 * @property integer $user_id
 * @property integer $image_id
 * @property string $barcode
 * @property string $name
 * @property string $tags
 * @property string $desc
 * @property integer $country
 * @property string $guaranty
 * @property integer $eco
 * @property string $related_product
 * @property string $usageplace
 * @property integer $average_rating
 * @property float $average_price
 * @property string $admin_comment
 * @property integer $count_comment
 * @property integer $create_time
 * @property integer $update_time
 */
class Product extends Catalog2ActiveRecord implements IUploadImage, IComment
{

	const STATUS_IN_PROGRESS = 0; // товар в процессе добавления
	const STATUS_DELETED = 1; // товар удален
	const STATUS_ACTIVE = 2; // товар активен (отображается в каталоге)
	const STATUS_INACTIVE = 3; // товар не активен (возможно временно)
	const STATUS_APPROVAL = 4; // на подтвержнеии старшим модератором или админом myhome
	const STATUS_MODERATE = 5; // товар, добавленный администратором или модератором магазина, на модерации
	const STATUS_REJECTED = 6; // товар, добавленный администратором или модератором магазина, отклонен
	const STATUS_TEMPORARY = 7; // временный товар (наприме, во время импорта всем товарам, ранее импортированным из xml выставляется данный статус)
    static $statuses = array(
		self::STATUS_IN_PROGRESS => 'В процессе',
		self::STATUS_MODERATE => 'На модерации',
		self::STATUS_REJECTED => 'Отклонен',
		self::STATUS_DELETED => 'Удален',
		self::STATUS_APPROVAL => 'Согласование',
		self::STATUS_ACTIVE => 'Активен',
		self::STATUS_INACTIVE => 'Не активен',
	);

	private $_options; // опции товара
	private $_values;
	private $_category;
	private $_cover; // обложка
	private $_vendor;
	private $_country;
	private $_collection;
	private $_checkFeedback = null;
	private $_previousStatus;
	private $_storesIds = null;

	private $_usagespace;
	private $_updateUsageplace=false;

	/**
	 * Цена товара. Используется в случае выборки товаров для определенного
	 * магазина (product left join cat_store_price)
	 * @var float|null
	 */
	public $price;
	/**
	 * Скидка товара. Испольузется в случае выборки товаров для определенного
	 * магазина (product left join cat_store_price)
	 * @var int|null
	 */
	public $discount;

	/**
	 * Тип цены (от, равно). Используется в случае выборки товаров для определенного магазина (product left join cat_store_price)
	 * @var int
	 */
	public $price_type;

	/**
	 * URL для товара от интерент магазина
	 * @var int
	 */
	public $url;

	const DEFAULT_PAGESIZE = 60;

	public static $preview = array(
		'crop_60' => array(60, 60, 'crop', 80),
		'crop_90' => array(90, 90, 'crop', 80),
		'crop_120' => array(120, 120, 'crop', 80),
		'crop_140' => array(140, 140, 'crop', 80),
		'crop_150' => array(150, 150, 'crop', 80),
		'crop_160' => array(160, 160, 'crop', 80), // Юзается на главной странице в блоке "Товары"
		'crop_200' => array(200, 200, 'crop', 80),
		'crop_292' => array(292, 292, 'crop', 80),

		'crop_220' => array(220, 220, 'crop', 90), // просмотр в списке
		'crop_338' => array(338, 338, 'crop', 90), // просмотр в списке
		'crop_300' => array(300, 300, 'crop', 90), // просмотр в папке

		'crop_380' => array(380, 346, 'crop', 80),
		'crop_416x344' => array(416, 344, 'crop', 80),
		'crop_220x175' => array(220, 175, 'crop', 80),
		'resize_510' => array(510, 510, 'resize', 80, 'decrease' => true),
		'resize_380' => array(380, 346, 'resize', 80, 'border'=>true),
		'resize_200' => array(200, 200, 'resize', 80, 'border'=>true),
		'resize_220' => array(220, 220, 'resize', 90, 'border'=>true),
		'resize_230' => array(230, 230, 'resize', 80, 'border'=>true),
		'resize_338' => array(338, 338, 'resize', 90, 'border'=>true),
		'resize_120' => array(120, 120, 'resize', 80, 'border'=>true),
		'resize_160' => array(160, 160, 'resize', 80, 'border'=>true),
		'resize_60' => array(60, 60, 'resize', 80, 'border'=>true),
		'resize_960' => array(960, 1080, 'resize', 90, 'decrease' => true),

		//'resize_210' => array(210, 210, 'resize', 80), // Используется только для управления интересными предложениями (главная товаров)
		'resize_420' => array(420, 420, 'resize', 80), // Используется только для управления интересными предложениями (главная товаров)
	);

	/** @var integer для поиска по контрагентам */
	public $contractor = null;

	// Тип изображения для загрузки
	private $_imageType = null;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Product the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter))
			return $this->$getter();
		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter))
			return $this->$setter($value);
		return parent::__set($name, $value);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cat_product';
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, 'initValues');
		$this->onAfterSave = array($this, 'updateProductQt');
		$this->onAfterSave = array($this, 'updateMaxPrice');
		$this->onAfterSave = array($this, 'saveUsagePlace');
		$this->onAfterDelete = array($this, 'deleteValues');
	}

	public function saveUsagePlace()
	{
		if (!$this->_updateUsageplace)
			return;

		$pid = $this->id;
		$sql = 'DELETE FROM cat_product_room WHERE product_id=:pid';
		Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':pid', $pid)->execute();

		$sql = 'insert into cat_product_room (`room_id`, `product_id`) VALUES ';
		$values = array();
		foreach ($this->_usagespace as $item) {
			$values[] = '('.$item.','.$pid.')';
		}

		if (!empty($values)) {
			$sql .= implode(',', $values);
			Yii::app()->dbcatalog2->createCommand($sql)->execute();
		}
	}

	/**
	 * Обновление / удаление записи в индексе
	 */
	public function updateSphinx()
	{
		if ($this->status == self::STATUS_ACTIVE) {
			Yii::app()->gearman->appendJob('sphinx:product2',
				array('product_id' => $this->id, 'action' => 'update')
			);
		} else {
			Yii::app()->gearman->appendJob('sphinx:product2',
				array('product_id' => $this->id, 'action' => 'delete')
			);
		}
	}

	/**
	 * Удаляет значения всех опций текущего товара
	 * @return bool
	 */
	public function deleteValues()
	{
		$command = Yii::app()->dbcatalog2->createCommand();
		$command->delete('cat_value', 'product_id=:pid', array(':pid' => $this->id));
		return true;
	}

	/**
	 * Инициализация значений опций для создаваемого товара
	 * @return bool
	 */
	public function initValues()
	{
		if ($this->getScenario() <> 'init' || !$this->isNewRecord)
			return false;

		$command = Yii::app()->dbcatalog2->createCommand();
		foreach ($this->options as $option) {
			$command->insert('cat_value', array(
				'option_id' => $option->id,
				'product_id' => $this->id,
				'category_id' => $this->category_id,
			));
		}
		return true;
	}

	/**
	 * Подсчет и обновление кол-ва активных товаров в категории текущего товара
	 */
	public function updateProductQt()
	{
		/**
		 * Если статус у товара изменился на "Активен" или с "Активен" на любой другой, то пересчитываем кол-во активных товаров
		 */
		if (($this->status == self::STATUS_ACTIVE && $this->_previousStatus <> self::STATUS_ACTIVE) || ($this->_previousStatus == self::STATUS_ACTIVE && $this->status <> self::STATUS_ACTIVE)) {
			$product_qt = Product::model()->count('category_id=:id and status=:stat and vendor_id is not null', array(':id' => $this->category_id, ':stat' => self::STATUS_ACTIVE));
			$this->category->product_qt = $product_qt;
			$this->category->saveNode(false, array('product_qt'));
		}
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, user_id, status', 'required', 'on' => 'init'),
			array('status, name, vendor_id, country, image_id', 'required', 'on' => 'update'),
			array('category_id, status, vendor_id, collection_id, user_id, country, image_id, eco, create_time, average_rating, count_comment, update_time', 'numerical', 'integerOnly' => true),
			array('price, store_id', 'numerical'),
			array('barcode, name, guaranty, store_inner_id', 'length', 'max' => 255),
			array('usageplace', 'usageplaceValidator'),
			array('related_product', 'relatedProductValidator'),
			array('desc, tags', 'length', 'max' => 3000),
			array('admin_comment', 'length', 'max' => 1000),
			array('vendor_id', 'exist', 'className' => 'Vendor', 'attributeName' => 'id'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, contractor, store_id, store_inner_id, category_id, status, vendor_id, collection_id, country, guaranty, related_product, usageplace, eco, user_id, image_id, price, barcode, name, tags, desc, admin_comment, create_time, update_time', 'safe', 'on' => 'search'),
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
			'category' => array(self::BELONGS_TO, 'Category', 'category_id'),
			'vendor' => array(self::BELONGS_TO, 'Vendor', 'vendor_id')
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
			'vendor_id' => 'Производитель',
			'collection_id' => 'Коллекция',
			'user_id' => 'Пользователь',
			'image_id' => 'Обложка',
			'price' => 'Реком-ая цена',
			'barcode' => 'Артикул',
			'name' => 'Название',
			'country' => 'Страна',
			'related_product' => 'Сопутствующее',
			'usageplace' => 'Использование',
			'guaranty' => 'Гарантия',
			'eco' => 'Экологичность',
			'tags' => 'Теги',
			'images' => 'Изображения',
			'status' => 'Статус',
			'average_price' => 'Средняя цена',
			'desc' => 'Описание',
			'admin_comment' => 'Комментарий администратора',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'contractor' => 'Контрагент',
		);
	}

	/**
	 * Поиск по товарам
	 * @param integer|null $date_from
	 * @param integer|null $date_to
	 * @param string|null $bind_store
	 * 	Тип связки товаров с магазинами (реальная связка через цены
	 * 	или фейковая через производителя)
	 * @return CActiveDataProvider
	 */
	public function search($date_from = null, $date_to = null, $bind_store = null)
	{
		$criteria = new CDbCriteria;

		$criteria->select = 't.*';
		if ($this->category_id) {
			$category = Category::model()->findByPk($this->category_id);
			if ($category) {
				// поиск всех выбранной данной категории
				$ids = Yii::app()->dbcatalog2->createCommand()
					->select('id')->from('cat_category')
					->where('(lft>:lft and rgt<:rgt) or id=:id', array(':id' => $this->category_id, ':lft' => $category->lft, ':rgt' => $category->rgt))
					->order('lft')->queryColumn();

				$criteria->compare('category_id', $ids, true);
			}
		}

		$criteria->compare('vendor_id', $this->vendor_id);
		$criteria->compare('user_id', $this->user_id);

		if ((int) $this->status != -1) {
			$criteria->compare('t.status', $this->status);
		} else {
			$criteria->compare('t.status', array(Product::STATUS_MODERATE, Product::STATUS_ACTIVE, Product::STATUS_APPROVAL, Product::STATUS_IN_PROGRESS, Product::STATUS_INACTIVE, Product::STATUS_REJECTED));
		}

		$criteria->compare('barcode', $this->barcode, true);
		$criteria->compare('name', $this->name, true);

		if (!empty($this->contractor))	{
			$criteria->join = 'INNER JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=t.vendor_id ';
			$criteria->compare('cvc.contractor_id', $this->contractor);
		}

		if ($this->id) {
			$criteria->compare('t.id', explode(',', $this->id), true);
		}
		if ($date_from) {
			$criteria->compare('t.create_time', '>=' . (strtotime($date_from)));
		}
		if ($date_to) {
			$criteria->compare('t.create_time', '<=' . (strtotime($date_to) + 86400));
		}

		// Фильтрация по типу связанности товаров с магазинами
		if ($bind_store) {
			$criteria->join .= ' INNER JOIN cat_store_price sp ON sp.product_id = t.id';

			if ($bind_store == 'fake') {
				// Выборка только тех товаров, которые связаны
				// фейковыми связями
				$criteria->group = 'sp.product_id';
				$criteria->having = 'MIN(sp.by_vendor) = 1';

			} elseif ($bind_store == 'real') {

				// Выборка товаров, у которых есть реальные
				// связи через магазины
				$criteria->distinct = true;
				$criteria->addCondition('sp.by_vendor = 0');
			}

		}

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 20,
			),
		));
	}

	/**
	 * Возвращает список опций для текущего товара
	 * @return mixed
	 */
	public function getOptions()
	{
		if (!$this->_options)
			$this->_options = Option::model()->findAllByAttributes(array('category_id' => $this->category_id));

		return $this->_options;
	}

	/**
	 * Возвращает объект категории для текущего продукта
	 * @return mixed
	 */
	public function getCategory()
	{
		if (!$this->_category)
			$this->_category = Category::model()->findByPk($this->category_id);

		return $this->_category;
	}

	/**
	 * Список значений опций для данного товара
	 * Группировка по опции для multiple-value опций
	 * @return array Value
	 */
	public function getValues()
	{
		if (!$this->_values) {
			$criteria = new CDbCriteria();
			$criteria->compare('product_id', $this->id);
			$criteria->join = 'left join cat_option o on o.id=t.option_id';
			$criteria->order = 'o.position asc, o.id asc';
			$criteria->group = 'option_id';
			$this->_values = Value::model()->findAll($criteria);
		}

		return $this->_values;
	}

	/**
	 * Объект UploadedFile - обложка товара
	 * @return CActiveRecord
	 */
	public function getCover()
	{
		if ( $this->_cover===null ) {
			$this->_cover = UploadedFile::model()->findByPk((int) $this->image_id);
			if ($this->_cover===null)
				$this->_cover = new UploadedFile();
		}
		return $this->_cover;
	}

	/**
	 * Валидатор выбранных мест для использования товара
	 * @param $attribute
	 * @param $value
	 */
	public function usageplaceValidator($attribute, $params)
	{
		if (!$this->usageplace)
			return true;

		if (!is_array($this->usageplace))
			return $this->addError($attribute, 'Неверный формат');

		$rooms = MainRoom::getAllRooms();

		foreach ($this->usageplace as $val) {
			if (!isset($rooms[$val]))
				return $this->addError($attribute, "Значение {$val} не существует");
		}
		// убирает флаг обновления, если не прошел валидацию
		if ($this->hasErrors($attribute)) {
			$this->_updateUsageplace = false;
		}
	}

	/**
	 * Валидатор списка сопутствующих товаров
	 * @param $attribute
	 * @param $params
	 */
	public function relatedProductValidator($attribute, $params)
	{
		if (!$this->related_product)
			return true;

		if (!is_array($this->related_product_array))
			return $this->addError($attribute, 'Неверный формат');

		foreach ($this->related_product_array as $prod) {
			if (!Product::model()->exists('id=:id', array(':id' => (int)$prod)))
				return $this->addError($attribute, "Товар {$prod} не существует");
		}
	}

	/**
	 * Подготавливает к сохранению массив мест использования товара для сохранения
	 * @param $value
	 */
	public function setUsageplace($value)
	{
		$this->_usagespace = $value;
		$this->_updateUsageplace = true;
	}


	/**
	 * Возвращает массив мест использования товара
	 * @return mixed
	 */
	public function getUsageplace()
	{
		if ($this->_usagespace !== null )
			return $this->_usagespace;

		$sql = 'SELECT room_id FROM cat_product_room WHERE product_id=:pid';
		$pid = $this->id;
		$this->_usagespace = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':pid', $pid)->queryColumn();

		return $this->_usagespace;
	}

	/**
	 * Преобразует строку сопутствующих товаров в сериализованный массив (для сохранения)
	 * @param $value
	 */
	public function setRelated_product($value)
	{
		preg_match_all("/\d+/", $value, $matches);
		if (count($matches) > 0)
			return parent::__set('related_product', serialize($matches[0]));
		else
			return parent::__set('related_product', serialize(array()));
	}

	/**
	 * Возвращаетс строку с сопутствующими товарами (для отображения)
	 * @return null|string
	 */
	public function getRelated_product()
	{
		$products = unserialize(parent::__get('related_product'));

		if (!is_array($products))
			return null;

		return implode(', ', $products);
	}

	/**
	 * Возвращает массив сопутствующих товаров (для валидатора)
	 * @return mixed
	 */
	public function getRelated_product_array()
	{
		return unserialize(parent::__get('related_product'));
	}

	/**
	 * Возвращает изображения текущего товара
	 * @param bool $AR если true - вернет массив объектов UploadedFile
	 * @return array
	 */
	public function getImages($AR = false, $withCover = false)
	{
		$images = Yii::app()->dbcatalog2->createCommand()
			->select('file_id')
			->from('cat_product_image')
			->where('product_id=:pid', array(':pid' => $this->id))
			->queryColumn();

		if ($AR) {
			if($withCover)
				$images[] = $this->image_id;

			$query = implode(',', $images);
			if (empty($query)) return array();
			return UploadedFile::model()->findAll('id in (' . $query . ')');
		} else
			return $images;
	}

	public function getVendor()
	{
		if (!$this->_vendor)
			$this->_vendor = Vendor::model()->findByPk($this->vendor_id);

		return $this->_vendor;
	}

	/**
	 * Возвращает аналогичные товары для текущего
	 * @params $AR Флаг обозначающий тип возвращемых данных. Если $AR = true, возвращается массив ActiveRecord'ов
	 * @return array
	 */
	public function getSimilar($AR = false, $limit = 4)
	{
		$products = Yii::app()->dbcatalog2->createCommand()
			->select('similar_product_id as id')
			->from('cat_similar_product')
			->where('product_id=:pid', array(':pid' => $this->id))
			->limit(intval($limit))
			->queryAll();

		if ($AR) {
			$products_array = array();
			foreach ($products as $prod)
				$products_array[] = $prod['id'];
			$query = implode(',', $products_array);
			if (!$query) return array();
			return Product::model()->findAll('t.id in (' . $query . ')');
		} else
			return $products;

	}

	// IUploadImage
	public function getImagePath()
	{
		if ($this->getIsNewRecord())
			return false;
		switch ($this->_imageType) {
			case 'product':
				return 'catalog2' . '/' . intval($this->user_id / UploadedFile::PATH_SIZE + 1) . '/' . $this->user_id . '/product/' . $this->id;
			default:
				throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ($this->getIsNewRecord())
			return false;
		switch ($this->_imageType) {
			case 'product':
				return 'product' . $this->id . '_' . mt_rand(1, 99) . '_' . time();
			default:
				throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->user_id;
	}

	public function checkAccess()
	{
		return !is_null($this->user_id) && $this->user_id == Yii::app()->user->id;
	}

	public function setImageType($name)
	{
		$this->_imageType = $name;
	}

	public function flushImageType()
	{
		$this->_imageType = null;
		return true;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'product':
				return array(
					'realtime' => array(
						self::$preview['crop_150'],
					),
					'background' => array(
						self::$preview['crop_60'],
						self::$preview['crop_120'],
						self::$preview['crop_150'],
						self::$preview['crop_200'],
						self::$preview['crop_380'],
						self::$preview['crop_416x344'],
						self::$preview['resize_380'],
						self::$preview['resize_200'],
						self::$preview['resize_230'],
						self::$preview['resize_120'],
						self::$preview['resize_160'],
						self::$preview['resize_60'],
						self::$preview['resize_960'],
						self::$preview['resize_510'],
					),
				);
			default:
				throw new CException('Invalid upload image type');
		}
	}

	/**
	 * Получение страны изготовителя
	 * @return mixed
	 */
	public function getCountryObj()
	{
		if (!$this->_country && $this->country)
			$this->_country = Country::model()->findByPk($this->country);

		return $this->_country;
	}

	/**
	 * Возвращает название коллекции производителя, к которой относится товар
	 * @return mixed
	 */
	public function getCollectionName()
	{
		if (!$this->_collection && isset($this->vendor)) {
			$vendor_collections = $this->vendor->getCollectionsArray();
			foreach ($vendor_collections as $coll) {
				if ($coll['id'] == $this->collection_id) {
					$this->_collection = $coll['name'];
					break;
				}
			}
		}

		return $this->_collection;
	}

	/**
	 * Проверка, является ли текущий пользователь администратором магазина, который
	 * продает текущий товар
	 * @return bool|mixed
	 */
	public function getIsSeller()
	{
		$stores_ids = $this->getStoresIds();

		if (!$stores_ids)
			return false;

		return Yii::app()->dbcatalog2->createCommand()
			->select('id')
			->from('cat_store')
			->where('id in (' . implode(',', $stores_ids) . ') and admin_id=:aid', array(
			':aid'=> Yii::app()->user->id
		))->limit(1)->queryScalar();
	}

	/**
	 * Возвращает опции для сокращенной карточки товара с их типом и значениями
	 * @return array
	 */
	public function getMinicardOptions()
	{
		/**
		 * Получение всех опций текущего товара, отмеченных для отображения на сокращенной карточке
		 */
		$options = Yii::app()->dbcatalog2->createCommand()->select('id, name, type_id, param')->from('cat_option')
			->where('minicard=1 and category_id=:cid', array(':cid' => $this->category_id))
			->order('position')->queryAll();

		$option_value = array();
		$options_array = array();

		foreach ($options as $option) {
			/**
			 * Формирование массива опций
			 */
			$option_value[$option['id']] = array('name' => $option['name'], 'type_id' => $option['type_id'], 'param' => Value::serializeToArrray($option['param']));
			/**
			 * Формирование массива id опций для составления запроса формата in (id,id,id)
			 */
			$options_array[] = $option['id'];
		}

		$query = implode(',', $options_array);

		if(!$query)
			return array();

		/**
		 * Получение значений для выбранных ранее опций
		 */
		$values = Yii::app()->dbcatalog2->createCommand()->select('id, option_id, value')->from('cat_value')
			->where('product_id=:pid and option_id in (' . $query . ')', array(':pid' => $this->id))
			->order('position')->queryAll();

		/**
		 * Добавление значений опций в массив опций
		 */
		foreach ($values as $value) {
			$option_value[$value['option_id']]['value'] = $value['value'];
		}

		/**
		 * Возвращение массива опций и значений для сокращенной карточки товара
		 */
		return $option_value;
	}

	/**
	 * Возвращает опции товара с их типом и значениями
	 * @return array
	 */
	public function getFullcardOptions()
	{
		/**
		 * Получение всех опций текущего товара
		 */
		$options = Yii::app()->dbcatalog2->createCommand()->select('id, name, group_id, type_id, param')->from('cat_option')
			->where('category_id=:cid', array(':cid' => $this->category_id))
			->order('position')->order('group_id')->queryAll();

		$option_value = array();
		$options_array = array();

		foreach ($options as $option) {
			/**
			 * Формирование массива опций
			 */
			$option_value[$option['id']] = array('name' => $option['name'], 'type_id' => $option['type_id'], 'group_id' => $option['group_id'], 'param' => Value::serializeToArrray($option['param']));
			/**
			 * Формирование массива id опций для составления запроса формата in (id,id,id)
			 */
			$options_array[] = $option['id'];
		}

		$query = implode(',', $options_array);

		if(!$query)
			return array();

		/**
		 * Получение значений для выбранных ранее опций
		 */
		$values = Yii::app()->dbcatalog2->createCommand()->select('id, option_id, value')->from('cat_value')
			->where('product_id=:pid and option_id in (' . $query . ')', array(':pid' => $this->id))
			->order('position')->queryAll();

		/**
		 * Добавление значений опций в массив опций
		 */
		foreach ($values as $value) {
			$option_value[$value['option_id']]['value'] = $value['value'];
		}

		/**
		 * Возвращение массива опций и значений для сокращенной карточки товара
		 */
		return $option_value;
	}

	/**
	 * Возвращает ссылку на страницу просмотра товара.
	 * Если указан store_id, то ссылка будет вести на карточку товара в конкретном магазине
	 * @static
	 * @param $id
	 * @param $store_id
	 * @return string
	 */
	static public function getLink($id, $store_id=null, $category_id=null)
	{
		$params = array('id' => $id, 'action' => 'index');
		if ( $store_id !== null )
			$params['store_id'] = $store_id;
		if ( $category_id !== null )
			$params['category_id'] = $category_id;

		return Yii::app()->createUrl('/product2', $params);
	}

	/**
	 * IComment method
	 * @return boolean
	 */
	public function getCommentsVisibility()
	{
		return $this->status == self::STATUS_ACTIVE;
	}

	/**
	 * IComment method
	 */
	public function afterComment($comment)
	{
		/**
		 * Обновление рейтинга и кол-ва комментариев текущего объекта
		 */
		$count_comment = Comment::getCountComments($this);
		$average_rating = Voting::getAverageRating($this);
		self::model()->updateByPk($this->id, array(
			'average_rating' => $average_rating,
			'count_comment' => $count_comment,
		));

		return array($average_rating, $count_comment);
	}

	/**
	 * Товары пока не имеют владельцев, кроме аминистраторов системы
	 * @return bool
	 */
	public function getIsOwner()
	{
		return false;
	}

	public function getAuthor_id()
	{
		return $this->user_id;
	}

	/**
	 * Проверяет наличие фидбека у текущего пользователя для текущего товара
	 * @return bool
	 */
	public function getCheckFeedback()
	{
		if (is_null($this->_checkFeedback))
			$this->_checkFeedback = Feedback::model()->exists('product_id=:pid and user_id=:uid', array(':pid' => $this->id, ':uid' => Yii::app()->user->id));
		return $this->_checkFeedback;
	}

	/**
	 * Сеттер для статуса (сохраняет предыдущее знаечение в случае его изменения)
	 * @param $value
	 * @return mixed
	 */
	public function setStatus($value)
	{
		if ($this->status != $value)
			$this->_previousStatus = $this->status;
		return parent::__set('status', $value);
	}

	/**
	 * Возвращает кол-во отзывов товара
	 * @return mixed
	 */
	public function getCount_feedback()
	{
		return Yii::app()->dbcatalog2->createCommand()->select('count(id)')->from(Feedback::model()->tableName())
			->where('product_id=:pid and parent_id is null', array(':pid' => $this->id))->queryScalar();
	}

	/**
	 * Обновляет максимальную цену для категории, к которой относится сохраненный товар
	 */
	public function updateMaxPrice()
	{
		Category::setMaxPrice($this->category_id);
	}

	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink()
	{
		return self::getLink($this->id);
	}

	/**
	 * Вычисляет общее количество активных товаров в каталоге.
	 *
	 * @param bool $use_cache Флаг использования кеша
	 * @param bool $round Флаг, при выставлении которого, количество товаров
	 *                    округляется до 1000. Например 35902 => 35000
	 * @param bool $format Флаг, при выставлении, которого значение
	 *                     форматируется в виде «44 000»
	 *
	 * @return int Кол-во товаров в каталоге
	 */
	static public function countAll($use_cache = true, $round = false, $format = false)
	{
		$qnt = Yii::app()->cache->get('countTotalProducts');

		if ( ! $use_cache)
			$qnt = null;

		if ( ! $qnt) {
			$qnt = (int)Product::model()->countByAttributes(array('status' => Product::STATUS_ACTIVE));

			Yii::app()->cache->set('countTotalProducts', $qnt, Cache::DURATION_MAIN_PAGE);
		}

		// Округляем до тысяч в меньшую сторону
		if ($round === true) {
			$qnt = intval($qnt / 1000) * 1000;
		}

		// Форматируем вывод
		if ($format) {
			$qnt = number_format($qnt, 0, '.', ' ');
		}

		return $qnt;
	}

	/**
	 * Вычисляет количество товаров, принадлежащих конкретному Торговому Центру.
	 *
	 * @param $mall
	 * @return int
	 */
	static public function countAllMall($mall)
	{
		$sql = 'SELECT @count FROM {{product2}} WHERE mall_ids = '.$mall->id.' GROUP BY mall_ids';
		$qnt = Yii::app()->sphinx->createCommand($sql)->queryScalar();

		return (int)$qnt;
	}


	/**
	 * Генератор случайных товаров по индекусу sphinx'а
	 * Получаем набор категорий в которых есть товары.
	 * Затем для каждой из них берем случайный товар.
	 *
	 * @param int $size
	 * @return array
	 */
	static public function getRandomProducts($size = 6)
	{
		$result = array();

		// Получаем список всех доспуных категорий и маскимально доступный ID товара в каждой из них.
		$sql = "SELECT MAX(id) as max_id, category_id FROM {{product2}} WHERE price >= 0.001 GROUP BY category_id LIMIT 100";
		$cats = Yii::app()->sphinx->createCommand($sql)->queryAll();

		shuffle($cats);

		foreach($cats as $cat)
		{
			// Получаем случайный товар
			$rnd = rand(0, $cat['max_id'] - 1);
			$sql = 'SELECT id FROM {{product2}} WHERE price >= 0.001 AND category_id = '.$cat['category_id'].' AND id > '.$rnd.' LIMIT 1';
			$p = Yii::app()->sphinx->createCommand($sql)->queryScalar();

			$model = Product::model()->findByPk((int)$p);
			if ($model) {
				$result[] = $model;

				if (--$size <= 0)
					break;
			}
		}

		return $result;
	}


	/**
	 * Генератор самых случайных товаров.
	 * Считает сколько товаров попадает под наши условия (total_qnt).
	 * А затем делает выборку из $qnt штук, беря товары
	 * по случайному смещению в запросе в интервале [0; <total_qnt>]
	 *
	 * @param int $size
	 * @return array
	 */
	static public function getRandomProductsMall($size = 6)
	{
		$result = array();

		// ПОлучаем Торговый Центр
		if ( ! ($mall = Cache::getInstance()->mallBuild))
			return null;

		// Получаем список всех доспуных категорий и маскимально доступный ID товара в каждой из них.
		$sql = 'SELECT MAX(id) as max_id, category_id FROM {{product2}} WHERE price >= 0.001 AND mall_ids = '.$mall->id.' GROUP BY category_id LIMIT 100';
		$cats = Yii::app()->sphinx->createCommand($sql)->queryAll();

		shuffle($cats);

		foreach($cats as $cat)
		{
			// Получаем случайный товар
			$rnd = rand(0, $cat['max_id'] - 1);
			$sql = 'SELECT id FROM {{product2}} WHERE price >= 0.001 AND category_id = '.$cat['category_id'].' AND id > '.$rnd.' AND mall_ids = '.$mall->id.' LIMIT 1';
			$p = Yii::app()->sphinx->createCommand($sql)->queryScalar();

			$model = Product::model()->findByPk((int)$p);
			if ($model) {
				$result[] = $model;

				if (--$size <= 0)
					break;
			}
		}

		return $result;
	}

	public function getOrderedValues()
	{
		$criteria = new CDbCriteria();
		$criteria->compare('product_id', $this->id);
		$criteria->group = 't.option_id';
		$criteria->join = 'inner join cat_option o on o.id = t.option_id and o.hide <> 1';
		$criteria->order = 'o.position asc, o.group_id asc';
		$this->_values = Value::model()->findAll($criteria);

		return $this->_values;
	}

	/**
	 * Физическое удаление товара из базы (со всеми зависимостями)
	 */
	public function hardDelete()
	{
		$connection = Yii::app()->dbcatalog2;
		$connection->createCommand()->delete('cat_value', 'product_id=:pid', array(':pid'=>$this->id));
		$connection->createCommand()->delete('cat_group_operation', 'product_id=:pid', array(':pid'=>$this->id));
		$connection->createCommand()->delete('cat_feedback', 'product_id=:pid', array(':pid'=>$this->id));
		$connection->createCommand()->delete('cat_product_image', 'product_id=:pid', array(':pid'=>$this->id));
		$connection->createCommand()->delete('cat_similar_product', 'product_id=:pid or similar_product_id=:pid', array(':pid'=>$this->id));
		$connection->createCommand()->delete('cat_store_price', 'product_id=:pid', array(':pid'=>$this->id));
		$this->delete();
		return null;
	}

	/**
	 * Возвращает список магазинов, в которых есть текущий товар
	 * @return array
	 */
	public function getStoresIds()
	{
		if($this->_storesIds === null)
			$this->_storesIds = Yii::app()->dbcatalog2->createCommand()->select('store_id')->from('cat_store_price')
				->where('product_id=:pid and by_vendor=0', array(':pid'=>$this->id))->group('store_id')->queryColumn();
		return $this->_storesIds;
	}


	/**
	 * Возвращает айдишники магазинов
	 * с учетом мола
	 * @param $id
	 *
	 * @return array
	 */
	public static function getStoresInMall($id)
	{
		if($id) {
			$sql = 'SELECT store_id FROM `cat_store_price` as csp INNER JOIN cat_store as cs ON csp.store_id = cs.id
					WHERE csp.product_id='.$id.' AND  cs.mall_build_id = 1 ORDER BY price DESC,discount DESC ';

			$result = Yii::app()->dbcatalog2->createCommand($sql)->queryColumn();
			return $result;
		}

		return array();
	}


	public static function countAveragePrice($pid)
	{
		$model = Product::model()->findByPk((int)$pid);

		if ( ! $model)
			throw new CHttpException(404);

		$avgPrice = Yii::app()->dbcatalog2->createCommand("SELECT AVG(price) as avg_price FROM cat_store_price WHERE product_id = :pid AND price > 0")->bindValue(':pid', $model->id)->queryScalar();

		if (Product::model()->updateByPk($model->id, array('average_price' => $avgPrice)))
			return $avgPrice;
		else
			return false;
	}

	/**
	 * Наращивает счетчик просмотра товара на единицу
	 */
	public function incrementView()
	{
		if ( ! $this->getIsNewRecord()) {
			// Наращиваем счетчик просмотра товара
			Yii::app()->redis->incr('Product:View:'.$this->id);
		}
	}

	/**
	 * Возвращает цену на товар в указанном магазине
	 * @param $store_id
	 * @return mixed|null
	 */
	public function getStorePrice($store_id)
	{
		$price = Yii::app()->dbcatalog2->createCommand()
			->select('price, status, price_type, discount')->from(StorePrice::model()->tableName())
			->where('store_id=:sid and product_id=:pid and by_vendor=0', array(':sid'=>(int) $store_id, ':pid'=>$this->id))->queryRow();
		if ($price)
			return $price;

		return null;
	}

	/**
	 * Получение количества товаров в указанной категории и городе (если не указано - во всех)
	 * @param Category $category
	 * @param integer $cityId
	 * NOTE: not use now
	 */
//	public static function getAllProducts($category = null, $cityId=null)
//	{
//		$cityId = intval($cityId);
//		$categoryId = ($category instanceof Category) ? $category->id : 0;
//		$key = 'Product::getAllProducts:'.$categoryId.':'.$cityId;
//
//		// Проверка в кеше
//		$count = Yii::app()->cache->get($key);
//		if ($count)
//			return $count;
//
//		// Получение списка конечных категорий
//		$categoryList = array();
//		if ( $category instanceof Category ) {
//			$sql = 'SELECT cc.id FROM cat_category as cc WHERE cc.rgt-cc.lft=1 AND cc.lft>=:lft AND cc.rgt<=:rgt';
//			$rgt = intval($category->rgt);
//			$lft = intval($category->lft);
//
//			$categoryList = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':lft', $lft)
//				->bindParam(':rgt', $rgt)->queryColumn();
//		}
//
//		$sql = 'SELECT COUNT(*) FROM {{product}} ';
//		// Фильтр по городу
//		if ( !empty($cityId) ) {
//			$storeList = StoreGeo::getOfflineStoreList($cityId);
//			if ( empty($storeList) ) { // отключение выбора
//				$sql .= 'WHERE id=0';
//			} else {
//				$sql .= 'WHERE store_ids IN ('.implode(',', $storeList).')';
//			}
//		}
//
//		if (!empty($categoryList)) {
//			if (empty($cityId)) {
//				$sql .= 'WHERE';
//			} else {
//				$sql .= ' AND ';
//			}
//			$sql .= ' category_id IN ('.implode(',', $categoryList).')';
//		}
//
//		$sql .= ' GROUP BY category_id LIMIT 150';
//		$data = Yii::app()->sphinx->createCommand($sql)->queryColumn();
//
//		// Подсчет результатов из sphinx
//		$count = 0;
//		foreach ($data as $item) {
//			$count += $item;
//		}
//
//		Yii::app()->cache->set($key, $count, Cache::DURATION_HOUR);
//
//		return $count;
//	}


	/**
	 * Получения списка похожих товаров в категории
	 * по заданному лимиту в случайном порядке
	 * @param int $categoryId
	 * @param int $storeId
	 * @param int $explodeIdProduct
	 * @param int $limit
	 *
	 * @return array|bool|CActiveRecord|mixed|null
	 */
	public static function getRelatedProducts($categoryId,$storeId,$explodeIdProduct,$limit=5)
	{
		$sql = "SELECT (id) FROM {{product2}} WHERE category_id=:catId AND store_ids=:storeId AND id!=:productId ORDER by rand() LIMIT :limit";

		$catId = (int)$categoryId;
		$storeId = (int)$storeId;
		$productId = (int)$explodeIdProduct;
		$limit = (int)$limit;

		$data = Yii::app()->sphinx->createCommand($sql)
			->bindParam(":catId", $catId)
			->bindParam(":storeId", $storeId)
			->bindParam(":productId", $productId)
			->bindParam(":limit", $limit)
			->queryColumn();

		$relatedItemsData = Product::model()->findAllByPk($data);
		($relatedItemsData) ? $result=$relatedItemsData : $result=false;

		return $result;

	}

	/**
	 * Получить товары
	 * с положительной ценой
	 * по заданому городу
	 * Если товары по городу не найдены
	 * То возвращаются товары
	 * без его учета
	 * Если задан массив $categoryIdArray
	 * то делается попытка найти товары
	 * в категиях заданных в массиве
	 * @param       $cityId
	 * @param       $limit
	 * @param array $categoryIdArray
	 *
	 * @return mixed
	 */
	public static function getProductWithPrice($cityId, $limit, $categoryIdArray = array())
	{
		Yii::import('catalog2.models.StoreGeo');
		$cityId = (int)$cityId;
		$limit = (int)$limit;
		$select = 'SELECT (id) FROM {{product2}} ';
		$order = 'ORDER BY Rand() ';
		$limitSql = 'LIMIT :limit ';
		$data = array();
		$storeList = StoreGeo::getStoreList($cityId);


		//Если передан массив адишников
		if ($categoryIdArray) {
			$data = array();

			if (!empty($storeList)) {
				$storeList = implode(',', $storeList);

				$categoryIdArray = implode(',', $categoryIdArray);

				//Делаем запрос с выбором только товаров которые продают
				//платные магазины в городе пользователя
				$where = 'WHERE store_ids IN ('.$storeList.') AND category_id IN (' . $categoryIdArray . ') AND price>=1.0 AND sort_default=3 ';
				//Формируем запрос в sphinx
				$sql = $select . $where . $order . $limitSql;

				$data = Yii::app()->sphinx->createCommand($sql)
					->bindParam(":limit", $limit)
					->queryColumn();

				//Делаем запрос с выбором в выбраном городе в выбранной каттегории
				if(count($data)<$limit)
				{
					$limitQ = $limit - count($data);
					$limitQ = (int)$limitQ;
					$where = 'WHERE store_ids IN ('.$storeList.') AND category_id IN (' . $categoryIdArray . ') AND price>=1.0 ';

					$sql = $select . $where . $order . $limitSql;

					$dataNotPaid = Yii::app()->sphinx->createCommand($sql)
						->bindParam(":limit", $limitQ)
						->queryColumn();

					$data = array_merge($data,$dataNotPaid);
				}
			}

			//Если получен пустой результат то ищем без учета города
			if (count($data)<$limit) {

				$dataNotPaid = array();
				$limitQ = $limit - count($data);
				$limitQ = (int)$limitQ;

				$where = 'WHERE category_id IN (' . $categoryIdArray . ') AND price>=1.0 ';

				$sql = $select . $where . $order . $limitSql;

				$dataNotPaid = Yii::app()->sphinx->createCommand($sql)
					->bindParam(":limit", $limitQ)
					->queryColumn();

				$data = array_merge($data,$dataNotPaid);
			}
		} else {

			if (!empty($storeList)) {
				$storeList = implode(',', $storeList);

				//Запрос на товары имеющие платные магазины и находящиеся в выбраном городе
				$where = 'WHERE store_ids IN ('.$storeList.') AND price>=1.0 AND category_id<>22 AND sort_default=3 ';

				$sql = $select . $where . $order . $limitSql;
				$data = Yii::app()->sphinx->createCommand($sql)
					->bindParam(":limit", $limit)
					->queryColumn();

				//Запрос на товары продающиесы в определенном городе
				if (count($data)<$limit) {
					$limitQ = $limit - count($data);
					$limitQ = (int)$limitQ;

					$where = 'WHERE store_ids IN ('.$storeList.') AND price>=1.0 AND category_id<>22 ';
					$sql = $select . $where . $order . $limitSql;
					$dataNotPaid = Yii::app()->sphinx->createCommand($sql)
						->bindParam(":limit", $limitQ)
						->queryColumn();
					$data = array_merge($data,$dataNotPaid);
				}
			}

			//Запрос на товары без учета города
			if (count($data)<$limit) {
				$limitQ = $limit - count($data);
				$limitQ = (int)$limitQ;

				$where = 'WHERE  price>=1.0 AND category_id<>22 ';
				$sql = $select . $where . $order . $limitSql;
				$dataNotPaid = Yii::app()->sphinx->createCommand($sql)
					->bindParam(":limit", $limitQ)
					->queryColumn();
				$data = array_merge($data,$dataNotPaid);
			}
		}

		($data) ? $result = $data : $result = array();

		return $data;
	}


	/**
	 * Метод возвращает похожие товары
	 * Для раздела новости и знания
	 * @param $model
	 * @param $city
	 * @param $limit
	 *
	 * @return array
	 */
	public static function getRelatedProductsInMedia($model, $city, $limit)
	{
		$relatedProducts = array();
		//Если установлены категории для похожих
		//товаров через админку статьи
		if ($model->selected_category) {
			$decodedCategory = json_decode($model->selected_category, true);
			$categoryArray = array();

			foreach ($decodedCategory as $key => $dc) {
				$categoriesChildren = Category::model()->getCategoryChildren($key, false, true);

				if ($categoriesChildren) {
					foreach ($categoriesChildren as $cChild) {
						$categoryArray[] = $cChild;
					}
				} else {
					$categoryArray[] = $key;
				}
			}

			$productsArray = self::model()->getProductWithPrice($city, $limit, $categoryArray);

			$relatedProducts = self::model()->findAllByPk($productsArray);

			return $relatedProducts;
		} else {

			$productList = self::getProductWithPrice($city, $limit);
			$relatedProducts = self::model()->findAllByPk($productList);

			return $relatedProducts;
		}
	}


	/**
	 * Получение списка случайных
	 * id товаров
	 * опиционально можно
	 * указать категорию
	 *
	 * @param int   $size
	 * @param array $categories
	 *
	 * @return array
	 */
	static public function getRandomProductsIds($size = 6, $categories = array())
	{
		$productIds = array();

		if ($categories) {
			$categoriesList = implode(',', $categories);
			$sql = 'SELECT id FROM {{product2}} WHERE price >= 0.001 and category_id IN (' . $categoriesList . ') and category_id <> 22 ORDER BY RAND() LIMIT ' . $size;
		} else {
			$sql = 'SELECT id FROM {{product2}} WHERE price >= 0.001 ORDER BY RAND() LIMIT ' . $size;
		}


		$productIds = Yii::app()->sphinx->createCommand($sql)->queryColumn();

		return $productIds;
	}

    public function getSimilarNew($city_id=null)
    {
        if (!$city_id) {
            return array();
        }
        $productIds = Yii::app()->dbcatalog2->createCommand('select distinct p.id from cat_store_price sp
    left join cat_product p on p.id = sp.product_id
    left join cat_store_city sc on sp.store_id = sc.store_id
  where p.status = :active and sp.price > :min and sp.price < :max and p.category_id = :cid and sc.city_id=:city_id
    order by p.create_time
    limit 15;')
            ->bindValue(':active', self::STATUS_ACTIVE)
            ->bindValue(':cid', $this->category_id)
            ->bindValue(':min', round($this->average_price * 0,9))
            ->bindValue(':max', round($this->average_price * 1,1))
            ->bindValue(':city_id', $city_id)
            ->queryColumn();

        $query = implode(',', $productIds);
        if (!$query) {
            return [];
        }
        return Product::model()->findAll('t.id in (' . $query . ')');
    }
}