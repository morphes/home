<?php

/**
 * Класс для хранения общих отзывов пользователей сайта
 *
 * The followings are the available columns in table 'feedback':
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $message
 * @property string $user_ip
 * @property string $user_agent
 * @property integer $create_time
 */
class Feedback extends EActiveRecord
{
	// Список полей, которые должны быть за encode'ны при присваивании значения
	protected $encodedFields = array('name', 'email', 'message');

        /**
         * @var string URL страницы, с которой отправлен feedback
         */
        public $page_url;

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Update create_time in object
	 */
	public function setDate()
	{
		if($this->isNewRecord)
			$this->create_time=time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Feedback the static model class
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
		return 'feedback';
	}

	public function behaviors()
	{
		return array(
			'CSafeContentBehavor' => array(
				'class' => 'application.components.CSafeContentBehavior',
				'attributes' => $this->encodedFields,
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
			array('name, message', 'required'),
                        array('email', 'required', 'message'=>'Необходимо заполнить поле "Адрес электронной почты"'),
			array('create_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
                        array('page_url', 'length'),
			array('email', 'length', 'max'=>50),
			array('message', 'length', 'max'=>4096),
			array('email', 'email'),
			//array('user_ip', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, email, message, user_ip, page_url, user_agent, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => '"Ваше имя"',
			'email' => 'Почтовый адрес',
			'message' => '"Ваш вопрос"',
			'user_ip' => 'User Ip',
			'user_agent' => 'User Agent',
			'create_time' => 'Create Time',
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
		$criteria->compare('email',$this->email,true);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('user_ip',$this->user_ip,true);
		$criteria->compare('user_agent',$this->user_agent,true);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function saveFile($fileInstance)
	{
		if ($this->getIsNewRecord())
			throw new CException('Unsaved model');

		$file = new UploadedFile('document');

		$file->author_id = intval( Yii::app()->getUser()->getId() );
		$file->path = 'feedback/'.intval($this->id / UploadedFile::PATH_SIZE + 1).'/'.$this->id;
		$file->ext = $fileInstance->extensionName;
		$file->size = $fileInstance->size;
		$file->type = UploadedFile::DOCUMENT_TYPE;

		$filename = 'feedback_'.$this->id.'_'.mt_rand(100, 999);
		$path = UploadedFile::UPLOAD_PATH .'/'.$file->path;

		$cnt = 0;
		while (file_exists($path . '/' . $filename . '.' . $file->ext) && $cnt < 50) {
			$filename .= mt_rand(100, 999);
			$cnt++;
		}
		if ($cnt==50)
			return false;

		$file->name = $filename;
		$folder = UploadedFile::UPLOAD_PATH . '/' . $file->path;

		if (!file_exists($folder))
			mkdir($folder, 0700, true);
		
		if($file->save()){
			$fileInstance->saveAs($folder . '/' . $file->name . '.' . $file->ext);

			/** @var $rel FeedbackFile */
			$rel = new FeedbackFile();
			$rel->feedback_id = $this->id;
			$rel->file_id = $file->id;
			$rel->save(false);
		}
		return true;
	}

	/**
	 * Отправка на email
	 */
	public function sendMail()
	{
		$files = FeedbackFile::model()->findAllByAttributes(array('feedback_id'=>$this->id));
		$fileLinks = '';
		if (empty($files)) {
			$fileLinks = 'Файлы отсутствуют';
		} else {
			$cnt = 1;
			/** @var $file FeedbackFile */
			foreach ($files as $file) {
				$fileLinks .= CHtml::tag('p', array(), CHtml::link('Файл '.$cnt, $file->getDownloadLink() ) );
				$cnt++;
			}
		}
		Yii::app()->mail->create('Feedback')
			->to(Yii::app()->params->feedbackEmail)
            ->subject("MyHome.ru. Обратная связь #".$this->id)
			->params(array(
			'name' 	  => $this->name,
			'email'   => $this->email,
			'message' => $this->message,
			'user_ip' => $this->user_ip,
			'user_agent' => $this->user_agent,
			'file_links' => $fileLinks,
                        'page_url' => $this->page_url,
		))
		->send();
	}
}