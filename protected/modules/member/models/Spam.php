<?php

/**
 * This is the model class for table "spam".
 *
 * The followings are the available columns in table 'spam':
 * @property integer $id
 * @property integer $msg_id
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 */
class Spam extends EActiveRecord
{
	const STATUS_NEW = 1;
	const STATUS_PROCESSED = 2;
	const STATUS_REJECTED = 3;

	//Автор сообщения на которого поступила жалоба
	public $authorName = null;

	/**
	 * @var string Инициатор заявки. Тот на которого жалоба поступила
	 */
	public $recipientName = null;

	/**
	 * Переменная в которою устанавливаются
	 * имя пользователя по которому надо вывести
	 * все сообщения
	 * @var null
	 */
	public $allMessageFilter = null;

	/**
	 * Переменная с количеством сообщений
	 * для фильтра
	 * @var null
	 */
	public $countMessageFilter = null;

	/**
	 * Поисковая фраза для сообщения
	 * @var null
	 */
	public $searchString = null;


	/**
	 * @var int значения фильтра время до
	 */
	public $timeTo;

	/**
	 * @var int значения фильтра время от
	 */
	public $timeFrom;

	public static $statusLabels = array(
		self::STATUS_NEW       => 'Не обработана',
		self::STATUS_PROCESSED => 'Обработана',
		self::STATUS_REJECTED  => 'Отклонена',

	);


	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
	}


	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Spam the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'spam';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('msg_id', 'required'),
			array('msg_id, status, create_time', 'numerical', 'integerOnly' => true),
			array('status', 'in', 'range'=>array_keys(self::$statusLabels)),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('timeTo,timeFrom,authorName,recipientName,id, msg_id, status, create_time, allMessageFilter,countMessageFilter,searchString','safe', 'on' => 'search'),
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
			'msgBody' => array(self::BELONGS_TO, 'MsgBody', 'msg_id'),
		);
	}

	public function getmessage()
	{
		return $this->msgBody->message;
	}

	public function getauthor_id()
	{
		return $this->msgBody->author_id;
	}

	public function getrecipient_id()
	{
		return $this->msgBody->recipient_id;
	}



	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'            => 'ID',
			'msg_id'        => 'Msg',
			'status'        => 'Статус',
			'message'       => 'Сообщение',
			'create_time'   => 'Время создания',
			'recipientName' => 'Жалоба от',
			'authorName'    => 'Жалоба на',
		);
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
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.
		$criteria = new CDbCriteria;

		if ($this->timeFrom) {
			$criteria->compare('create_time', '>=' . strtotime($this->timeFrom));
		}

		if ($this->timeTo) {
			$criteria->compare('create_time', '<' . strtotime('+1 day', strtotime($this->timeTo)));
		}

		//Если установлен фильтр по всем сообщениям
		if (!empty($this->allMessageFilter)) {
			$user = User::model()->findByAttributes(array('login' => $this->allMessageFilter));
			if ($user) {
				$userId = $user->id;
			} else $userId = 0;
			$criteria->compare('author_id', '=' . $userId);
			$criteria->order = 'create_time DESC';

			return new CActiveDataProvider(MsgBody::model(), array(
				'criteria'   => $criteria,
				'pagination' => array('pageSize' => 20),
			));
		}

		//Если установлен фильтр по количеству сообщений
		if (!empty($this->countMessageFilter)) {
			$criteria->select = 'author_id,recipient_id,message,create_time,update_time,COUNT(author_id) as num';
			$criteria->group = '1';
			$criteria->having = 'num > ' . $this->countMessageFilter;
			$criteria->order = 'num DESC';

			return new CActiveDataProvider(MsgBody::model(), array(
				'criteria'   => $criteria,
				'pagination' => array('pageSize' => 20),

			));
		}

		//Если установлен фильтр по ключевой фразе
		if (!empty($this->searchString)) {
			$string = $this->searchString;

			$from = array('\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '=', "'", "\x00", "\n", "\r", "\x1a");
			$to = array('\\\\', '\\\(', '\\\)', '\\\|', '\\\-', '\\\!', '\\\@', '\\\~', '\\\"', '\\\&', '\\\/', '\\\^', '\\\$', '\\\=', "\\'", "\\x00", "\\n", "\\r", "\\x1a");

			$string = str_replace($from, $to, $string);


			$string = (string)$string;
			$messageIds = array();

			$sql = "SELECT id FROM {{user_message}} WHERE MATCH('@message $string')";
			$messageIds = Yii::app()->sphinx->createCommand($sql)
				->queryColumn();

			if ($messageIds) {
				$stringIds = implode(',', $messageIds);
			} else {
				$stringIds = '0';
			}

			$criteria->condition = 'id in (' . $stringIds . ')';
			$criteria->order = 'create_time DESC';

			return new CActiveDataProvider(MsgBody::model(), array(
				'criteria'   => $criteria,
				'pagination' => array('pageSize' => 20),

			));
		}

		//Фильтр по логину спамера
		if (!empty($this->authorName)) {
			if ($user = User::model()->findByAttributes(array('login' => $this->authorName))) {

				$idUser = $user->id;
				$criteria->join = 'INNER JOIN msg_body ON msg_id=msg_body.id';
				$criteria->condition = 'msg_body.author_id=:idUser';
				$criteria->params = array(':idUser' => $idUser);

				$idUser = false;
			} else {
				$criteria->compare('msg_id', 0);
			}
		}


		//Фильтр по логину сообщившего о спаме
		if (!empty($this->recipientName)) {

			$user = User::model()->findByAttributes(array('login' => $this->recipientName));

			if ($user) {
				$idUser = $user->id;
				$criteria->join = 'INNER JOIN msg_body ON msg_id=msg_body.id';
				$criteria->condition = 'msg_body.recipient_id=:idUser';
				$criteria->params = array(':idUser' => $idUser);

				$idUser = false;
			} else {
				$criteria->compare('msg_id', 0);
			}
		}

		//Фильтр по статусу
		if ($this->status) {
			$criteria->compare('status', $this->status);
		}

		$criteria->order = 'id DESC';

		return new CActiveDataProvider($this, array(
			'criteria'   => $criteria,
			'pagination' => array('pageSize' => 20),

		));
	}
}