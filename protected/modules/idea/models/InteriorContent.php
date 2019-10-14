<?php

/**
 * This is the model class for table "interior_content".
 *
 * The followings are the available columns in table 'interior_content':
 * @property integer $id
 * @property integer $author_id
 * @property integer $interior_id
 * @property integer $room_id
 * @property integer $color_id
 * @property integer $status
 * @property integer $style_id
 * @property integer $image_id
 * @property string $tag
 * @property integer $update_time
 * @property integer $create_time
 *
 * The followings are the available model relations:
 * @property Interior $interior
 * @property UploadedFile $image
 */
class InteriorContent extends EActiveRecord implements IUploadImage
{
        
	public $image_count = 0;
	
	// Список полей, которые должны быть за encode'ны при присваивании значения
	protected $encodedFields = array('tag');
	protected static $purifier = null;

	// Тип изображения для загрузки
	private $_imageType = null;

	/** @var null|false|UploadedFile */
	private $_image = false;
        
	/**
	 * Sphinx field weight for search in solution_content index
	 * @var array
	 */
	public static $sphinxWeight = array(
		'interior_name' => 120,
		'objecttype_name' => 70,
		'objecttype_desk' => 60,
		'room_name' => 70,
		'room_desc' => 60,
		'style_name' => 50,
		'style_desc' => 40,
		'color_name' => 30,
		'color_desc' => 20,
		'color_additional' => 20,
		'interiorcontent_tag' => 20,
		'interior_desc' => 10,
	);

	public static $preview = array(
		'crop_80' => array(80, 80, 'crop', 80), // in view
		'crop_150' => array(150, 150, 'crop', 80), // in update page
		'crop_140' => array(140, 140, 'crop', 80), // in update page
		'crop_210' => array(210, 210, 'crop', 80), // preview
		'crop_230' => array(230, 230, 'crop', 80), // in update page
		'resize_710x475' => array(710, 475, 'resize', 90), // in view
		'resize_1920x1080' => array(1920, 1080, 'resize', 90, 'watermark' => true, 'decrease' => true), // in view
		'width_520' => array(520, 0, 'resize', 90, false), // просмотр идеи на фронте
		'height_420' => array(0, 420, 'resize', 90, false), // вывод на главной товаров
	);


