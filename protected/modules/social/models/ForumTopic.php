<?php

/**
 * This is the model class for table "forum_topic".
 *
 * The followings are the available columns in table 'forum_topic':
 * @property integer $id
 * @property integer $status
 * @property string $name
 * @property string $description
 * @property integer $author_id
 * @property integer $section_id
 * @property integer $count_answer
 * @property integer $count_view
 * @property integer $create_time
 * @property integer $update_time
 */
class ForumTopic extends EActiveRecord
{

	private $files;

	// результат проверки
	public $verifyCode;

	// Тип соритровки по "новизне"
	const SORT_TYPE_TIME = 1;
	// Тип соритровки по "кол-ву ответов"
	const SORT_TYPE_ANSWER = 2;
	// Сортировка от меньшего к большему
	const SORT_DIRECT_DOWN = 1;
	// Сортировка от большего к меньшему
	const SORT_DIRECT_UP = 2;


	// --- СТАТУСЫ ---
	const STATUS_PUBLIC 	= 1; // Опубикован
	const STATUS_HIDE 	= 2; // Скрыт
	const STATUS_DELETED 	= 3; // Удален
	const STATUS_MODERATING 	= 4; // На модерации

	public static $statusNames = array(
		self::STATUS_PUBLIC     => 'Открыт',
		self::STATUS_HIDE       => 'Скрыт',
		self::STATUS_MODERATING => 'На модерации'
	);

	public function behaviors()
	{
		return array(
			'CSafeContentBehavor' => array(
				'class' => 'application.components.CSafeContentBehavior',
				'attributes' => array('name', 'description', 'guest_name'),
			),
		);
	}

	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ForumTopic the static model class
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
		return 'forum_topic';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, section_id, description, author_id', 'required'),
			array('status, section_id, count_answer, count_view, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('description', 'length', 'max'=>3000),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, name, description, author_id, section_id, count_answer, create_time, update_time', 'safe', 'on'=>'search'),
			array('files', 'file', 'types' => 'zip, 7z, rar, jpg, jpeg, png, txt, doc, docx, xls, xlsx, rtf, pdf', 'maxFiles' => 5, 'maxSize' => 10485760, 'allowEmpty' => true),

			// роль GUEST
			array('guest_email, guest_name', 'required', 'on' => 'guest'),
			array('guest_email', 'email', 'message' => 'Некорректный формат адреса электронной почты', 'on' => 'guest'),
			array('guest_email, guest_name', 'length', 'max' => 255, 'on' => 'guest'),
			array('verifyCode', 'captcha', 'captchaAction' => '/site/captcha','allowEmpty'=>!Yii::app()->user->isGuest),
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
			'author'=>array(self::BELONGS_TO, 'User', 'author_id'),
                        'section'=>array(self::BELONGS_TO, 'ForumSection', 'section_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'           => 'ID',
			'status'       => 'Статус',
			'name'         => 'Название',
			'description'  => 'Описание темы',
			'author_id'    => 'Автор',
			'guest_email'  => 'Email',
			'guest_name'   => 'Имя / Название компании',
			'section_id'   => 'Раздел',
			'count_answer' => 'Кол-во ответов в теме',
			'count_view'   => 'Кол-во просмотров',
			'create_time'  => 'Дата создания',
			'update_time'  => 'Дата обновления',
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

		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('guest_email',$this->guest_email, true);
		$criteria->compare('guest_name',$this->guest_name, true);
		$criteria->compare('section_id',$this->section_id);
		$criteria->compare('count_answer',$this->count_answer);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		if ($this->status)
			$criteria->compare('status', $this->status);
		else
			$criteria->addNotInCondition('status', array(ForumTopic::STATUS_DELETED));

		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));


		$sort = new CSort();
		$sort->defaultOrder = 'create_time DESC';

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort'     => $sort
		));
	}


	/**
	 * Функция сохраняет файлы текущего объекта
	 * @return boolean
	 */
	public function saveFiles()
	{
		// Сохранение файлов
		$this->files = CUploadedFile::getInstances($this, 'files');

		if (isset($this->files) && count($this->files) > 0 && $this->validate()) {
			foreach ($this->files as $key => $file) {

				$formatName = $this->getFileName($file->getName());
				$path = $this->getFilePath();
				if (!file_exists($path))
					mkdir ($path, 0755, true);
				if ($file->saveAs($path . '/' . $formatName)) {

					$uf = new UploadedFile();
					$uf->author_id = $this->author_id;
					$uf->path = $this->getFilePath();
					$uf->name = Amputate::getFilenameWithoutExt($formatName);
					// Оригинальное имя берем подстрокой, т.к. pathinfo с русскими символами не рабит
					$uf->original_name = substr($file->getName(), 0, strrpos($file->getName(), '.') );
					$uf->ext = $file->getExtensionName();
					$uf->size = $file->getSize();
					$uf->type = UploadedFile::FILE_TYPE;

					if ($uf->save()) {
						$forumFile = new ForumFile();
						$forumFile->item_id = $this->id;
						$forumFile->file_id = $uf->id;
						$forumFile->type = ForumFile::TYPE_TOPIC;
						$forumFile->save();
					}
				}
			}
		}
		return false;
	}

	/**
	 * Возвращает список всех файлов
	 */
	public function getFiles()
	{
		$result = array();

		$command = Yii::app()->db->createCommand();
		$command->from(ForumFile::model()->tableName());
		$command->select('file_id');
		$command->where = 'item_id = :itemId AND type = :type';
		$command->params = array(':itemId' => $this->id, ':type' => ForumFile::TYPE_TOPIC);

		$files = $command->queryAll();

		if ($files) {
			foreach ($files as $file) {
				$uf = UploadedFile::model()->findByPk((int)$file['file_id']);
				$result[] = array(
					'file_id'       => $uf->id,
					'full_path'	=> '/'.$uf->path.'/'.$uf->name.'.'.$uf->ext,
					'name'          => $uf->name,
					'ext'           => $uf->ext,
					'size'          => $uf->size,
					'original_name' => $uf->original_name
				);
			}
		}

		return $result;
	}

	/**
	 * Возвращает путь до папки хранения фоток к топикам
	 * @return string
	 */
	public function getFilePath()
	{
		return 'uploads/public/forum/topic/'.($this->id % 10000);
	}

	/**
	 * Возвращает имя файла прикрепляемого к топику
	 * @param string $fileName
	 * @return string
	 */
	public function getFileName($fileName = '')
	{
		return time().rand(10,100) . '_' . Amputate::rus2translit($fileName);
	}


	/**
	 * Возвращает ссылку на элемент
	 */
	public function getElementLink()
	{
		return '/forum/topic/id/'.$this->id;
	}


	/**
	 * Возвращает имя класса для иконки файла по расширению файла.
	 * @param $fileExt Расширение файла
	 * @return string Имя класса
	 */
	static public function getClassIcon($fileExt)
	{
		// Имя класса, который будет показывать иконочку для файла
		$cls = '';

		switch($fileExt)
		{
			case'zip':case '7z':case'rar':
				$cls = 'zip';
				break;
			case'jpg':case'jpeg':
				$cls = 'img';
				break;
			case'txt':case'doc':case'docx':rtf:
				$cls = 'doc';
				break;
			case'xls':case'xlsx':
				$cls = 'xls';
				break;
			case'pdf':
				$cls = 'pdf';
				break;
			default:
				$cls = '';
				break;
		}

		return $cls;
	}
}