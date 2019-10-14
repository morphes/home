<?php

/**
 * This is the model class for table "cat_category".
 *
 * The followings are the available columns in table 'cat_category':
 * @property integer $id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $level
 * @property string $name
 * @property string $eng_name
 * @property string $genitiveCase
 * @property string $accusativeCase
 * @property string $groups
 * @property string $desc
 * @property integer $user_id
 * @property integer $image_id
 * @property integer $product_qt
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status
 *
 * @package Catalog
 */
class Category extends Catalog2ActiveRecord implements IUploadImage
{
	public static $preview = array(
		'crop_120'=>array(120, 120, 'crop', 90),
	);

        private $_availableOptions;
        private $_groups=false;

	private $_imageType;

	private static $_root;

	private static $_categoryData = false;

	const IMAGE_CROP = 1;
	const IMAGE_RESIZE = 2;

	/**
	 * @var array формат нарезки фотографий в каталоге
	 */
	static public $imageFormats = array(
		self::IMAGE_CROP=>'Кроп',
		self::IMAGE_RESIZE=>'Ресайз',
	);

	/**
	 * Предыдущий статус категории
	 * @var int
	 */
	public $previousStatus;

	/**
	 * Категория открыты
	 */
	const STATUS_OPEN = 1;
	/**
	 * Категория закрыта
	 */
	const STATUS_CLOSE = 2;


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Category the static model class
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
		return 'cat_category';
	}

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'initData');
		$this->onAfterSave  = array($this,'_updateSubCategoriesStatus');
		$this->onAfterSave = array($this, '_dropCache');


        }

        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = $this->update_time = time();
                else
                        $this->update_time = time();
        }

	public function _dropCache()
	{
		Yii::app()->cache->delete('CATEGORY2:ENGNAMES');
	}

        public function __get($name)
        {
                $getter = 'get' . $name;
                if (method_exists($this, $getter))
                        return $this->$getter();

                return parent::__get($name);
        }

        /**
         * Инициализация пустого массива групп, если группы ранее не были созданы
         */
        public function initData()
        {
		if(empty($this->groups))
                        $this->groups = serialize(array());

        }

        public function behaviors()
        {
                return array(
                        'NestedSetBehavior'=>array(
                                'class'=>'ext.behaviors.NestedSetBehavior',
                                'leftAttribute'=>'lft',
                                'rightAttribute'=>'rgt',
                                'levelAttribute'=>'level',
			),
			'DynaTreeBehavior' => array(
				'class' => 'ext.NestedDynaTree.NestedTreeBehavior',
				'titleAttribute' => 'name',
				'levelAttribute'=>'level',
			),
		);
        }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, name, eng_name, image_format, genitiveCase, accusativeCase', 'required'),
			array('lft, rgt, level, user_id, product_qt, create_time, update_time, status,  image_format', 'numerical', 'integerOnly'=>true),
			array('name, desc, eng_name, genitiveCase, accusativeCase', 'length', 'max'=>255),
                        array('groups, seo_top_desc, seo_bottom_desc', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, lft, rgt, level, name, groups, eng_name, genitiveCase, accusativeCase, image_format, desc, user_id, create_time, update_time', 'safe', 'on'=>'search'),
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
                        //'options'=>array(self::HAS_MANY, 'Option', 'category_id'),
		);
	}

        public function getOptions()
        {
                $criteria = new CDbCriteria();
                $criteria->condition = 'category_id=:id';
                $criteria->params = array(':id'=>$this->id);
                $criteria->order = 'position';

                return Option::model()->findAll($criteria);
        }

        /**
         * Возвращает опции для категории через CDbCommand для облегчения последующей обработки
         * @returtn mixed
         */
        public function getDaoOptions()
        {
                return Yii::app()->dbcatalog2->createCommand()
                        ->from(Option::model()->tableName())
                        ->where('category_id=:cid', array(':cid'=>$this->id))
                        ->order('position')
                        ->limit(150)
                        ->queryAll();
        }

        /**
         * Возвращает опции категории, готовые для использования в товарах (с указанным ключем и названием)
         */
        public function getAvailableOptions()
        {
                if(!$this->_availableOptions) {
                        $this->_availableOptions = Option::model()->findAll(array(
				'condition'=>'t.category_id=:cid AND (t.hide is null OR t.hide=0) AND ((t.key is not null AND t.name is not null) OR (t.parent_id is not null))',
				'order'=>'t.`position`, t.id asc',
				'index'=>'id',
				'params'=>array(':cid'=>$this->id)
			));
                }
                return $this->_availableOptions;
        }


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'           => 'ID',
			'lft'          => 'Lft',
			'rgt'          => 'Rgt',
			'level'        => 'Level',
			'name'         => 'Название',
			'genitiveCase' => 'Название (родительный падеж)',
			'accusativeCase' => 'Название (винительный падеж)',
			'groups'       => 'Группы',
			'desc'         => 'Описание',
			'seo_top_desc' => 'Верхнее SEO описание',
			'seo_bottom_desc' => 'Нижнее SEO описание',
			'user_id'      => 'Создатель',
			'create_time'  => 'Дата создания',
			'update_time'  => 'Дата обновления',
			'status'       => 'Статус',
			'image_format' => 'Формат вывода изображений в каталоге',
		);
	}

	public static $statusLabels = array(
		self::STATUS_CLOSE => 'Закрыта',
		self::STATUS_OPEN  => 'Открыта',
	);

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
		$criteria->compare('lft',$this->lft);
		$criteria->compare('rgt',$this->rgt);
		$criteria->compare('level',$this->level);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('genitiveCase',$this->genitiveCase,true);
		$criteria->compare('accusativeCase',$this->accusativeCase,true);
                $criteria->compare('groups',$this->groups,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

        /**
         * Возвращает корень списка категорий. Если его нет - создаст его.
         * @return Category
         */
        static public function getRoot()
        {
		if (is_null(self::$_root)) {
			$root=self::model()->roots()->find();
			if(!$root) {
				$root=new self;
				$root->name='root';
				$root->user_id=Yii::app()->user->id;
				$root->saveNode();
			}
			self::$_root = $root;
		}

                return self::$_root;
        }

        static public function getGroupedCategoriesList()
        {
                $html = '';
                $level = 0;
                $root = self::getRoot();
                $categories = $root->descendants()->findAll(array('order'=>'lft'));
                $categories_without_childs = self::getCategoriesWithoutChilds();
                $group_open_flag = false;

                foreach($categories as $category)
                {
                        $label = str_repeat("&nbsp;&nbsp;", $category->level-2) . $category->name;

                        if($category->level < $level){

                                if($group_open_flag)
                                        $html.= "</optgroup>";

                                if(isset($categories_without_childs[$category->id])) {
                                        $html.= "<option value='{$category->id}' style='color: #0069D6'>{$label}</option>";
                                } else {
                                        $group_open_flag = true;
                                        $html.= "<optgroup label='{$label}'>";
                                }
                        } elseif (isset($categories_without_childs[$category->id]))
                                $html.= "<option value='{$category->id}' style='color: #0069D6'>{$label}</option>";
                        else {
                                if($group_open_flag)
                                        $html.= "</optgroup>";
                                $group_open_flag = true;
                                $html.= "<optgroup label='{$label}'>";
                        }
                        $level = $category->level;
                }


                return $html;
        }

        /**
         * Возвращает категории, не имеющие потомков
         */
        static public function getCategoriesWithoutChilds()
        {
                $result = array();
                $categories = Yii::app()->dbcatalog2->createCommand('SELECT t.id, t.name FROM cat_category t WHERE (t.rgt - t.lft) = 1 ORDER BY t.level')->queryAll();

                foreach($categories as $category)
                        $result[$category['id']] = $category['name'];

                return $result;
        }


	/**
	 * Возваращает массив моделей Category, в которых есть товары.
	 * Если передать в функцию $city, то список категррий фильтруется по городу.
	 *
	 * @param City $city Объект города
	 *
	 * @return Category[]
	 */
	static public function getNotEmptyCategories($city = null)
	{
		$categoryList = array();
		if ($city instanceof City) {
			$categoryList = Category::getNotEmptyByCity($city->id);
		}

		$sql = 'SELECT DISTINCT cc.* FROM cat_category as cc ';
		if (!empty($categoryList)) {
			$sql .= 'INNER JOIN ( '
				.'SELECT DISTINCT t.id, t.lft, t.rgt FROM `cat_category` `t` '
				.'WHERE t.id IN ('.implode(',', $categoryList).') '
				.') as tmp ON tmp.id=cc.id OR (tmp.lft>cc.lft AND tmp.rgt<cc.rgt) ';
		}
		$sql .= 'WHERE cc.level<>1 and cc.status='.Category::STATUS_OPEN
			.' ORDER BY cc.lft';

		$data = Yii::app()->dbcatalog2->createCommand($sql)->queryAll();
		$categories = Category::model()->populateRecords($data);

		return $categories;
	}



	/**
	 * Возвращает кусок дерева категорий товаров относительно выбранной
	 * категории $categoryId.
	 * Если у активной категориий $categoryId есть дети, в выборку попадают
	 * все родители и дети. Если у категории нет детей — то все родители
	 * и братья.
	 *
	 *
	 * @param Category $category Объект активной категории
	 * @param City $city Объект города, по которому нужно фильтрануть
	 *
	 * @return Category[]
	 */
	static public function getCategoriesWithActive($category = null, $city = null)
	{
		$categoryList = array();
		if ($city instanceof City) {
			$categoryList = Category::getNotEmptyByCity($city->id);
		}

		$sql = 'SELECT DISTINCT ct.* FROM cat_category ct';

		if (!empty($categoryList)) {
			$sql .= ' INNER JOIN ('
				. ' SELECT DISTINCT t.id, t.lft, t.rgt FROM `cat_category` `t` '
				. ' WHERE t.id IN ('.implode(',', $categoryList).') '
				. ' ) as tmp ON tmp.id = ct.id OR (tmp.lft > ct.lft AND tmp.rgt < ct.rgt)';
		}

		$sql .= ' WHERE ct.lft <= :lft '
			. ' AND ct.rgt >= :rgt '
			. ' AND ct.level <> 1 '
			. ' AND ct.status = ' . Category::STATUS_OPEN
			. ' ORDER BY ct.lft';

		$data = Yii::app()->dbcatalog2->createCommand($sql)
			->bindValue(':lft', $category->lft)
			->bindValue(':rgt', $category->rgt)
			->queryAll();

		$result = Category::model()->populateRecords($data);

		
		if ($category->isLeaf()) {

			/* Если вершина конечная, то из предыдущей выборки
			 * удаляем последне значение, чтобы оно не повторилось
			 * при выборке детей
			 */
			array_pop($result);

			/* Если активный узел является последним в своей ветке,
			 * то к выборке добавляем родственников (узлы на том же уровне) 
			 */
			$parent = $category->parent()->find();

		} else {
			/*
			 * Если у актвиного узла есть дочерние, то в выборку
			 * добавляем их.
			 */
			$parent = $category;
		}


		$tt = $parent->children()->findAll(
			'status = :active',
			array(':active' => Category::STATUS_OPEN)
		);

		foreach ($tt as $t) {
			$result[] = $t;
		}


		$categories = $result;

		return $categories;
	}

        /**
         * Проверяет наличие товаров в данной категории
         * @return bool
         */
        public function getProductExists()
        {
                return Product::model()->exists('category_id=:cid', array(':cid'=>$this->id));
        }

        /**
         * Возвращает массив групп категории
         * @return mixed
         */
        public function getGroupsArray()
        {
		if ($this->_groups !== false)
			return $this->_groups;

                if(empty($this->groups))
                        $this->_groups = array();
                else
                        $this->_groups = unserialize($this->groups);

                return $this->_groups;
        }

        /**
         * @return int|string кол-во товаров в дочерних категориях
         */
        public function getDescendantsProductQt()
        {
                $descendants = Yii::app()->dbcatalog2->createCommand()->select('id')->from('cat_category c')
                        ->where('c.lft > :lft and c.rgt < :rgt', array(':lft'=>$this->lft, ':rgt'=>$this->rgt))
                        ->order('c.lft')->queryAll();
                $product_qt = 0;
                /**
                 * Подсчет товаров во всех дочерних категориях
                 */
                foreach($descendants as $cat)
                        $product_qt+=Product::model()->count('status<>:deleted and category_id=:cid', array(':deleted'=>Product::STATUS_DELETED, ':cid'=>$cat['id']));

                return $product_qt;
        }

        /**
         * Возвращает ссылку на категорию по id
         * @param $id
         * @return string
         */
        static public function getLink($id)
        {
                return Yii::app()->createUrl('/catalog2/category/list', array('id'=>$id));
        }

	/**
	 * Получение списка самых последних узлов категории
	 *
	 * @param $order_field Имя поля из cat_category, по которому делаем ORDER
	 * @return array
	 */
	public function getLastDescendants($order_field = 'lft')
        {
                $descendants = Yii::app()->dbcatalog2->createCommand()->select('id, name, product_qt')->from('cat_category c')
                        ->where('c.rgt - c.lft = 1 and c.rgt < :rgt and c.lft > :lft', array(':rgt'=>$this->rgt, ':lft'=>$this->lft))
                        ->order('c.'.$order_field)->queryAll();

                return $descendants;
        }

        /**
         * @return array массив доп. параметров опции
         */
        public function getParamsArray()
        {
                if(empty($this->params{0}))
                        return array();

                $params = unserialize($this->params);

                if(is_array($params))
                        return $params;
                else
                        return array();
        }

	/**
	 * Находит максимальную цену серди товаров категории, и записывает ей эту цифру.
	 * Используется потом для вывода максимальной цены в фильтре товаров.
	 *
	 * @param null $cid Инеднификатор категории
	 */
	static public function setMaxPrice($cid = null)
	{
		$cid = intval($cid);

		/** @var $model Category */
		$model = Category::model()->findByPk($cid);
		if ($model) {
			$max_price = Yii::app()->dbcatalog2->createCommand("SELECT MAX(average_price) as max_price FROM cat_product WHERE category_id = '{$cid}'")->queryScalar();

			$data = $model->getParamsArray();

			$data['max_price'] = ceil($max_price);

			Category::model()->updateByPk($model->id, array('params' => serialize($data)));

			return $data['max_price'];
		}

		return false;
	}


	/**
	 * Получает у сфинкса информацию о максимальной цене товаров
	 * в текущей категории.
	 *
	 * @return float|mixed
	 */
	public function getMaxPrice()
	{
		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$key = 'CATEGORY_MAX_PRICE:'.$this->id.':'.$mall->id;
		} else {
			$key = 'CATEGORY_MAX_PRICE:'.$this->id;
		}

		$data = Yii::app()->cache->get($key);
		if ( $data !== false )
			return $data;

		$maxPrice = 0.0;
		if ($this->getIsNewRecord()) {
			return $maxPrice;
		}

		$condition = '';
		if ($mall instanceof MallBuild) {
			$condition = ' AND mall_ids='.$mall->id;
		}

		
		$ids = array(0);
		if ($this->isLeaf()) {
			$ids[] = $this->id;
		} else {
			$ld = $this->getLastDescendants();
			foreach ($ld as $item) {
				$ids[] = $item['id'];
			}
		}

		$sql = 'SELECT MAX(price) FROM {{product2}} WHERE category_id IN (' . implode(',', $ids) . ')' . $condition . ' GROUP BY category_id';
		
		$data = Yii::app()
			->sphinx
			->createCommand($sql)
			->queryColumn();

		foreach ($data as $item) {
			if ($maxPrice < $item)
				$maxPrice = $item;
		}

		Yii::app()->cache->set($key, $maxPrice, Cache::DURATION_HOUR);
		return $maxPrice;
	}

	/**
	 * Получение пути для сохранения файла, в формате для uploadedFile
	 * @return string | false для новых записей
	 */
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'category': return 'category/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	/**
	 * Получение имени для сохранения файла, в формате для uploadedFile
	 * @return string | false для новых записей
	 */
	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'category': return 'interior'.$this->id.'_'.time();
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->user_id;
	}

	/**
	 * Проверка доступа к объекту пользователем
	 * @return bool true-имеет доступ
	 */
	public function checkAccess()
	{
		return true;
	}

	/**
	 * Установка типа загружаемого изображения для модели
	 * @return mixed
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
	 * Конфиг для получения превью в конкретной модели
	 * @return array
	 */
	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'category': return array(
				'realtime' => array(
					self::$preview['crop_120'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getPreview($config)
	{
		if (!is_null($this->image_id)) {
			$uploadedFile = UploadedFile::model()->findByPk($this->image_id);
			if (!is_null($uploadedFile)) {
				$previewFile = $uploadedFile->getPreviewName($config);
				return $previewFile;
			}
		}
		$name = $config[0].'x'.$config[1];
		return UploadedFile::getDefaultImage('default', $name);
	}

	/**
	 * Получение массива не нулевых категорий по указанному городу
	 * @param $cityId
	 */
	public static function getNotEmptyByCity($cityId)
	{
		$cityId = intval($cityId);
		$key = 'CATEGORIES_BY_CITY:'.$cityId;
		$data = Yii::app()->cache->get($key);
		if ( is_array($data) )
			return $data;

		$storeList = StoreGeo::getStoreList($cityId);
		$data = array();
		if (!empty($storeList)) {
			$storeList = implode(',', $storeList);
			$sql = 'SELECT COUNT(*) as cnt, category_id FROM {{product2}} WHERE store_ids IN ('.$storeList.') GROUP BY category_id LIMIT 100';
			$data = Yii::app()->sphinx->createCommand($sql)->queryAll();
		}

		$categoryList = array();
		foreach ($data as $item) {
			if ($item['cnt'] > 0)
				$categoryList[] = $item['category_id'];
		}

		Yii::app()->cache->set($key, $categoryList, Cache::DURATION_HOUR);

		return $categoryList;
	}

	/**
	 * Получение массива не нулевых категорий для молла по указанному городу
	 * @param $cityId
	 */
	public static function getNotEmptyByMall($mallId)
	{
		$mallId = intval($mallId);
		$key = 'CATEGORIES_BY_MALL:'.$mallId;

		$data = Yii::app()->cache->get($key);
		if ( is_array($data) )
			return $data;

		$sql = 'SELECT COUNT(*) as cnt, category_id FROM {{product2}} WHERE mall_ids=:mid GROUP BY category_id LIMIT 100';
		$data = Yii::app()->sphinx->createCommand($sql)->bindParam(':mid', $mallId)->queryAll();

		$categoryList = array();
		foreach ($data as $item) {
			if ($item['cnt'] > 0)
				$categoryList[] = $item['category_id'];
		}

		Yii::app()->cache->set($key, $categoryList, Cache::DURATION_HOUR);

		return $categoryList;
	}


	/**
	 * Меняем статусы у дочерних категорий в зависимости от статуса предка
	 */
	public function _updateSubCategoriesStatus()
	{
		if ($this->previousStatus !== $this->status) {
			self::model()->updateAll(array('status' => $this->status), 'lft>=:lft AND rgt<=:rgt', array(':lft' => $this->lft, ':rgt' => $this->rgt));
		}
	}


	/**
	 * Получить список дочерних категорий
	 * Если returnArObject установлен в true
	 * Если установлен $onlyLastLevel
	 * @param      $id
	 * @param bool $returnArObject
	 * @param bool $onlyLastLevel
	 *
	 * @return array|CActiveRecord|CDbDataReader|mixed|null
	 *
	 */
	public static function getCategoryChildren($id, $returnArObject = false, $onlyLastLevel = false)
	{
		$category = self::model()->findByPk($id);

		if ($category) {
			$criteria = new CDbCriteria();

			$criteria->condition = 'lft>=:lft AND rgt<=:rgt';

			$criteria->params = array(
				':lft' => $category->lft,
				':rgt' => $category->rgt,
			);

			if ($onlyLastLevel) {
				$criteria->condition .= ' and (rgt-lft) = 1 ';
			}


			if ($returnArObject) {
				$result = self::model()->findAll($criteria);
			} else {
				$builder = new CDbCommandBuilder(Yii::app()->dbcatalog2->getSchema());

				$criteria->select = 'id';
				$command = $builder->createFindCommand('cat_category', $criteria);

				$result = $command->queryColumn();
			}


			if ($result) {
				return $result;
			}

			return array();
		}
	}


	/**
	 * Метод возвращает список категорий для
	 * знаний и новостей. Если категории заданы
	 * в админке то возвращаются они + если не хватает до лимита
	 * добивается случайными категориями
	 * @param     $model
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function getRelatedCategoryInMedia($model, $limit = 7)
	{
		$categoryArray = array();
		$randCategoryArray = array();
		$categoryIds = array();

		$criteria = new CDbCriteria();
		$criteria->condition = 'STATUS = 1 AND id!=1';
		$criteria->order = 'rand()';

		//Если категории установелны через админку
		if ($model->selected_category) {
			$decodedCategory = json_decode($model->selected_category, true);

			foreach ($decodedCategory as $key => $dc) {
				//Получаем дочерние категории
				$categoriesChildren = self::model()->getCategoryChildren($key, false, true);

				//Если есть дочернии категории то добавляем в массив айдишников
				if ($categoriesChildren) {
					foreach ($categoriesChildren as $cChild) {
						$categoryIds[] = (int)$cChild;
					}
					$categoryIds[] = (int)$key;
				} else {
					$categoryIds[] = (int)$key;
				}
			}
			//Убираем дубли
			$categoryIds = array_unique($categoryIds);
			//Перемешиваем массив
			shuffle($categoryIds);

			foreach ($categoryIds as $cid) {
				$categoryArray[] = self::model()->findByPk($cid);
			}

			$countCategory = count($categoryArray);
			//Если выставленных категорий не хватает до лимита добиваем случайными
			if ($countCategory < $limit) {
				$criteria->limit = $limit - $countCategory;
				$randCategoryArray = self::model()->findAll($criteria);
				$categoryArray = array_merge($categoryArray, $randCategoryArray);
			}
		//Если категории через админку не установлены то выдаем случайные
		} else {
			$criteria->limit = $limit;
			$categoryArray = self::model()->findAll($criteria);
		}

		if ($categoryArray) {
			return $categoryArray;
		}

		return array();
	}


	/**
	 * Возвращает ключ для массива категорий в фильтре на странице списка
	 * товаров каталога.
	 *
	 * @return string
	 */
	public static function getCacheKeyCategoryMall($mallId, $categoryId)
	{
		$mallId = (int)$mallId;
		$categoryId = (int)$categoryId;

		return 'CATALOG2:MALL:' . $mallId . ':CATEGORY2:' . $categoryId;
	}

	/**
	 * Получение eng_name для категории
	 * @param $id
	 */
	public static function getCategoryName($id)
	{
		if ( self::$_categoryData === false ) {
			$data = Yii::app()->cache->get('CATEGORY2:ENGNAMES');
			if ( $data === false ) { // получение из БД
				$sql = 'SELECT id, eng_name FROM cat_category';
				$data = Yii::app()->dbcatalog2->createCommand($sql)->setFetchMode(PDO::FETCH_KEY_PAIR)->queryAll();
				Yii::app()->cache->set('CATEGORY2:ENGNAMES', $data, Cache::DURATION_DAY);
			}
			self::$_categoryData = $data;
		}

		if ( isset(self::$_categoryData[$id]) )
			return self::$_categoryData[$id];

		return null;
	}

    static public function getRootChildren(Category $excludeCat=null)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('status', self::STATUS_OPEN);
        if ($excludeCat) {
            $criteria->compare('id', '<>'.$excludeCat->id);
            $criteria->compare('id', '<>'.$excludeCat->parent()->find()->id);
        }
        return self::model()->getRoot()->children()->findAll($criteria);
    }
}