<?php

/**
 * This is the model class for table "mall_build".
 *
 * The followings are the available columns in table 'mall_build':
 * @property integer $id
 * @property integer $user_id
 * @property integer $image_id
 * @property integer $city_id
 * @property string $name
 * @property string $phone
 * @property string $site
 * @property string $address
 * @property string $work_time
 * @property string about
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $admin_id
 */
class MallBuild extends CActiveRecord implements IUploadImage
{
	public $logo;

	//public $_timeArray;

	public $servicesIds;

	private $_imageType = null;

	public static $preview = array(
		'resize_140' => array(140, 140, 'resize', 80), // Просмотр в админке
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MallBuild the static model class
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

	public function afterFind()
	{
		$this->servicesIds = array_keys($this->services);
		parent::afterFind();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'mall_build';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, image_id, admin_id city_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name, phone, site', 'length', 'max'=>255),
			array('key', 'length', 'max'=>20),
			array('address, work_time', 'length', 'max'=>1000),
			array('about', 'length', 'max'=>3000),
			array('user_id, name, key', 'required'),
			array('site', 'url', 'allowEmpty' => true,
				'message' => 'Неправильный URL сайта',
				'pattern'=>'/^(http(s?)\:\/\/)?(([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)(\.[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)+(\/[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)*(\/?(\?([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1}(&[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1})*){0,1})?))$/i',
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, image_id, city_id, name, phone, site, address, work_time, create_time, update_time, admin_id', 'safe', 'on'=>'search'),
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
			'logoFile' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'author'   => array(self::BELONGS_TO, 'User', 'user_id'),
			'admin'    => array(self::BELONGS_TO, 'User', 'admin_id'),
			'city'     => array(self::BELONGS_TO, 'City', 'city_id'),
			'services' => array(self::MANY_MANY, 'MallService', 'mall_build_service(mall_build_id, mall_service_id)', 'index' => 'id'),
			'floors'   => array(self::HAS_MANY, 'MallFloor', 'mall_build_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'user_id'     => 'Автор',
			'image_id'    => 'Логотип',
			'logo'        => 'Логотип',
			'city_id'     => 'Город',
			'key'         => 'Ключ (лат. символы)',
			'name'        => 'Название',
			'phone'       => 'Телефон',
			'site'        => 'Web-сайт',
			'address'     => 'Адрес',
			'work_time'   => 'Рабочее время',
			'about'       => 'Описание',
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

		$criteria->compare('id', $this->id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('image_id', $this->image_id);
		$criteria->compare('city_id', $this->city_id);
		$criteria->compare('key', $this->key, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('phone', $this->phone, true);
		$criteria->compare('site', $this->site, true);
		$criteria->compare('address', $this->address, true);
		$criteria->compare('work_time', $this->work_time, true);
		$criteria->compare('about', $this->about, true);
		$criteria->compare('create_time', $this->create_time);
		$criteria->compare('update_time', $this->update_time);

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
			case 'logo': return 'catalog/mall/'.$this->id;
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
			case 'logo': return 'mall_logo_' . $this->id . '_' . mt_rand(1, 99) . '_'. time();
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
		return $this->user_id;
	}

	/**
	 * Проверка доступа к объекту пользователем
	 * @return bool true-имеет доступ
	 */
	public function checkAccess()
	{
		return in_array(Yii::app()->user->model->role, array(BaseUser::ROLE_POWERADMIN, BaseUser::ROLE_ADMIN));
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'logo': return array(
				'realtime' => array(
					self::$preview['resize_140'],
				),
				'background' => array(
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}

	public static function getLink($type, $id = null)
	{
		switch($type)
		{
			case 'adminView':
				if (is_null($id))
					throw new CHttpException(400, 'Для типа «adminView» необходимо указать «id» Записи');

				$link = '/catalog/admin/mallBuild/view/id/'.(int)$id;
				break;
			default:
				$link = '#';
				break;
		}

		return $link;
	}



	public function saveServices($servicesIds)
	{
		if ( ! empty($servicesIds)) {

			Yii::app()->db
				->createCommand('DELETE FROM mall_build_service WHERE mall_build_id = :bid')
				->bindValue(':bid', $this->id)
				->execute();

			foreach ($servicesIds as $sid) {
				Yii::app()->db
					->createCommand("INSERT INTO mall_build_service (`mall_build_id`, `mall_service_id`) VALUES ('".$this->id."', '".intval($sid)."')")
					->execute();
			}
		}
	}

}