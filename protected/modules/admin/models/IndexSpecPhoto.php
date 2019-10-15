<?php

/**
 * This is the model class for table "index_spec_photo".
 *
 * The followings are the available columns in table 'index_spec_photo':
 * @property integer $id
 * @property integer $image_id
 * @property integer $model_id
 * @property integer $status
 * @property string $name
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexSpecPhoto extends CActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	static public $statusName = array(
		self::STATUS_ACTIVE   => 'активен',
		self::STATUS_INACTIVE => 'скрыт'
	);

	// Набор табов для фотографий.
	public $blockIds;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IndexSpecPhoto the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function afterFind()
	{
		$this->blockIds = array_keys($this->blocks);
		parent::afterFind();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'index_spec_photo';
	}

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
		);
	}

	public function init()
	{
		$this->onAfterSave = array($this, 'saveBlocks');
		$this->onAfterSave = array($this, 'clearCache');

		parent::init();
	}

	/**
	 * Удаляет из кеша данные по фоткам.
	 */
	public function clearCache()
	{
		Yii::app()->cache->delete(
			IndexSpecPhoto::getCacheKeySpec()
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
			array('image_id, model_id, status, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, image_id, model_id, status, name, create_time, update_time', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'User', 'model_id'),
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'blocks' => array(self::MANY_MANY, 'IndexSpecBlock', 'index_spec_photo_block(photo_id, block_id)', 'index' => 'id'),
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
			'model_id'    => 'ID пользователя',
			'status'      => 'Статус',
			'name'        => 'Имя',
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


		if (($block_id = (int)Yii::app()->request->getParam('block_id'))) {
			$sql = 'SELECT photo_id FROM index_spec_photo_block WHERE block_id = :bid';
			$ids = Yii::app()->db->createCommand($sql)->bindValue(':bid', $block_id)->queryColumn();
			if ($ids) {
				$criteria->compare('id', $ids);
			}
		}


		$criteria->compare('image_id',$this->image_id);

		if ($this->model_id) {
			$criteria->compare('model_id', explode(',', $this->model_id));
		}

		$criteria->compare('status',$this->status);
		$criteria->compare('name',$this->name,true);


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
	 * Возвращает путь по которому будут сохраняться изображения для промоблока
	 * @return string
	 */
	public static function getImagePath()
	{
		return '/uploads/public/indexpage/specialist';
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

	/**
	 * Сохраняет список указанных блоков (ссылок) для текущей фотографии.
	 * Данные по блокам берутся из $_POST['IndexSpecPhoto']['blockIds']
	 */
	public function saveBlocks()
	{

		$blockIds = isset($_POST['IndexSpecPhoto']['blockIds'])
			? $_POST['IndexSpecPhoto']['blockIds']
			: array();

		if ( ! empty($blockIds)) {

			Yii::app()->db
				->createCommand('DELETE FROM index_spec_photo_block WHERE photo_id = :phid')
				->bindValue(':phid', $this->id)
				->execute();


			foreach ($blockIds as $tid) {
				Yii::app()->db
					->createCommand("INSERT INTO index_spec_photo_block (`photo_id`, `block_id`) VALUES ('".$this->id."', '".intval($tid)."')")
					->execute();
			}
		}
	}

	static public function getPhotos($clearCache = false)
	{

		$ideas = Yii::app()->cache->get(IndexSpecPhoto::getCacheKeySpec		());

		if ($clearCache == true) {
			$ideas = false;
		}

		if (!$ideas) {
			/** @var $res IndexIdeaPhoto[] */
			$res = IndexSpecPhoto::model()->findAllByAttributes(array(
				'status' => IndexSpecPhoto::STATUS_ACTIVE
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
					//'idea_url'    => '/idea/interior/' . $item->model_id,
					'img_src'     => $item->getImageFullPath(),
					'author_role' => $item->author->role,
					'author_url'  => $item->author->getLinkProfile(),
					'author_name' => $item->author->name,
					'author_img'  => $item->author->getPreview(User::$preview['crop_23']),
				);
			}


			Yii::app()->cache->set(IndexSpecPhoto::getCacheKeySpec(), $ideas, 600);
		}

		return $ideas;
	}

	/**
	 * Возваращает ключ кэша для блока специалистов на главной странице.
	 *
	 * @return string
	 */
	static public function getCacheKeySpec()
	{
		return 'INDEX:SPEC:BLOCK';
	}
}