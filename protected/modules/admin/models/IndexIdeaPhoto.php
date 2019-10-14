<?php

/**
 * This is the model class for table "index_idea_photo".
 *
 * На главной странице в блоке Идеи и архитектура фотографии.
 *
 * The followings are the available columns in table 'index_idea_photo':
 * @property integer $id
 * @property integer $image_id
 * @property integer $model_id
 * @property integer $status
 * @property string $name
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexIdeaPhoto extends CActiveRecord
{

	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	static public $statusName = array(
		self::STATUS_ACTIVE   => 'активен',
		self::STATUS_INACTIVE => 'скрыт'
	);

	static public $preview = array(
		'crop_300x220' => array(300, 220, 'crop', 80), // на главной странице в блоке Идеи
		'resize_540' => array('540', '540', 'resize', 80)
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
	 * @return IndexIdeaPhoto the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function init()
	{
		$this->onAfterSave = array($this, 'clearCache');

		parent::init();
	}

	/**
	 * Удаляет из кеша данные по фоткам.
	 */
	public function clearCache()
	{
		Yii::app()->cache->delete(
			IndexIdeaPhoto::getCacheKeyIdeas()
		);
	}
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'index_idea_photo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, model_id, status', 'required'),
			array('image_id, model_id, status, user_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, image_id, model_id, status, name, user_id, create_time, update_time', 'safe', 'on'=>'search'),
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
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'author' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'image_id'    => 'Изображение',
			'model_id'    => 'ID модели',
			'status'      => 'Статус',
			'name'        => 'Название',
			'user_id'     => 'Автор',
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
		$criteria->compare('image_id',$this->image_id);


		if ($this->model_id) {
			$criteria->compare('model_id', explode(',', $this->model_id));
		}

		$criteria->compare('status',$this->status);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('user_id',$this->user_id);


		// Дата начала обновления
		if (($update_from = Yii::app()->request->getParam('update_from'))) {
			$criteria->compare('update_time', '>=' . strtotime($update_from));
		}

		// Дата окончания обновления
		if (($update_to = Yii::app()->request->getParam('update_to'))) {
			$criteria->compare('update_time', '<' . strtotime('+1 day', strtotime($update_to)));
		}


		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Возвращает список фотографий для главной страницы.
	 *
	 * @param $clearCache ФЛаг очистки кеша
	 *
	 * @return array
	 */
	static public function getPhotos($clearCache = false)
	{

		$ideas = Yii::app()->cache->get(IndexIdeaPhoto::getCacheKeyIdeas());

		if ($clearCache == true) {
			$ideas = false;
		}

		if (!$ideas) {
			/** @var $res IndexIdeaPhoto[] */
			$res = IndexIdeaPhoto::model()->findAllByAttributes(array(
				'status' => IndexIdeaPhoto::STATUS_ACTIVE
			));

			if ($res) {
				shuffle($res);
			}


			$ideas = array();
			for ($i = 0; $i < 6; $i++) {

				if (isset($res[$i])) {
					$item = $res[$i];
				} else {
					break;
				}

				$ideas[] = array(
					'idea_name'   => $item->name,
					'idea_url'    => '/idea/interior/' . $item->model_id,
					'img_src'     => $item->getImageFullPath(),
					'author_role' => $item->author->role,
					'author_url'  => $item->author->getLinkProfile(),
					'author_name' => $item->author->name,
					'author_img'  => $item->author->getPreview(User::$preview['crop_23']),
				);
			}


			Yii::app()->cache->set(IndexIdeaPhoto::getCacheKeyIdeas(), $ideas, 600);
		}

		return $ideas;
	}


	/**
	 * Возвращает ключ для хранения списка идей для главной страницы сайта
	 *
	 * @return string
	 */
	private static function getCacheKeyIdeas()
	{
		return 'INDEX:IDEAS:BLOCK';
	}



	/**
	 * Возвращает путь по которому будут сохраняться изображения для промоблока
	 * @return string
	 */
	public static function getImagePath()
	{
		return '/uploads/public/indexpage/idea';
	}


	/**
	 * Возваращает полный путь до картинки.
	 *
	 * @return string
	 */
	public function getImageFullPath()
	{
		$image = $this->image;
		return self::getImagePath() . '/' . $image->name . '.' . $image->ext;
	}


	/**
	 * Возваращает статус для записи в виде html строки,
	 * с расцветкой взависимости от статуса.
	 *
	 * @return string
	 */
	public function getStatusHtml()
	{
		$html = '';
		if (isset(self::$statusName[$this->status])) {

			switch($this->status) {
				case self::STATUS_ACTIVE:
					$cls = 'success';
					break;
				case self::STATUS_INACTIVE:
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
				self::$statusName[$this->status]
			);

		} else {
			$html .= 'N/A';
		}

		return $html;
	}
}