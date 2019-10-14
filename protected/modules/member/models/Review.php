<?php

/**
 * This is the model class for table "review".
 *
 * The followings are the available columns in table 'review':
 * @property integer $id
 * @property integer $status
 * @property integer $type
 * @property integer $spec_id
 * @property integer $user_id
 * @property string  $message
 * @property integer $create_time
 * @property integer $update_time
 */
class Review extends EActiveRecord implements IActivity, IUploadImage
{
	const STATUS_SHOW = 1;
	const STATUS_DELETED = 2;
	const STATUS_HIDE = 3;

	// оценка отзыва
	const RATING_PLUS = 1;
	const RATING_MINUS = 2;
	const RATING_RECOMMEND = 3;

	const TYPE_REVIEW = 1; // Отзыв на спеца
	const TYPE_ANSWER = 2; // Ответ спеца на отзыв

	public static $statusNames = array(
		self::STATUS_SHOW    => 'Показан',
		self::STATUS_HIDE    => 'Скрыт',
		self::STATUS_DELETED => 'Удален',
	);

	public static $typeNames = array(
		self::TYPE_REVIEW => 'Отзыв',
		self::TYPE_ANSWER => 'Ответ на отзыв',
	);

	public static $ratingNames = array(
		self::RATING_PLUS      => 'Положительный',
		self::RATING_MINUS     => 'Отрицательный',
		self::RATING_RECOMMEND => 'Рекомендация',
	);

	private $_answer = false;
	private $_author = null;
	private $_specialist = null;

	public $reviewFile;
	public $attach;
	public $spec_login;
	public $author_login;

	// Список полей, которые должны быть за encode'ны при присваивании значения
	protected $encodedFields = array('message');

	public static $preview = array(
		'crop_60'  => array(60, 60, 'crop', 80),
		'crop_520' => array(520, 345, 'crop', 80),
	);

