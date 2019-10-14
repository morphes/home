<?php

/**
 * This is the model class for table "cat_vendor".
 *
 * The followings are the available columns in table 'cat_vendor':
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $name_translit
 * @property string $cat_categories
 * @property string $collections
 * @property string $country_id
 * @property string $city_id
 * @property string $site
 * @property string $image_id
 * @property string $desc
 * @property integer $create_time
 * @property integer $update_time
 */
class Vendor extends Catalog2ActiveRecord
{
	/** @var integer для поиска по контрагентам */
	public $contractor = null;
        public $image;

        public static $preview = array(
                'resize_50' => array(50, 50, 'resize', 80, 'border'=>true),
		'resize_70' => array(70, 70, 'resize', 90, 'border'=>true), // для главной товаров
        );

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Vendor the static model class
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
		return 'cat_vendor';
	}

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'saveImage');
		$this->onBeforeSave = array($this, 'translitName');
		/* Удаление связанных данных */
		$this->onBeforeDelete = array($this, 'removeLinkedData');
        }

	/**
	 * Удаление связанных данных
	 */
	public function removeLinkedData()
	{
		VendorContractor::model()->deleteAllByAttributes(array('vendor_id'=>$this->id));
	}

        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = $this->update_time = time();
                else
                        $this->update_time = time();
        }

	public function translitName()
	{
		$this->name_translit = Amputate::translitYandex($this->name);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, user_id', 'required', 'except'=>'search'),
			array('user_id, image_id, city_id, country_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name, name_translit', 'length', 'max'=>255),
                        array('site', 'url'),
                        array('image', 'file', 'types' => 'jpg, bmp, png, jpeg', 'maxFiles'=> 1, 'maxSize' => 104857600000, 'allowEmpty' => true),
                        array('desc, cat_categories', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, contractor, name, name_translit, desc, cat_categories, city_id, country_id, image_id, site, create_time, update_time', 'safe', 'on'=>'search'),
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
                        'country' => array(self::BELONGS_TO, 'Country', 'country_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'name' => 'Название',
			'name_translit' => 'Название (транслит)',
			'desc' => 'Описание',
                        'collections' => 'Коллекции',
                        'cat_categories' => 'Сферы деятельности',
                        'city_id' => 'Город',
                        'country_id' => 'Страна',
                        'image' => 'Логотип',
                        'site' => 'Сайт',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'contractor' => 'Контрагент',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('name_translit',$this->name_translit,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

        /**
         * Сохранение изображения производителя
         * Вызывается перед сохранением
         * @return boolean
         */
        public function saveImage()
        {
                /**
                 * Если файл не загружался, то завершаем обработчик
                 */
                if(!($this->image instanceof CUploadedFile))
                        return false;

                $file = new UploadedFile();

                $file->author_id = Yii::app()->user->id;
                $file->path = 'catalog'. DIRECTORY_SEPARATOR .intval($this->user_id / UploadedFile::PATH_SIZE + 1). DIRECTORY_SEPARATOR .$this->user_id.'/vendor/'.$this->id;
                $file->name = 'vendor' . $this->id . '_' . mt_rand(1, 99) . '_'. time();
                $file->ext = $this->image->extensionName;
                $file->size = $this->image->size;
                $file->type = UploadedFile::IMAGE_TYPE;

                $folder = UploadedFile::UPLOAD_PATH . DIRECTORY_SEPARATOR . $file->path;

                if (!file_exists($folder))
                        mkdir($folder, 0700, true);

                if ($this->image->saveAs($folder . DIRECTORY_SEPARATOR . $file->name . '.' . $file->ext) && $file->save()) {
                        $this->image_id = $file->id;
                        return $file;
                }

                return false;
        }

        /**
         * @return Array коллекции текущего производителя
         */
        public function getCollectionsArray()
        {
		$cmd = Yii::app()->dbcatalog2->createCommand();
		$cmd->select('id, name');
		$cmd->from('cat_vendor_collection');
		$cmd->where('vendor_id = :vid', array(':vid' => $this->id));
		$cmd->order = 'position ASC';
		$collections = $cmd->queryAll();

		if (is_array($collections))
			return $collections;
		else
			return array();

        }

        /**
         * Возвращает ссылку на производителя по id
         * @param $id
         * @return string
         */
        static public function getLink($id)
        {
                return Yii::app()->createUrl('/catalog2/vendor', array('id'=>$id, 'action'=>'index'));
        }

        /**
         * Возвращает массив стран, в которых есть производители, продающие товары в указанной категории
         */
        static public  function getCountries($cid)
        {
		$mall = Cache::getInstance()->mallBuild;

		$listIds = '';

		Yii::import('application.modules.catalog2.models.Category');

		/** @var $cat Category */
		$cat = Category::model()->findByPk((int)$cid);
		if ($cat && $cat->isLeaf()) {
			$listIds = $cat->id;

		} elseif ($cat) {
			/* Если категория не листьевая, то собираем ID
			   все конечных категорий. */
			$res = $cat->getLastDescendants();
			if ($res) {
				foreach ($res as $item) {
					if ($listIds != '') {
						$listIds .= ',';
					}
					$listIds .= $item['id'];
				}
			}
		} else {

			$listIds = $cid;
		}


                $key = 'Vendor:getCountries:'.$listIds.(($mall) ? 'Mall' : '');

                $data = Yii::app()->cache->get($key);
                if ( ! $data) {

                        $criteria = new CDbCriteria();
                        $criteria->select = 't.*, COUNT(p.id) as pqt';
                        $criteria->condition = 'p.category_id IN (:cid) AND p.status = :pst';
                        $criteria->params = array(':cid' => $listIds, ':pst' => Product::STATUS_ACTIVE);
                        $criteria->join = 'INNER JOIN cat_vendor v ON v.country_id=t.id INNER JOIN cat_product p ON p.vendor_id=v.id';

			if ($mall) {
				$criteria->join .= ' INNER JOIN cat_store_price sp'
					. ' 	ON sp.product_id = p.id'
					. ' INNER JOIN cat_store s'
					. ' 	ON s.id = sp.store_id';

				$criteria->addCondition('s.mall_build_id = :mid');
				$criteria->params[':mid'] = $mall->id;
			}

                        $criteria->group = 't.id';
                        $criteria->having = 'pqt > 0';
                        $criteria->order = 't.pos DESC, t.name ASC';
                        $data = Country::model()->findAll($criteria);
                        Yii::app()->cache->set($key, $data, 600);
                }
                return $data;
        }

        /**
         * Возвращает массив производителей для указанной страны
         * @param $country_id
         * @param $category_id
	 * @param $onlyId
         * @return array of CActiveRecord
         */
        static public function getVendorsByCountry($country_id, $category_id, $onlyId=false)
        {
		$mall = Cache::getInstance()->mallBuild;

		if ($category_id == 1 && $country_id == null)
			return array();

		$listIds = '';

		/** @var $cat Category */
		$cat = Category::model()->findByPk((int)$category_id);
		if ($cat->isLeaf()) {
			$listIds = $cat->id;

		} else {
			/* Если категория не листьевая, то собираем Идентификаторы
			   все конечных категорий. */
			$res = $cat->getLastDescendants();
			if ($res) {
				foreach ($res as $item) {
					if ($listIds != '') {
						$listIds .= ',';
					}
					$listIds .= $item['id'];
				}
			} else {
				return array();
			}
		}

		$criteria = new CDbCriteria();
		$criteria->select = 'DISTINCT vendor_id';
		$criteria->condition = 'category_id IN ('.$listIds.') AND t.status = :stat';
		$criteria->params = array(
			':stat' => Product::STATUS_ACTIVE
		);
//		$criteria->group = 'vendor_id';

		if ($mall) {
			$criteria->join =
				  ' INNER JOIN cat_store_price sp'
				. ' 	ON sp.product_id = t.id'
				. ' INNER JOIN cat_store s'
				. ' 	ON s.id = sp.store_id';

			$criteria->addCondition('mall_build_id = :mid');
			$criteria->params[':mid'] = $mall->id;
		}

		// Выполненям собранный запрос на DAO
		$builder = new CDbCommandBuilder(Yii::app()->dbcatalog2->getSchema());
		$command = $builder->createFindCommand(Product::model()->tableName(), $criteria);
                $vendors = $command->queryAll();

                if (!$vendors) {
                        return array();
		}

                $vendor_ids = array();

                foreach ($vendors as $vendor) {

                        if (!$vendor['vendor_id']) {
                                continue;
			}

                        $vendor_ids[] = $vendor['vendor_id'];
                }

                $vendor_ids = implode(',', $vendor_ids);

                if (!$country_id) {
                        $query = '';
		} else {
                        $query = 'country_id=' . (int) $country_id . ' and ';
		}

		if ( $onlyId ) {
			return Yii::app()->dbcatalog2->createCommand()
				->select('id')->from(self::model()->tableName())
				->where($query . 'id in ('.$vendor_ids.')')
				->queryColumn();
		}

                return self::model()->findAll(array(
			'select' => "*, IF (name REGEXP '^[а-яА-Я0-9]', 0, 1) as sort",
			'condition' => $query . 'id in ('.$vendor_ids.')',
			'order' => 'sort ASC, name ASC'
		));
        }

        /**
         * Возвращает категории, в которых есть товары текущего производителя
         */
        public function getCategories($onlyQuery = false)
        {
                $categories_ids = Yii::app()->dbcatalog2->createCommand()->select('t.id')->from('cat_category t')
                        ->rightJoin('cat_product p', 'p.category_id=t.id and p.vendor_id=:vid and p.status=:stat', array(':vid'=>$this->id, ':stat'=>Product::STATUS_ACTIVE))
                        ->where('(t.rgt-t.lft)=1')->order('t.id')->group('t.id')->queryAll();

                $query_array = array();
                foreach($categories_ids as $id)
                        $query_array[] = $id['id'];

                $query = implode(',', $query_array);

                if($onlyQuery)
                        return $query;

                if($query)
                        return Category::model()->findAll('id in ('.$query.')');
                else
                        return array();
        }

        /**
         * Возвращает магазины в которых есть товары данного производителя
         */
        public function getStores()
        {
                $stores_ids = Yii::app()->dbcatalog2->createCommand()->select('store_id')->from('cat_store_vendor')
                        ->where('vendor_id=:id', array(':id'=>$this->id))->queryAll();

                $query_array = array();
                foreach($stores_ids as $id)
                        $query_array[] = $id['store_id'];

                $query = implode(',', $query_array);

                return $query;
        }

        /**
         * Кол-во товаров производителя в категории
         * @param $category_id
         * @return string
         */
        public function getProductQt($category_id)
        {
                return Product::model()->count('vendor_id=:vid and category_id=:cid and status<>:st', array(
                        ':vid'=>$this->id,
                        ':cid'=>(int) $category_id,
                        ':st'=>Product::STATUS_DELETED,
                ));
        }

	/**
	 * Получение логотипа производителя
	 * @return array|CActiveRecord|mixed|null
	 */
	public function getImage()
	{
		return UploadedFile::model()->findByPk($this->image_id);
	}
}