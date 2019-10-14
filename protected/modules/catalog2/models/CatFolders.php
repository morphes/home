<?php

/**
 * This is the model class for table "cat_folders".
 *
 * The followings are the available columns in table 'cat_folders':
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $user_id
 * @property integer $image_id
 * @property integer $status
 * @property integer $update_time
 * @property integer $create_time
 */
class CatFolders extends Catalog2ActiveRecord implements IUploadImage
{
	//Новая папка без товаров
	const STATUS_EMPTY = 1;

	//Папка с товарами
	const STATUS_NOT_EMPTY = 2;

	//Удаленная папка
	const STATUS_DELETED = 3;

	//Скрытая папка
	const  STATUS_HIDDEN = 4;

	//Переменная с торговым центром
	// нужен для фильтрации
	public $mallId;

	public static $preview = array(
		'crop_200' => array(200, 200, 'crop', 80),
		'crop_292' => array(292, 292, 'crop', 80),
	);

	// Тип изображения для загрузки
	private $_imageType = null;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CatFolders the static model class
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
		return 'cat_folders';
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, status, name', 'required'),
			array('user_id, status, update_time, create_time', 'numerical', 'integerOnly'=>true),
			array('name, description', 'length', 'max'=>2000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('mallId, id, name, user_id, status, update_time, create_time', 'safe', 'on'=>'search'),
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
				'mall' => array(self::BELONGS_TO, 'MallBuild', array('user_id' => 'admin_id'), ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название папки',
			'mall' => 'Торговый центр',
			'user_id' => 'id владельца',
			'status' => 'Статус',
			'update_time' => 'Дата обновления',
			'create_time' => 'Дата создания',
			'count' => 'Количество товаров',
			'userName' => 'Имя владельца',
			'description' => 'Описание',
			'mallId'      => 'Id торгового центра'
		);
	}

	public static $statusLabels = array(
		self::STATUS_EMPTY => 'Пустая папка',
		self::STATUS_NOT_EMPTY => 'Активная папка',
		self::STATUS_DELETED => 'Удаленная папка',
		self::STATUS_HIDDEN => 'Скрытая папка',
	);


	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
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

		if(isset($this->mallId) &&  !empty($this->mallId)){
			$criteria->join = 'INNER JOIN mall_build as mb ON t.user_id = mb.admin_id AND mb.id = :mallId';
			$criteria->params[':mallId'] = $this->mallId;
		}

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.user_id',$this->user_id);
		$criteria->compare('t.status',$this->status);
		$criteria->compare('t.update_time',$this->update_time);
		$criteria->compare('t.create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Возвращает ссылку на папку
	 * @return mixed
	 */
	public function getLink()
	{
		return Yii::app()->createUrl('/catalog2/folders/folder/id/'.$this->id);
	}




	/**
	 * Метод возвращает список папок пользователя
	 * @return mixed
	 */
	public function getFoldersByUserId($id)
	{
		$command = Yii::app()->dbcatalog2->createCommand();

		$command->select('id, name')
			->from($this->tableName())
			->where('user_id = :id AND status<>:status', array(':id' => $id,':status' => self::STATUS_DELETED));
		$command->order = 'create_time ASC';

		$arr = $command->queryAll();

		return $arr;
	}


	/**
	 * Проверяет является
	 * ли пользователь
	 * модератором магазина
	 * @param $userId
	 *
	 * @return bool
	 */
	public function isOwner($userId)
	{
		if ($this->user_id == $userId) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Метод возвращает массив из
	 * объектов случайных папок
	 * @param int $limit
	 *
	 * @return array|CActiveRecord|mixed|null
	 */
	public static function  getRandomFolder($limit=3)
	{
		$criteria = new CDbCriteria();
		$criteria->limit = $limit;
		$criteria->order = 'Rand()';
		$criteria->condition = 'status='.self::STATUS_NOT_EMPTY;
		$models = self::model()->findAll($criteria);

		if($models){
			return $models;
		}
		else{
			return array();
		}
	}


	/**
	 * Количество кешируется на час
	 * @return mixed
	 */
	public static function getCount()
	{
		$key = 'COUNT_FOLDERS_BM';
		$data = Yii::app()->cache->get($key);
		if ($data && $data > 0) {
			return $data;
		}

		$sql = 'Select count(*) FROM cat_folders WHERE status=' . self::STATUS_NOT_EMPTY;
		$count = Yii::app()->dbcatalog2->createCommand($sql)->queryScalar();

		Yii::app()->cache->set($key, $count, Cache::DURATION_HOUR);

		return $count;
	}

	/**
	 * Получение превью папки
	 * @param $config
	 */
	public function getPreview($config)
	{
		$uFile = null;
		if ( empty($this->image_id) ) {
			// NOTICE: работает только с товарами
			$sql = 'SELECT uf.* FROM uploaded_file as uf '
				.'INNER JOIN cat_product as p ON p.image_id=uf.id '
				.'INNER JOIN cat_folder_item as i ON i.model_id=p.id '
				.'WHERE i.folder_id=:fid '
				.'ORDER BY i.position DESC '
				.'LIMIT 1';
			$folderId = intval($this->id);

			$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':fid', $folderId)->queryRow();
			$uFile = (empty($data)) ? null : UploadedFile::model()->populateRecord($data);
		} else {
			$uFile = UploadedFile::model()->findByPk($this->image_id);
		}

		if ($uFile === null) {
			$name = $config[0].'x'.$config[1];
			$preview = UploadedFile::getDefaultImage('user', $name);
		} else {
			$preview = $uFile->getPreviewName($config);
		}

		return $preview;
	}

	/**
	 * Получение всех uploadedFile, связанных с товарами, в текущей папке
	 */
	public function getPhotos()
	{
		// NOTICE: работает только с товарами
		$sql = 'SELECT uf.* FROM uploaded_file as uf '
			.'INNER JOIN cat_product as p ON p.image_id=uf.id '
			.'INNER JOIN cat_folder_item as i ON i.model_id=p.id '
			.'WHERE i.folder_id=:fid '
			.'ORDER BY i.position DESC ';

		$folderId = intval($this->id);

		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':fid', $folderId)->queryAll();
		return UploadedFile::model()->populateRecords($data, true, 'id');
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
			case 'folder': return 'folders/'.intval($this->id/100 + 1);
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
			case 'folder': return 'folder_'.$this->id.'_'.time();
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
		return ( $this->user_id!==null && $this->user_id == Yii::app()->getUser()->getId() );
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
			case 'folder': return array(
				'realtime' => array(
					self::$preview['crop_200'],
				),
				'background' => array(
					self::$preview['crop_292'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
}