	// Тип изображения для загрузки
	private $_imageType = null;


	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		// Создание/удаление активности  юзера
		$this->onAfterSave = array($this, 'updateActivity');
		$this->onAfterSave = array($this, 'updateUserData');
		$this->onAfterSave = array($this, 'statusUpdate');
	}


	/**
	 * Установка того же статуса для ответа
	 */
	public function statusUpdate()
	{
		if ($this->type == self::TYPE_REVIEW) {
			Yii::app()->db->createCommand()->update('review', array(
				'status' => $this->status
			), 'parent_id=:parent_id AND type=:type', array(':parent_id' => $this->id, ':type' => self::TYPE_ANSWER));
		}
	}


	/**
	 * Обновление числа отзывов для пользователей
	 */
	public function updateUserData()
	{
		$sql = 'SELECT AVG(rating) FROM review WHERE type=' . self::TYPE_REVIEW . ' AND status=' . self::STATUS_SHOW . ' AND spec_id=' . $this->spec_id;
		$statistic = Yii::app()->db->createCommand($sql)->queryScalar();

		$sqlCount = 'SELECT count(*) FROM review WHERE type=' . self::TYPE_REVIEW . ' AND status=' . self::STATUS_SHOW . ' AND spec_id=' . $this->spec_id;
		$count = Yii::app()->db->createCommand($sqlCount)->queryScalar();

		Yii::app()->db->createCommand()->update('user_data', array(
			'average_rating'      => intval($statistic),
			'review_count' => intval($count),
		), 'user_id=:uid', array(':uid' => $this->spec_id));

		Yii::app()->gearman->appendJob('userService', array('userId'=>$this->spec_id));
	}


	public function updateActivity()
	{
		if ($this->status == self::STATUS_SHOW) {
			if ($this->type == self::TYPE_REVIEW)
				Activity::createActivity($this);
		} else
			Activity::deleteActivity($this);
	}


	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}


	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Review the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}


	public function behaviors()
	{
		return array(
			'CSafeContentBehavor' => array(
				'class'      => 'application.components.CSafeContentBehavior',
				'attributes' => $this->encodedFields,
				'options'    => array(
					'HTML.AllowedElements' => array('br' => true),
				),
			),
		);
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'review';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, author_id, spec_id, message', 'required'),
			array('rating', 'required', 'message' => 'Необходимо поставить оценку'),
			array('status, type, rating, spec_id, author_id', 'numerical', 'integerOnly' => true),
			array('message', 'length', 'max' => 5000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, type, ratting, spec_id, author_id, message, spec_login, author_login', 'safe', 'on' => 'search'),
			array('reviewFile', 'file', 'types' => 'jpg, gif, png', 'allowEmpty' => true),
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
			'author_id'   => 'ID автора',
			'spec_id'     => 'Специалист',
			'type'        => 'Тип',
			'message'     => 'Отзыв',
			'rating'      => 'Оценка',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'spec_login' => 'Логин специалиста',
			'author_login' => 'Логин автора',
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
		$criteria->compare('status', $this->status);
		$criteria->compare('type', $this->type);
		$criteria->compare('spec_id', $this->spec_id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('message', $this->message, true);
		$criteria->compare('create_time', $this->create_time);
		$criteria->compare('update_time', $this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}


	/**
	 * Получение объекта ответа на отзыв
	 */
	public function getReviewAnswer()
	{
		if ($this->_answer === false) {
			$this->_answer = self::model()->findByAttributes(array('parent_id' => $this->id, 'status' => self::STATUS_SHOW, 'type' => self::TYPE_ANSWER));
		}

		return $this->_answer;
	}


	/**
	 * Проверка доступа к отзыву
	 */
	public function checkAccess()
	{
		$uid = Yii::app()->getUser()->getId();

		return !is_null($uid) && $this->author_id == $uid;
	}


	/**
	 * Проверка, оставлял ли пользователь отзыв на спеца(или ответ на отзыв в своем профиле)
	 * @param $specialistId ID специалиста, на которого оставляется отзыв
	 * @param $userId       ID пользователя, оставляющего отзыв
	 */
	public static function hasReview($specialistId, $userId)
	{
		if (is_null($specialistId) || is_null($userId))
			throw new CException('Invalid user id');

		if ($specialistId == $userId)
			throw new CException('Нельзя оставить отзыв на себя');

		$criteria = new CDbCriteria();
		$criteria->condition = 'author_id = :uid AND parent_id = 0 AND status=:status AND spec_id=:spec';
		$criteria->limit = 1;
		$criteria->params = array(':uid' => $userId, ':status' => Review::STATUS_SHOW, ':spec' => $specialistId);

		return (bool)Review::model()->count($criteria);
	}


	/**
	 * Проверка наличия ответа на отзыв
	 * @static
	 *
	 * @param $reviewId
	 * @param $userId
	 *
	 * @return boolean
	 * @throws CException
	 */
	public static function hasReviewAnswer($reviewId, $userId)
	{
		if (is_null($userId))
			throw new CException('Invalid user id');

		return (bool)Review::model()->countByAttributes(array('author_id' => $userId, 'parent_id' => $reviewId, 'type' => self::TYPE_ANSWER, 'status' => Review::STATUS_SHOW));
	}


	/**
	 * Получение автора отзыва
	 * @return CActiveRecord|null
	 */
	public function getAuthor()
	{
		if (is_null($this->_author)) {
			$this->_author = User::model()->findByPk($this->author_id);
		}

		return $this->_author;
	}


	/**
	 * @return CActiveRecord | null
	 */
	public function getSpecialist()
	{
		if (is_null($this->_specialist)) {
			$this->_specialist = User::model()->findByPk($this->spec_id);
		}

		return $this->_specialist;
	}


	/**
	 * Возвращает фрагмент активности для вывода
	 * @return string
	 */
	public function renderActivityItem($user)
	{
		return Yii::app()->getController()->renderPartial('//member/profile/_activity/_review', array('review' => $this, 'user' => $user), true);
	}


	/**
	 * Получение ID автора
	 * @return mixed
	 */
	public function getAuthorId()
	{
		return $this->author_id;
	}


	/**
	 * Получение ссылки на отзыв в профиле спеца
	 */
	public function getReviewLink()
	{
		$specialist = User::model()->findByPk($this->spec_id);
		if (is_null($specialist))
			return '';

		return Yii::app()->getController()->createUrl('/member/review/list', array('login' => $specialist->login));
	}


	// IUploadImage
	public function getImagePath()
	{
		if ($this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'review':
				return 'review/' . intval($this->id / UploadedFile::PATH_SIZE + 1) . '/' . $this->id;
			default:
				throw new CException('Invalid upload image type');
		}
	}


	public function getImageName()
	{
		if ($this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'review':
				return time() . '_' . rand() . '_' . $this->id;

			default:
				throw new CException('Invalid upload image type');
		}
	}


	public function setImageType($name)
	{
		$this->_imageType = $name;
	}


	public function flushImageType()
	{
		$this->_imageType = null;

		return true;
	}


	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'review':
				return array(
					'realtime'   => array(
						self::$preview['crop_60'],
					),
					'background' => array(
						self::$preview['crop_520'],
					),
				);
			default:
				throw new CException('Invalid upload image type');
		}
	}


	/**
	 * Метод возвращает список ID фотографий коммента
	 * @return bool
	 */
	public function getImagesId()
	{
		$result = Yii::app()->db->createCommand('SELECT file_id FROM `review_uploadedfile` WHERE item_id = :itemId')
			->bindValue(':itemId', $this->id)
			->queryColumn();
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}


	/**
	 * Возвращает превью
	 * @param $id
	 * @param $config
	 *
	 * @return bool
	 */
	public function getPreview($id, $config)
	{
		$key = 'Review:preview:' . $id . ':' . serialize($config);
		$preview = Yii::app()->cache->get($key);
		if (!$preview) {
			$uploadedFile = UploadedFile::model()->findByPk($id);
			if (!is_null($uploadedFile)) {
				$preview = $uploadedFile->getPreviewName($config, 'review');
			} else {
				return false;
			}
			Yii::app()->cache->set($key, $preview, Cache::DURATION_IMAGE_PATH);
		}

		return $preview;
	}


	// end of IUploadImage

	/**
	 * Метод возвращает тектовое
	 * соответствие рейтинга
	 * @return string
	 */
	public function getNameRating()
	{
		switch ($this->rating) {
			case 1:
				return 'Не рекомендую';
			case 2:
				return 'Плохо';
			case 3:
				return 'Нормально';
			case 4:
				return 'Хорошо';
			case 5:
				return 'Рекомендую';
			default:
				return 'Нормально';
		}
	}
}