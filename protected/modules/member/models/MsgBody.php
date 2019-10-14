<?php

/**
 * This is the model class for table "msg_body".
 *
 * The followings are the available columns in table 'msg_body':
 * @property integer $id
 * @property integer $chain_id
 * @property integer $author_id
 * @property integer $author_status
 * @property integer $recipient_status
 * @property string $message
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property MsgChain $chain
 * @property User $author
 */
class MsgBody extends EActiveRecord
{
        const STATUS_DELETE = 0;
        const STATUS_READ = 1;
        const STATUS_UNREAD = 2;

        public $attach;

	/**
	 * Переменная заведена для того чтобы
	 * получить count(*) as num
	 * через дата провайдер
	 * сейчас используется в модели
	 * Spam
	 * @var
	 */
	public $num;

	public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onAfterSave = array($this, 'recipientNotifier');
		$this->onAfterSave = array($this, 'updateSphinx');
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
        
        
        public function recipientNotifier()
        {
                if (!$this->isNewRecord)
                        return;
                
                if($this->recipient->data->notice_private_message != 1)
                        return;

                Yii::app()->mail->create('newMessage')
                        ->to($this->recipient->email)
                        ->params(array(
                                'user_name' => $this->recipient->name,
                                'author_name' 	=> $this->author->name,
                                'message_link' => 'member/message/show/id/'.$this->id,
                                'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                        ))
                        ->send();
        }
	
	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:user_message', $this->id);
	}

        /**
         * Returns the static model of the specified AR class.
         * @return MsgBody the static model class
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
                return 'msg_body';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('author_id, recipient_id, message', 'required'),
                    array('author_id, author_status, recipient_status, create_time, update_time, num', 'numerical', 'integerOnly' => true),
                    array('message', 'length', 'max' => 3000),
                    array('recipient_id', 'otherUser', 'on' => 'create'),
		    array('recipient_id', 'exist', 'message' => 'Некорректно указан получатель', 'className' => 'User', 'attributeName' => 'id', 'on' => 'create'),
                    array('attach', 'file', 'types' => 'jpg, bmp, png, zip, pdf', 'maxFiles' => 10, 'maxSize' => 104857600000, 'allowEmpty' => true),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, author_id, recipient_id, author_status, recipient_status, message, create_time, update_time, num', 'safe', 'on' => 'search'),
                );
        }

        /**
         * Валидатор проверяет получателя. Получатель не может быть равен автору.
         */
        public function otherUser($attribute, $params)
        {
                if ($this->recipient_id == Yii::app()->user->id)
                        $this->addError('recipient_id', 'Нельзя указать себя как получателя');
        }

        /**
         * @return array relational rules.
         */
        public function relations()
        {
                // NOTE: you may need to adjust the relation name and the related
                // class name for the relations automatically generated below.
                return array(
                    'author' => array(self::BELONGS_TO, 'User', 'author_id'),
                    'recipient' => array(self::BELONGS_TO, 'User', 'recipient_id'),
                    'uploadedFiles' => array(self::MANY_MANY, 'UploadedFile', 'msg_file(msg_body_id, uploaded_file_id)'),
		    'spam' => array(self::HAS_ONE, 'Spam', 'msg_id'),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
                    'id' => 'ID',
                    'author_id' => 'Author',
                    'recipient_id' => 'Recipient',
                    'author_status' => 'Author Status',
                    'recipient_status' => 'Recipient Status',
                    'message' => 'Сообщение',
                    'create_time' => 'Create Time',
                    'update_time' => 'Update Time',
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

                $criteria = new CDbCriteria;

                $criteria->compare('id', $this->id);
                $criteria->compare('author_id', $this->author_id);
                $criteria->compare('recipient_id', $this->recipient_id);
                $criteria->compare('author_status', $this->author_status);
                $criteria->compare('recipient_status', $this->recipient_status);
                $criteria->compare('message', $this->message, true);
                $criteria->compare('create_time', $this->create_time);
                $criteria->compare('update_time', $this->update_time);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

        /**
         * Возвращает получателя при ответе в цепочку сообщений
         */
        public function getRecipient()
        {
                if ($this->author_id == Yii::app()->user->id)
                        return $this->recipient_id;
                else
                        return $this->author_id;
        }

        /**
         * Проставляет всем сообщениям указанной цепочки статус "ПРОЧИТАНО" для текущего пользователя
         */
        public function setReadStatus()
        {
                $bodys = self::model()->findAll('author_id = :author AND recipient_id = :user AND recipient_status = :status', array(
                    ':author' => $this->author_id,
                    ':user' => Yii::app()->user->id,
                    ':status' => self::STATUS_UNREAD
                        )
                );
                foreach ($bodys as $body) {
                        $body->recipient_status = self::STATUS_READ;
                        $body->save();
                }
                return true;
        }

        /**
         * Функция сохраняет файлы текущего объекта
         * @return boolean
         */
        public function saveFiles()
        {
                // Сохранение файлов
                $this->attach = CUploadedFile::getInstances($this, 'attach');

                if (isset($this->attach) && count($this->attach) > 0 && $this->validate()) {
                        foreach ($this->attach as $key => $file) {

                                $formatName = time() . '_' . Amputate::rus2translit($file->getName());
				$path = Config::PM_UPLOAD_PATH;
				if (!file_exists($path))
					mkdir ($path, 0700, true);
                                if ($file->saveAs($path . '/' . $formatName)) {

                                        $uf = new UploadedFile();
                                        $uf->author_id = Yii::app()->user->id;
                                        $uf->path = Config::PM_UPLOAD_PATH;
                                        $uf->name = Amputate::getFilenameWithoutExt($formatName);
                                        $uf->ext = $file->getExtensionName();
                                        $uf->size = $file->getSize();
                                        $uf->type = UploadedFile::IMAGE_TYPE;
                                        if ($uf->save()) {
                                                $msg_file = new MsgFile();
                                                $msg_file->msg_body_id = $this->id;
                                                $msg_file->uploaded_file_id = $uf->id;
                                                $msg_file->save();
                                                continue;
                                        }
                                }
                        }
                }
                return false;
        }

	public static function newMessage($recipient = null, $message = null, $userId=null)
	{
		$recipient = User::model()->findByPk((int) $recipient);

		$userId = $userId===null ? Yii::app()->getUser()->getId() : intval($userId);
		$validate = ($userId===null);

		if ($recipient && $message) {

			$body = new self('create');
			$body->author_id = $userId;
			$body->recipient_id = $recipient->id;
			$body->author_status = self::STATUS_READ;
			$body->recipient_status = self::STATUS_UNREAD;
			$body->message = $message;
			return $body->save($validate);
		}

		return false;
	}

	/**
	 * Пометка сообщения, как прочитанного
	 */
	public function readMessage()
	{
		if ($this->recipient_status == self::STATUS_READ)
			return true;

		if ($this->recipient_status == self::STATUS_UNREAD) {
			$this->recipient_status = self::STATUS_READ;
			$this->save(false, array('recipient_status'));
			return true;
		}
		return false;
	}

}