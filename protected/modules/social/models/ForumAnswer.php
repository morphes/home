<?php

/**
 * This is the model class for table "forum_answer".
 *
 * The followings are the available columns in table 'forum_answer':
 * @property integer $id
 * @property integer $status
 * @property integer $author_id
 * @property integer $topic_id
 * @property string $answer
 * @property integer $count_like
 * @property integer $create_time
 * @property integer $update_time
 */
class ForumAnswer extends EActiveRecord
{
	private $files;
	public $answer_id;

	// результат проверки
	public $verifyCode;

	// --- СТАТУСЫ ---
	const STATUS_PUBLIC 		= 1; // Опубикован
	const STATUS_HIDE 		= 2; // Скрыт
	const STATUS_DELETED 		= 3; // Удален
	const STATUS_MODERATING 		= 4; // На модерации
	const STATUS_DELETED_SOFT 	= 5; // Мягкое удаление.

	public static $statusNames = array(
		self::STATUS_PUBLIC       => 'Открыт',
		self::STATUS_HIDE         => 'Скрыт',
		self::STATUS_MODERATING   => 'На модерации',
		self::STATUS_DELETED_SOFT => '«Мягко» удален'
	);

	public function behaviors()
	{
		return array(
			'CSafeContentBehavor' => array(
				'class' => 'application.components.CSafeContentBehavior',
				'attributes' => array('answer'),
				'options' => array(
					'HTML.AllowedElements' => array(
						'span' => true,
						'div'  => true,
						'i'    => true,
						'p'    => true
					),
				),
			),
		);
	}

	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
		$this->onBeforeSave = array($this, 'correctAnswer');
		$this->onAfterSave = array($this, 'countAnswer');
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

	public function correctAnswer()
	{
		$this->answer = preg_replace(
			'#\[quote="(.*?)"\](.*?)\[\/quote\]#is',
			'<div class="quote_text"><i></i><span>$1</span><p>$2</p></div>',
			$this->answer
		);
	}

	/**
	 * Пересчитывает и обновляет кол-во ответов к теме.
	 */
	public function countAnswer()
	{
		$cnt = ForumAnswer::model()->countByAttributes(
			array('topic_id' => $this->topic_id),
			'status = :st1 OR status = :st2',
			array(':st1' => ForumAnswer::STATUS_PUBLIC, ':st2' => ForumAnswer::STATUS_DELETED_SOFT)
		);

		ForumTopic::model()->updateByPk($this->topic_id, array('count_answer' => $cnt));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ForumAnswer the static model class
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
		return 'forum_answer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, topic_id, answer', 'required'),
			array('status, author_id, topic_id, count_like, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('answer', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, author_id, topic_id, answer, count_like, create_time, update_time', 'safe', 'on'=>'search'),
			array('verifyCode', 'captcha', 'captchaAction' => '/site/captchaWhite','allowEmpty'=>!Yii::app()->user->isGuest),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'status'      => 'Статус',
			'author_id'   => 'Автор',
			'topic_id'    => 'Тема форума',
			'answer'      => 'Ответ',
			'count_like'  => 'Кол-во лайков',
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
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('topic_id',$this->topic_id);
		$criteria->compare('answer',$this->answer,true);
		$criteria->compare('count_like',$this->count_like);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		if ($this->status)
			$criteria->compare('status',$this->status);
		else
			$criteria->addNotInCondition('status', array(ForumAnswer::STATUS_DELETED));

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
					$uf->author_id     = $this->author_id;
					$uf->path          = $this->getFilePath();
					$uf->name          = Amputate::getFilenameWithoutExt($formatName);
					$uf->ext           = $file->getExtensionName();
					// Оригинальное имя берем подстрокой, т.к. pathinfo с русскими символами не рабит
					$uf->original_name = substr($file->getName(), 0, strrpos($file->getName(), '.') );
					$uf->size          = $file->getSize();
					$uf->type          = UploadedFile::FILE_TYPE;

					if ($uf->save()) {
						$forumFile          = new ForumFile();
						$forumFile->item_id = $this->id;
						$forumFile->file_id = $uf->id;
						$forumFile->type    = ForumFile::TYPE_ANSWER;
						$forumFile->save();

					}
				}
			}
			return true;
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
		$command->params = array(':itemId' => $this->id, ':type' => ForumFile::TYPE_ANSWER);

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
		return 'uploads/public/forum/answer/'.($this->id % 10000);
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
	 * Отвечает на вопрос является ли просмтаривающий автором.
	 */
	public function isAuthor()
	{
		return (Yii::app()->user->id == $this->author_id);
	}
}