	public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'setAuthor');
                $this->onBeforeDelete = array($this, 'removeFiles');
		$this->onAfterDelete= array($this, 'updateSphinx');
        }
	
	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:interior_content', array('action'=>'delete', 'iContentId'=>$this->id) );
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
	 * Set author for new record
	 */
	public function setAuthor()
	{
		if ($this->isNewRecord) {
			$interior = Interior::model()->findByPk($this->interior_id, array('select' => 'author_id'));
			$this->author_id = $interior->author_id;
		}
	}

	public function behaviors(){
                return array(
                        'CSafeContentBehavor' => array( 
                                'class' => 'application.components.CSafeContentBehavior',
                                'attributes' => $this->encodedFields,
                        ),
                );
        }
	
	/**
	 * Remove uploaded files
	 */
	public function removeFiles()
	{
		if ($this->getIsNewRecord())
			return false;
                // Получать тип идеи из базы
		IdeaUploadedFile::model()->deleteAllByAttributes(array('item_id' => $this->id, 'idea_type_id'=>'1'));
		InteriorContentTag::model()->deleteAllByAttributes(array('interior_content_id'=>$this->id));
	}
        
        
	/**
	 * Returns the static model of the specified AR class.
	 * @return InteriorContent the static model class
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
		return 'interior_content';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('interior_id', 'required', 'on' => 'init'),

                    array('interior_id, style_id, color_id, room_id', 'required', 'on' => 'finished'),
		    array('image_id', 'required', 'message' => 'Необходимо добавить хотя бы одно изображение', 'on' => 'finished'),
                    array('interior_id', 'required', 'on' => 'failed'),
                    
                    array('id, interior_id, room_id, status, style_id, color_id, image_id, update_time, create_time', 'numerical', 'integerOnly' => true),
                
                    array('room_id', 'exist', 'attributeName' => 'id', 'className' => 'IdeaHeap'),
                    array('style_id', 'exist', 'attributeName' => 'id', 'className' => 'IdeaHeap'),
                    array('color_id', 'exist', 'attributeName' => 'id', 'className' => 'IdeaHeap'),
                    array('tag', 'length', 'tooLong' => 'Не более 3000 символов', 'max' => 3000),
		    array('color_id', 'checkInteriorColors', 'message' => 'Цвета помещения повторяются', 'on' => 'finished'),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, solution_id, room_id, status, style_id, color_id, image_id, tag, update_time, create_time', 'safe', 'on' => 'search'),
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
			'interior' => array(self::BELONGS_TO, 'Interior', 'interior_id'),
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
        {
                return array(
                    'id'		=> 'ID',
                    'solution_id'	=> 'Solution',
                    'room_id'		=> 'Помещение',
                    'status'		=> 'Status',
                    'style_id'		=> 'Стиль',
                    'color_id'		=> 'Цвет',
                    'image_id'		=> 'Обложка помещения',
                    'tag'		=> 'Теги',
                    'image_count'	=> 'Кол-во изображений',
                    'author'		=> 'Автор',
                    'solution_name'	=> 'Название работы',
                    'update_time'	=> 'Update Time',
                    'create_time'	=> 'Create Time',
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
		$criteria->compare('interior_id',$this->interior_id);
		$criteria->compare('room_id',$this->room_id);
		$criteria->compare('color_id',$this->color_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('style_id',$this->style_id);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('tag',$this->tag,true);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
         * Валидирует цвета помещения
         * 
         * @param string $attribute
         * @param array $params 
         */
        public function checkInteriorColors($attribute, $params)
        {
		if (is_null($this->color_id))
			return;
                $result = IdeaAdditionalColor::model()->exists('idea_type_id=:idea_type_id AND item_id=:item_id AND color_id=:color_id', array('idea_type_id' => Config::INTERIOR, 'item_id' => $this->id, 'color_id' => $this->color_id));
		if ($result)
			$this->addError('color_id', $params['message']);
        }
        
        /**
         * Check the user access to solution content
         */
        public function checkAccess()
        {
                return !is_null($this->author_id) && $this->author_id == Yii::app()->user->id;
        }

        /**
         * Создание нового помещения для заполнения данными
         * @param int $solution_id
         * @return SolutionContent 
         */
        static public function ajaxCreateRow($id)
        {
		
                $newContent = new InteriorContent('init');
                $newContent->interior_id = $id;
		$room_id = Yii::app()->request->getParam('room');
		if ($room_id)
			$newContent->room_id = (int)$room_id;
                
                if (!$newContent->save()) {
                        Yii::app()->end();
                }
                
                return $newContent;
        }


        /**
         * Сохранение формы помещения, полученной аяксом или POSTом
         * @param int $id
         * @return boolean
         */
        static public function SaveInteriorContent($id)
        {
                $interior_errors = array();
                $colors_errors = array();

		if (!empty($_POST['InteriorContent'])) {
                
			foreach ($_POST['InteriorContent'] as $key => $sc) {

				/** @var $interiorContent InteriorContent */
				$interiorContent = self::model()->findByPk((int) $sc['id'], 'interior_id = :sid', array(':sid' => $id));

                                if (!$interiorContent)
                                        return false;

				$colors_errors+= IdeaAdditionalColor::saveAdditionalColor($interiorContent->id, Config::INTERIOR);
				
                                $interiorContent->setScenario('finished');
                                $interiorContent->attributes = $sc;
                                if (!$interiorContent->save()) {
                                        $interior_errors[$key] = $interiorContent->getErrors();
                                        $interiorContent->setScenario('failed');
                                        $interiorContent->save();
                                }

				$interiorContent->setCover();
                                
                                
                                
                                continue;
                        }
                }
                
                if ($colors_errors || $interior_errors) {
                        return array(
                            'additional_colors' => $colors_errors,
                            'interior_contents' => $interior_errors
                        );
                } else {
                        return false;
                }
        }

        /**
         * Возвращает Solution и SolutionContent, проверяя возможности 
         * доступа текущим пользователем к интерфесу редактирования объектов
         * @param int $id
         */
        static public function getModelsByInteriorContent($id)
        {
                $interiorContent = self::model()->findByPk((int) $id);
		
                if ($interiorContent) {
                        $interior = Interior::model()->findByPk($interiorContent->interior_id, 'author_id = :uid', array(
                                    ':uid' => Yii::app()->user->id,
                                        )
                        );
                        if ($interior) {
                                return array('interior' => $interior, 'interiorContent' => $interiorContent);
                        }
                }
                return false;
        }
	
	/**
	 * По указанному $id получаем данные о конкретном помещении интерьера.
	 * @param interger $id
	 * @return array Включает id, name, картинки
	 */
	public static function getInteriorContentImagesById($id)
	{
		$arr_result = array();
		
		/* Получаем картинки */
		
		$images = IdeaUploadedFile::model()->findAll('item_id=:id AND idea_type_id=:typeId', array(':id' => $id, ':typeId' => Config::INTERIOR)); 
		$arr_temp = array();
		foreach($images as $image) {
			// Получаем данные о картинке по ID
			$img = UploadedFile::model()->findByPk($image->uploaded_file_id);
			$arr_temp[] = array(
			    'id'	=> $img->id,
			    'preview'	=> '/'.$img->getPreviewName(Config::$preview['crop_150'], 'interiorContent'),
			    'full'	=> '/'.$img->getPreviewName(Config::$preview['resize_710x475'], 'interiorContent'),
			);
		}
		// Добавляем в результуриющий массив картинки
		$arr_result = $arr_temp;
		
		return $arr_result;
	}
	
	/**
	 * Выдает массив всех фоток текущего помещения
	 * 
	 * @return array Массив UploadedFile'ов
	 */
	public function getAllPhotos()
	{
		$resultArrPhotos = array();
		
		/* Получаем картинки */
		
		$idea_uf = IdeaUploadedFile::model()->findAll('item_id=:id AND idea_type_id=:typeId', array(':id' => $this->id, ':typeId' => Config::INTERIOR)); 
		foreach($idea_uf as $image) {
			// Получаем данные о картинке по ID
			$uf = $image->uploadedFile;
			if ($uf)
				$resultArrPhotos[] = $uf;
		}
		
		return $resultArrPhotos;
	}
        
        /**
	 * Get image preview path
	 */
	public function getPreview($config)
	{
		if ($this->_image === false)
			$this->_image = UploadedFile::model()->findByPk($this->image_id);

		if (!is_null($this->_image)) {
			$preview = $this->_image->getPreviewName($config, 'interiorContent');
                        return $preview;
		}
		$name = $config[0].'x'.$config[1];

		return UploadedFile::getDefaultImage('interiorContent', $name);
	}
	
	/**
	 * Get room name for current interior content
	 * @return string
	 */
	public function getRoomName()
	{
		// TODO: cache result
		$room = IdeaHeap::model()->findByPk($this->room_id);
		if (is_null($room))
			return '';
		return $room->option_value;
	}

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'interiorContent': return 'idea/'.intval($this->author_id/UploadedFile::PATH_SIZE + 1).'/'.$this->author_id.'/interior/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'interiorContent': return 'interior'.$this->interior_id.'_'.mt_rand(0, 100).'_'.time();
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->author_id;
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
			case 'interiorContent': return array(
				'realtime' => array(
					self::$preview['crop_210'],
					self::$preview['crop_150'],
				),
				'background' => array(
					self::$preview['crop_80'],
					self::$preview['crop_230'],
					self::$preview['resize_710x475'],
					self::$preview['resize_1920x1080'],
					self::$preview['width_520'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
	// end of IUploadImage

	/** For new front */

	/**
	 * Find tags for current interior content
	 * @return array
	 */
	public function getTags()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN interior_content_tag as ict ON ict.tag_id=t.id';
		$criteria->join .= ' LEFT JOIN (SELECT ict2.tag_id, COUNT(*) as cnt'
			. ' FROM interior_content_tag ict2 GROUP BY ict2.tag_id) as tmp'
			. ' ON tmp.tag_id = t.id';
		$criteria->condition = 'ict.interior_content_id = :icid AND tmp.cnt > 5';
		$criteria->params = array(':icid'=>$this->id);

		return Tag::model()->findAll($criteria);
	}

	/**
	 * Colors id list
	 */
	public function getColorsList()
	{
		$sql = 'SELECT color_id FROM idea_additional_color as iac WHERE iac.idea_type_id='.Config::INTERIOR.' AND NOT ISNULL(iac.color_id) AND iac.item_id = '.$this->id;
		$result = Yii::app()->db->createCommand($sql)->queryColumn();
		return array_merge(array($this->color_id), $result);
	}

	/**
	 * Хеш помещения на странице
	 * @return string
	 */
	public function getHash()
	{
		return 'r_'.$this->id;
	}

	public function getPopupHash()
	{
		return 'room_id_'.$this->id;
	}

	/**
	 * Получение объектов на изображения помещения
	 * @return array
	 */
	public function getPhotos()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN idea_uploaded_file as iuf ON iuf.uploaded_file_id=t.id AND idea_type_id=:type';
		$criteria->condition = 'iuf.item_id=:id';
		//$criteria->select = 'DISTINCT *';
		$criteria->params = array(':type'=>Config::INTERIOR, ':id'=>$this->id);
		$criteria->index = 'id';
		return UploadedFile::model()->findAll($criteria);
	}

	/**
	 * Ссыдка на помещение на странице просмотра идеи
	 */
	public function getIdeaLink()
	{
		return '/idea/interior/' . $this->interior_id.'#'.$this->getHash();
	}


	/**
	 * Устанавливает обложку для идеи
	 */
	public function setCover()
	{
		$sql = 'SELECT uploaded_file_id FROM ' . IdeaUploadedFile::model()->tableName() . ' WHERE item_id = :itid AND idea_type_id = :type LIMIT 1';
		$ufId = Yii::app()->db
			->createCommand($sql)
			->bindValue(':itid', $this->id)
			->bindValue(':type', Config::INTERIOR)
			->queryScalar();

		if ($ufId) {
			Yii::app()->db
				->createCommand('UPDATE ' . InteriorContent::model()->tableName() . ' SET image_id = ' . (int)$ufId . ' WHERE id = ' . $this->id)
				->execute();
		}
	}

    /**
     * @param $interiorId
     * @return mixed
     */
    public static  function getStyleById($interiorId)
    {
        $sql = 'SELECT style_id FROM interior_content WHERE interior_id = :intId  GROUP BY style_id';
        $rez = Yii::app()->db
            ->createCommand($sql)
            ->bindValue(':intId', $interiorId)
            ->queryColumn();
        return $rez;
    }
}