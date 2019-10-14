<?php

/**
 * This is the model class for table "cat_chain".
 *
 * The followings are the available columns in table 'cat_chain':
 * @property integer $id
 * @property integer $image_id
 * @property string $name
 * @property integer $user_id
 * @property string $email
 * @property string $site
 * @property string $phone
 * @property integer $city_id
 * @property integer $admin_id
 * @property string $address
 * @propery string $geocode
 * @property string $about
 * @property integer $create_time
 * @property integer $update_time
 */
class Chain extends EActiveRecord implements IUploadImage
{
        // Тип изображения для загрузки
        private $_imageType = null;

        /**
         * @var используется в поисковом фильтре
         */
        public $vendor_id;

        public $logo;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Chain the static model class
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
		return 'cat_chain';
	}

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
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
                        array('name, user_id', 'required', 'except'=>'search'),
			array('image_id, city_id, user_id, admin_id, vendor_id', 'numerical', 'integerOnly'=>true),
			array('name, email, site, phone, geocode', 'length', 'max'=>255),
			array('address', 'length', 'max'=>1000),
			array('about', 'length', 'max'=>3000),
                        array('email', 'email'),
                        array('site', 'url'),
                        array('logo', 'file', 'types' => 'jpg, bmp, png, jpeg', 'maxFiles'=> 1, 'maxSize' => 104857600000, 'allowEmpty' => true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, image_id, vendor_id, name, email, site, city_id, phone, admin_id, address, geocode, about, create_time, update_time', 'safe', 'on'=>'search'),
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
                        'author'=> array(self::BELONGS_TO, 'User', 'user_id'),
                        'admin'=> array(self::BELONGS_TO, 'User', 'admin_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'image_id' => 'Логотип',
                        'logo' => 'Логотип',
			'name' => 'Название',
			'email' => 'Email',
                        'user_id'=>'Автор',
			'site' => 'Сайт',
                        'city_id' => 'Город',
			'phone' => 'Телефоны',
			'address' => 'Адрес',
                        'vendor_id' => 'Производитель',
			'about' => 'Описание',
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
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('site',$this->site,true);
                $criteria->compare('user_id',$this->user_id);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('about',$this->about,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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
                        case 'logo': return 'catalog/chain/'.Yii::app()->user->id.'/'.$this->id;
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
                        case 'logo': return 'chain' . $this->id . '_' . mt_rand(1, 99) . '_'. time();
                        default: throw new CException('Invalid upload image type');
                }
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
                return in_array(Yii::app()->user->model->role, array(BaseUser::ROLE_POWERADMIN, BaseUser::ROLE_ADMIN));
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

        public function getStores($asDataProvider = false, $onlyIdsQuery = false)
        {
                $chain_stores = Yii::app()->db->createCommand()
                        ->select('store_id')->from('cat_chain_store')
                        ->where('chain_id=:cid', array(':cid'=>$this->id))
                        ->queryAll();

                $stores_list = array();

                foreach($chain_stores as $store)
                        $stores_list[]=$store['store_id'];

                $stores_ids = implode(',', $stores_list);

                if(!$stores_ids)
                        $stores_ids = 0;

                if($onlyIdsQuery)
                        return $stores_ids;

                if(!$asDataProvider) {
                        $stores = Store::model()->findAll("id in ({$stores_ids})");
                        return $stores;
                } else {
                        $stores=new CActiveDataProvider('Store', array(
                                'criteria'=>array(
                                        'condition'=>"id in ({$stores_ids})",
                                ),
                        ));
                        return $stores;
                }
        }

        public function imageConfig()
        {
                return array();
        }

        /**
         * Возвращает объект страны магазина
         * @return CActiveRecord|null
         */
        public function getCountry()
        {
                if ($this->city)
                        return Country::model()->findByPk($this->city->country_id);
                else
                        return null;
        }

        /**
         * Возвращает DataProvider производителей, товары которых продают магазины сети
         * @return CActiveDataProvider
         */
        public function getVendors()
        {
                $stores_ids = Yii::app()->db->createCommand()->select('store_id')->from('cat_chain_store')
                        ->where('chain_id=:id', array(':id'=>$this->id))->queryAll();

                $store_query = array();

                foreach($stores_ids as $sid)
                        $store_query[] = $sid['store_id'];

                $store_query = implode(',', $store_query);

                if(!$store_query)
                        $store_query = 0;


                $store_vendor = Yii::app()->db->createCommand()
                        ->select('vendor_id')->from('cat_store_vendor')
                        ->where('store_id in ('.$store_query.')')->group('vendor_id')
                        ->queryAll();

                $vendors_list = array();

                foreach($store_vendor as $vendor)
                        $vendors_list[]=$vendor['vendor_id'];

                $vendor_ids = implode(',', $vendors_list);

                if(!$vendor_ids)
                        $vendor_ids = 0;

                $vendors=new CActiveDataProvider('Vendor', array(
                        'criteria'=>array(
                                'condition'=>"id in ({$vendor_ids})",
                        ),
                ));
                return $vendors;

        }
}