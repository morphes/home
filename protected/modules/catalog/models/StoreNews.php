<?php

/**
 * This is the model class for table "cat_store_news".
 *
 * Класс обеспечивает поддержку новостей магазина.
 * У платных магазинов (некоторые тарифы) имеют возможность
 * писать новости.
 *
 * The followings are the available columns in table 'cat_store_news':
 * @property integer $id
 * @property integer $status
 * @property integer $user_id
 * @property integer $image_id
 * @property integer $store_id
 * @property double $rating
 * @property string $title
 * @property string $content
 * @property integer $create_time
 * @property integer $update_time
 */
class StoreNews extends CActiveRecord implements IUploadImage, IComment
{

	private $_imageType = null;

	const STATUS_NEW = 1;
	const STATUS_WAIT_MOD = 2;
	const STATUS_PUBLIC = 3;
	const STATUS_DELETE = 4;

	static public $statuses = array(
		self::STATUS_NEW      => 'Новая',
		self::STATUS_WAIT_MOD => 'Ожидает модерации',
		self::STATUS_PUBLIC   => 'Опубликована',
		self::STATUS_DELETE   => 'Удалена пользователем',
	);


	public static $preview = array(
		'crop_140'  => array(140, 140, 'crop', 80),
		'width_620' => array(620, 0, 'resize', 80, false),
	);

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
		);
	}



	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StoreNews the static model class
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
		return 'cat_store_news';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, store_id, title, content', 'required'),
			array('status, user_id, image_id, store_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('rating', 'numerical'),
			array('title', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, user_id, image_id, store_id, rating, title, content, create_time, update_time', 'safe', 'on'=>'search'),
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
			'author'  => array(self::BELONGS_TO, 'User', 'user_id'),
			'preview' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'store'   => array(self::BELONGS_TO, 'Store', 'store_id')
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
			'user_id'     => 'Автор',
			'image_id'    => 'Изображение',
			'store_id'    => 'ID магазина',
			'rating'      => 'Средний рейтинг',
			'title'       => 'Заголовок',
			'content'     => 'Текст новости',
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
		$criteria->compare('status',$this->status);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('rating',$this->rating);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();

		$sort->defaultOrder = array('create_time' => 'desc');

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort' => $sort
		));
	}

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'image': return 'storeNews/'.intval($this->id / UploadedFile::PATH_SIZE + 1).'/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'image': return time() . 'image_'. $this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->id;
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

	public function checkAccess()
	{
		return !is_null($this->id) && $this->user_id == Yii::app()->user->id;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'image': return array(
				'realtime' => array(
					self::$preview['crop_140'],
				),
				'background' => array(
					self::$preview['width_620'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
	// end of IUploadImage


	/**
	 * Возваращает статус для записи в виде html строки,
	 * с расцветкой взависимости от статуса.
	 *
	 * @return string
	 */
	public function getStatusHtml()
	{
		$html = '';
		if (isset(self::$statuses[$this->status])) {

			switch($this->status) {
				case self::STATUS_NEW:
					$cls = 'default';
					break;
				case self::STATUS_WAIT_MOD:
					$cls = 'warning';
					break;
				case self::STATUS_PUBLIC:
					$cls = 'success';
					break;
				case self::STATUS_DELETE:
					$cls = 'important';
					break;
				default:
					$cls = '';
			}
			$html .= CHtml::tag(
				'span',
				array(
					'class'   => 'item_status label ' . $cls,
					'data-id' => $this->id
				),
				self::$statuses[$this->status]
			);

		} else {
			$html .= 'N/A';
		}

		return $html;
	}


	/**
	 * Генерирует ссылки на список новостей магазина и на конкретную новость.
	 *
	 * @param Store   $store Экземпляр магазина
	 * @param string  $type  Строковый тип ссылки, которую нужно вернуть
	 * @param integer $newsId Идентификатор новости
	 *
	 * @return string ВОзвращает строку url
	 * @throws CHttpException
	 * 	404 - если не передан экземпляр магазазина
	 * 	500 - если передан неверный ID новости
	 * 	500 - если запрошен неверный тип ссылки
	 */
	static public function getLink($store, $type, $newsId = null)
	{
		if (!$store instanceof Store) {
			throw new CHttpException(400, 'Создание ссылки на новость магазина невозможно');
		}

		switch ($type) {
			case 'list':
				$url = preg_replace('/.*myhome(.*)/', 'http://' . $store->subdomain->domain . '.myhome$1', Yii::app()->homeUrl)
					. '/news';
				break;

			case 'element':
				$newsId = (int)$newsId;
				if ($newsId == 0) {
					throw new CHttpException(500, 'Некорректный ID новости магазина');
				}

				$url = preg_replace('/.*myhome(.*)/', 'http://' . $store->subdomain->domain . '.myhome$1', Yii::app()->homeUrl)
					. '/news/' . $newsId;
				break;

			default:
				throw new CHttpException(500, 'Запрошен неверный тип ссылки');
		}

		return $url;
	}


	/**
	 * Проверка отображения комментариев
	 * @return boolean
	 * @author Alexey Shvedov
	 */
	public function getCommentsVisibility()
	{
		return true;
	}

	/**
	 * Обработчик события комментирования текущего объекта.
	 * @param $comment Comment
	 * @return Array
	 */
	public function afterComment($comment)
	{
		/**
		 * Обновление кол-ва комментариев текущего объекта
		 */
		$count_comment = Comment::getCountComments($this);
		self::model()->updateByPk($this->id, array(
			'count_comment'  => $count_comment,
		));

		return array(0, $count_comment);
	}

	/**
	 * Проверка владения моделью
	 * @author Alexey Shvedov
	 */
	public function getIsOwner()
	{
		return $this->user_id === Yii::app()->user->id;
	}


	/**
	 * Возвращает ссылку на новость
	 *
	 * @return string
	 */
	public function getElementLink()
	{
		Yii::import('application.modules.catalog.models.Store');
		return StoreNews::getLink(Store::model()->findByPk($this->store_id), 'element', $this->id);
	}
}