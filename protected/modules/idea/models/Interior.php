<?php

/**
 * This is the model class for table "interior".
 *
 * The followings are the available columns in table 'interior':
 * @property integer $id
 * @property integer $author_id
 * @property integer $idea_type_id
 * @property integer $object_id
 * @property integer $service_id
 * @property integer $status
 * @property integer $image_id
 * @property string $name
 * @property string $desc
 * @property string $manufacturer
 * @property string $collection
 * @property integer $update_time
 * @property integer $create_time
 * @property integer $count_view
 * @property integer $count_photos
 * @property integer $count_comment
 *
 * The followings are the available model relations:
 * @property User $author
 * @property IdeaType $ideaType
 * @property InteriorContent[] $interiorContents
 */
class Interior extends EActiveRecord implements IUploadImage, IProject, IComment
{
	const REDIS_VIEW = 'interior:view:';
	/**
	 * Sphinx field weight for search in interior index
	 * @var array
	 */
	public static $sphinxWeight = array(
		'name' => 80,
		'rooms' => 40,
		'colors' => 40,
		'styles' => 40,
		'tags' => 30,
		'desc' => 20,
	);

	/** @var int позиция для вывода в портфолио */
	public $position = 0;


	/**
	 * Задаем типы посторек для интерьера. По этим типам
	 * потом различается вывод формы редактирования и назначение сценариев.
	 */
	const BUILD_TYPE_LIVE 		= 1; // Жилые помещения
	const BUILD_TYPE_PUBLIC 		= 2; // Общественные помещения


        protected $_oldStatus = null;
	
        const STATUS_MAKING = 1; // В процессе
        const STATUS_MODERATING = 2; // На модерации
        const STATUS_ACCEPTED = 3; // Принят в идеи
        const STATUS_REJECTED = 4; // Не допущен в идеи
        const STATUS_DELETED = 5; // Удален
        const STATUS_VERIFIED = 6; // Проверен Junior'ом
	const STATUS_CHANGED = 7; // Изменен после публикации

        // ID услуги Интерьеры (для подсветки в меню)
        const SERVICE_ID = 2;

	public static $preview = array(
		'crop_80' => array(80, 80, 'crop', 80), // in view
		'crop_150' => array(150, 150, 'crop', 80), // in update page
		'crop_180' => array(180, 180, 'crop', 80), // страница просмотра на фронте
		'crop_210' => array(210, 210, 'crop', 80), // preview
		'crop_230' => array(230, 230, 'crop', 80), // in update page
		'resize_710x475' => array(710, 475, 'resize', 90), // in view
		'resize_1920x1080' => array(1920, 1080, 'resize', 90, 'watermark' => true, 'decrease' => true), // in view
		'width_520' => array(520, 0, 'resize', 90, false), // просмотр идеи на фронте (layout)

		'crop_360x305' => array(380, 380, 'crop', 80), // New spec list
	);

        // Список имен статусов
        public static $statusNames = array(
            self::STATUS_MAKING => 'В процессе',
            self::STATUS_MODERATING => 'На модерации',
            self::STATUS_REJECTED => 'Не допущен в идеи',
            self::STATUS_VERIFIED => 'Проверен',
            self::STATUS_ACCEPTED => 'Опубликован',
	    self::STATUS_CHANGED => 'Опубликован, Изменен',
                // Статус скрыт, потомучто к нему нет доступа у пользователей
                //self::STATUS_DELETED	=> 'Удален'
        );
        public static $statusNamesForJuniors = array(
            self::STATUS_MAKING => 'В процессе',
            self::STATUS_MODERATING => 'На модерации',
            self::STATUS_REJECTED => 'Не допущен в идеи',
            self::STATUS_VERIFIED => 'Проверен',
                // Статус скрыт, потомучто к нему нет доступа у пользователей
                //self::STATUS_DELETED	=> 'Удален'
        );
	// Все возможные статусы
	public static $allStatusNames = array(
            self::STATUS_MAKING => 'В процессе',
            self::STATUS_MODERATING => 'На модерации',
            self::STATUS_REJECTED => 'Не допущен в идеи',
            self::STATUS_VERIFIED => 'Проверен',
            self::STATUS_ACCEPTED => 'Опубликован',
	    self::STATUS_CHANGED => 'Опубликован, Изменен',
            self::STATUS_DELETED	=> 'Удален'
        );
	
	const LAYOUT_NAME = 'Планировки';
	
	// Список полей, которые должны быть за encode'ны при присваивании значения
	protected $encodedFields = array('name', 'desc', 'manufacturer', 'collection');
	protected static $purifier = null;

	// Тип изображения для загрузки
	private $_imageType = null;

	private static $_service = null;

	/**
         * Выдает массив всех констант в случае, если не указан $status_id.
         * Иначе выдает название статуса $status_id.
         * 
         * @param integer $status_id id нужной константы
         * @return array Массив вида
         * 	array( "id_константы" => "имя_константы", ...)
         */
        public static function getStatusName($status_id = NULL)
        {
                switch (Yii::app()->user->getRole()) {
                        case User::ROLE_JUNIORMODERATOR :
                                return (!array_key_exists($status_id, self::$statusNamesForJuniors)) ? self::$statusNamesForJuniors : self::$statusNamesForJuniors[$status_id];
                                break;

                        default:
                                return (!array_key_exists($status_id, self::$statusNames)) ? self::$statusNames : self::$statusNames[$status_id];
                                break;
                }
        }

        /**
         * Contains images count in dependence solutionContent
         * @var integes
         */
        public $totalImages = 0;
        public $InteriorContentCount = 0;

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'setServiceId');
		$this->onAfterSave = array($this, 'initSorting');
                $this->onAfterSave = array($this, 'logging');
                $this->onAfterSave = array($this, 'updateProjectCountByService');
                $this->onAfterSave = array($this, 'countUserServiceRating'); // расчет рейтинга специалиста по текущей услуге
		$this->onAfterSave = array($this, 'updateSphinx');
        }

	/**
	 * Подключение сортировки проектов
	 */
	public function initSorting()
	{
		if (!$this->getIsNewRecord())
			return;

		$sorting = new PortfolioSort();
		$sorting->item_id = $this->id;
		$sorting->user_id = $this->author_id;
		$sorting->idea_type_id = Config::INTERIOR;
		$sorting->service_id = $this->service_id;
		$sorting->position = 1;
		$sorting->update_time = time();
		$sorting->save(false);

	}
        
        public function behaviors(){
                return array(
                        'CSafeContentBehavor' => array( 
                                'class' => 'application.components.CSafeContentBehavior',
                                'attributes' => $this->encodedFields,
                        ),
                );
        }
	
	public function updateSphinx()
	{
		//if ($this->status == self::STATUS_ACCEPTED || $this->status == self::STATUS_CHANGED)
		Yii::app()->gearman->appendJob('sphinx:user_login', $this->author_id);
			
		if ($this->status == self::STATUS_ACCEPTED || $this->status == self::STATUS_CHANGED) {
			Yii::app()->gearman->appendJob('sphinx:interior_content',
								array('interior_id'=>  $this->id, 'action'=>'update')
			    );
		} else {
			Yii::app()->gearman->appendJob('sphinx:interior_content',
								array('interior_id'=>  $this->id, 'action'=>'delete')
			    );
		}
	}

        /**
         * Обновление кол-ва проектов в текущей услуге
         */
        public function updateProjectCountByService()
        {
		$tmp1 = Interiorpublic::model()->scopeOwnPublic($this->author_id, $this->service_id)->count();
		$tmp2 = Interior::model()->scopeOwnPublic($this->author_id, $this->service_id)->count();
		$project_qt = $tmp1 + $tmp2;

                Yii::app()->db->createCommand()->update('user_service_data', array(
                        'project_qt'=>$project_qt,
                ), 'user_id=:uid AND service_id=:sid', array(':uid'=>$this->author_id, ':sid'=>$this->service_id));

                /**
                 * Если проект создан со статусом "в процессе", то наращивается счетчик черновиков
                 */
                if($this->isNewRecord && $this->status == self::STATUS_MAKING) {
                        $user = $this->author;
                        $user->data->draft_qt++;
                        $user->data->save();
                }
        }

        public function __set($name, $value)
        {
                $setter = 'set' . $name;
                if (method_exists($this, $setter))
                        return $this->$setter($value);
                else 
                        return parent::__set($name, $value);
        }

        public function setServiceId()
        {
                if($this->isNewRecord)
                        $this->service_id = self::SERVICE_ID;
        }

	/**
	 * Проставляет новые статусы при сохранении на фронте
	 */
	public function changedStatus()
	{
		if ($this->status == self::STATUS_MAKING 
			|| $this->status == self::STATUS_CHANGED 
			|| $this->status == self::STATUS_MODERATING
		)
			return;
		
		if ($this->status == self::STATUS_ACCEPTED)  {
			$this->status = self::STATUS_CHANGED;
		} else {
			$this->status = self::STATUS_MODERATING;
		}

		if ($this->reachedLimitReject()) {
			$this->status = self::STATUS_REJECTED;
		}
	}
	
        /**
         * Сохранение старого значения статуса интерьера и устанавливаем новое
         */
        public function setStatus($value)
        {
                $this->_oldStatus = $this->status;
                return parent::__set('status', $value);
        }
        
        public function logging()
        {
                if($this->isNewRecord || is_null($this->_oldStatus) || $this->_oldStatus == $this->status)
                        return false;
                
                Yii::app()->db->createCommand()->insert('interior_journal', array(
                    'interior_id'=>$this->id,
                    'user_id'=>Yii::app()->user->id,
                    'create_time'=>time(),
                    'key'=>InteriorJournal::LOG_STATUS,
                    'value'=>$this->status,
                ));
        }
	
	/**
	 * Подсчет кол-ва фотографий идеи и обновление параметра в БД
	 */
	public function countPhotos()
	{
		// В итоговую цифру попадает обложка
		if ($this->getIsNewRecord())
			return;
		
		
                // Ассоциативный массив array( array(<id interior_content> => <навзание room>),... )
                $icRooms = unserialize($this->rooms_list);

		// Количество фоток в помещении и планировок
		$count_content_photos = 0;
		$count_layout_photos = 0;
                if (is_array($icRooms) && !empty($icRooms)) {
                        // Получаем фотки помещений
                        $count_content_photos = IdeaUploadedFile::model()->countByAttributes(array(
				'item_id' => array_keys($icRooms),
				'idea_type_id' => Config::INTERIOR
                        ));
			$count_layout_photos = LayoutUploadedFile::model()->countByAttributes(array(
				'item_id' => $this->id,
				'idea_type_id' => Config::INTERIOR
			));
                }

                $count_photos = $count_content_photos + $count_layout_photos;
		
		Interior::model()->updateByPk($this->id, array('count_photos' => $count_photos));
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
         * @return Interior the static model class
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
                return 'interior';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('idea_type_id, author_id', 'required',
			    'on' => 'create_step_1'),
                    array('name, object_id, image_id, count_photos, author_id', 'required',
			    'on' => 'create_step_2'),
                    array('author_id, object_id, service_id, status, update_time, create_time, architecture_id', 'numerical', 'integerOnly' => true),
                    array('object_id', 'exist', 'attributeName' => 'id', 'className' => 'IdeaHeap'),
                    array('name, manufacturer, collection', 'length', 'max' => 255),
                    array('status', 'checkValidStatus'),
                    array('desc', 'length', 'max' => 5000),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, author_id, author, idea_type_id, object_id, service_id, status, name, desc, manufacturer, collection, update_time, create_time', 'safe',
			    'on' => 'search'),
                );
        }

        public function checkValidStatus($attribute, $params)
        {
                if (!array_key_exists($this->status, self::getStatusName()))
                        $this->addError('status', 'Некорректный статус');
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
                    'ideaType' => array(self::BELONGS_TO, 'IdeaType', 'idea_type_id'),
                    'interiorContents' => array(self::HAS_MANY, 'InteriorContent', 'interior_id'),
                    'service' => array(self::BELONGS_TO, 'Service', 'service_id'),
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
                    'author' => 'Автор',
                    'idea_type_id' => 'Вид идеи',
                    'object_id' => 'Тип объекта',
                    'status' => 'Статус',
                    'name' => 'Название проекта',
                    'desc' => 'Описание',
                    'manufacturer' => 'Производитель',
                    'collection' => 'Коллекция',
                    'update_time' => 'Update Time',
                    'create_time' => 'Create Time',
                    'totalImages' => 'Фотографий',
                    'InteriorContentCount' => 'Число помещений',
                    'image_id' => 'Обложка идеи'
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
                $criteria->compare('idea_type_id', $this->idea_type_id);
                $criteria->compare('object_id', $this->object_id);
                $criteria->compare('status', $this->status);
                $criteria->compare('image_id', $this->image_id);
                $criteria->compare('name', $this->name, true);
                $criteria->compare('desc', $this->desc, true);
                $criteria->compare('manufacturer', $this->manufacturer, true);
                $criteria->compare('collection', $this->collection, true);
                $criteria->compare('update_time', $this->update_time);
                $criteria->compare('create_time', $this->create_time);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

        /**
         * Именованное условие для выборки данных.
         * Выбирает интерьеры только с разрешенными статусами.
         * Если не передать $user_id, выбираются интерьеры для текущего
         * пользователя (Yii::app()->user)
         * 
         * @param interger $user_id Идентификатор пользователя
         * @return Interior 
         */
        public function scopeOwnPublic($user_id = null, $sid = null)
        {
		if (is_null($user_id))
			$user_id = Yii::app()->user->id;

		$params = array(
			':author_id' => $user_id,
			':st1' => self::STATUS_MODERATING,
			':st2' => self::STATUS_ACCEPTED,
			':st3' => self::STATUS_REJECTED,
			':st4' => self::STATUS_CHANGED,
			':st5' => self::STATUS_VERIFIED,
		);

		$criteria = new CDbCriteria();
		$criteria->order = 'create_time ASC';
		$criteria->limit = 1000;

		if (!$sid) {
			$criteria->condition = 'author_id=:author_id
                                        AND (status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5)';
		} else {
			$criteria->condition = 'author_id=:author_id AND service_id=:sid
                                        AND (status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5)';
			$params[':sid'] = intval($sid);
		}
		$criteria->params = $params;

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
        }

        /**
         * Все опубликованные проекты
         * @return Interior
         */
        public function scopeAllPublic()
        {
                $this->getDbCriteria()->mergeWith(array(
                        'condition' => 'status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5',
                        'params' => array(
                                ':st1' => Interior::STATUS_MODERATING,
                                ':st2' => Interior::STATUS_ACCEPTED,
                                ':st3' => Interior::STATUS_REJECTED,
                                ':st4' => Interior::STATUS_CHANGED,
                                ':st5' => Interior::STATUS_VERIFIED,
                        ),
                        'order' => 'create_time ASC',
                        'limit' => 1000,
                ));
                return $this;
        }

        /**
         * Именованное условие для выборки данных.
         * Выбирает интерьеры только со статусами
         * Если не передать $user_id, выбираются интерьеры для текущего
         * пользователя (Yii::app()->user)
         * 
         * @param interger $user_id Идентификатор пользователя
         * @return Interior 
         */
	public function scopeNonPublic($user_id = null)
        {
                if (is_null($user_id))
                        $user_id = Yii::app()->user->id;

                $this->getDbCriteria()->mergeWith(array(
                    'condition' => 'author_id=:author_id AND status = :st1',
                    'params' => array(
                        ':author_id' => $user_id,
                        ':st1' => Interior::STATUS_MAKING,
                    ),
                    'order' => 'create_time ASC',
                ));
                return $this;
        }

        /**
         * Check the user access to solution content
         * @author Alexey Shvedov
         */
        public function checkAccess()
        {
		return !is_null($this->author_id) && $this->author_id == Yii::app()->user->id;
        }

        /**
         * Get image preview path
         */
        public function getPreview($config)
        {
                $fileObject = UploadedFile::model()->findByPk($this->image_id);
                if (!is_null($fileObject)) {
                        $preview = $fileObject->getPreviewName($config, 'interior');
                        return $preview;
                }
                $name = $config[0] . 'x' . $config[1];
		        return UploadedFile::getDefaultImage('interior', $name);
        }

        /**
         * Выдает массив, содержащий данные по $count последним обнволявшимся
         * интерьерам пользователя $user_id.
         * В выдачу попадают все, не находящиеся в процессе создания, записи.
         * 
         * @param interger $user_id Идентификатор пользователя
         * @param integer $count Количество последних записей
         * @return array Массив со всеми необходимыми данными по интерьерам
         */
        static public function getLastByUserId($user_id, $count)
        {
                // Получаем список интерьеров
                $criteria = new CDbCriteria();
                $criteria->order = 'update_time DESC';
                $criteria->limit = 3;
                $criteria->offset = 0;

                $model = Interior::model()->scopeOwnPublic($user_id)->findAll($criteria);

                $arrResult = array();
                // Получаем данные, формируя массив на выдачу
                foreach ($model as $interior) {
                        // Считаем кол-во комментов
                        $cntComments = Comment::model()->countByAttributes(array('model' => get_class($interior), 'model_id' => $interior->id));

                        $arrResult[] = array(
                            'id' => $interior->id,
                            'name' => $interior->name,
                            'rooms' => array_values($interior->roomsNames),
                            'averageRating' => Comment::getAverageRating($interior),
                            'cntComments' => $cntComments,
                            'imagePreview' => $interior->getPreview(Config::$preview['crop_230']),
                        );
                }

                return $arrResult;
        }

        /**
         * Обнволяет у идеи список всех комнат, сохраняя key=>value массив в серриализованном
         * виде в поле интерьера.
         * Key это ID InteriorContent
         * Value это название помещения
         */
        public function updateRoomsList()
        {
                if ($this->getIsNewRecord())
                        throw new CException(__CLASS__ . ':' . __METHOD__ . ': Instance cannot be new');

                $arr_result = array();

                // Получаем список всех комнат
                $content = InteriorContent::model()->findAll('interior_id = :interior_id AND room_id is NOT NULL', array(':interior_id' => $this->id));

                // Получаем список названий комнат и формируем массив соответсвий id=>название
                $rooms = IdeaHeap::getRooms(Config::INTERIOR);
                $arr_rooms = CHtml::listData($rooms, 'id', 'option_value'); // Массив соответствий
                unset($rooms);

                foreach ($content as $item) {
                        $arr_result[$item->id]['room_name'] = $arr_rooms[$item->room_id];
                        $arr_result[$item->id]['count_photos'] = IdeaUploadedFile::model()->count('item_id=:rid AND idea_type_id=:iti', array(':rid'=>$item->id, ':iti'=>Config::INTERIOR));
                }

                // Обновляем
                Interior::model()->updateByPk($this->id, array('rooms_list' => serialize($arr_result)));
		$this->rooms_list = serialize($arr_result);
        }

        public function afterSave()
        {
                parent::afterSave();

                if (!$this->getIsNewRecord())
                        $this->updateRoomsList();

                //Обновляем кол-во фотографий у пользователя
		$this->countPhotos();

                // Обновление кол-ва проектов пользователя
		$project_quantity = Interior::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity += Interiorpublic::model()->scopeOwnPublic($this->author_id)->count();
                $project_quantity+= Portfolio::model()->scopeOwnPublic($this->author_id)->count();
                $project_quantity+= Architecture::model()->scopeOwnPublic($this->author_id)->count();
                UserData::model()->updateByPk($this->author_id, array('project_quantity' => $project_quantity));
        }
        
        
        static public function imageFormater($images = array(), $show_products = false)
        {
                if(!is_array($images))
                       $images = array($images); 
                
                $html = '';

                foreach($images as $img){
                        
                        if(!$img instanceof UploadedFile) continue;
                                       
                        $image = CHtml::image('/'.$img->getPreviewName(Config::$preview['crop_150'], 'interior'), '');
                        $src_big = '/'.$img->getPreviewName(Config::$preview['resize_710x475'], 'interior');

			// Показываем кол-во товаров, привязанных к элементу
			$cssBind = '';
			if ($show_products == true) {
				Yii::import('application.modules.catalog.models.ProductOnPhotos');

				$html .= ProductOnPhotos::getQntProducts($img->id);
				$cssBind = 'bind_products';
			}

                        $html.=Chtml::openTag('div',array('style'=>'position: relative; overflow: hidden; '));
				$link = CHtml::link( $image, $src_big, array( 'class' => 'preview '.$cssBind, 'data-file_id' => $img->id, 'title'=>$img->getOriginalImageSize() ) );
                                $html.=CHtml::tag('div', array('style'=>'float:left;'), $link, true);
                                $html.=CHtml::openTag('div', array('style'=>'position: absolute; top: 0pt; left: 0pt; padding-left: 170px; z-index: -1;'));
                                        $html.= CHtml::tag('strong', array(), 'Описание',true)."<br>";
                                        $html.= CHtml::value($img, 'desc');
                                        $html.="<br><br>";
                                        $html.= CHtml::tag('strong', array(), 'Ключевые слова',true)."<br>";
                                        $html.= CHtml::value($img, 'keywords');
                                $html.=CHtml::closeTag('div');
                        $html.= CHtml::closeTag('div');
                        $html.= CHtml::tag('div', array('style' => 'clear: both;margin-bottom: 10px;'), '', true);


                }

                return $html;
        }
	
	/**
	 * Проверка отображения комментариев
	 * @return boolean
	 * @author Alexey Shvedov
	 */
	public function getCommentsVisibility()
	{
		return $this->status != self::STATUS_MAKING;
	}

        /**
         * Ссылка на редактирование текущего проекта
         */
        public function getUpdateLink()
        {
                return Yii::app()->createUrl("/idea/create/interior/id/{$this->id}");
        }

        public function getDeleteLink()
        {
                return Yii::app()->createUrl("/idea/create/delete/id/{$this->id}");
        }

        /**
         * Вызов расчета рейтинга специалиста по текущей услуге
         */
        public function countUserServiceRating()
        {
		Yii::app()->gearman->appendJob('userService', array('userId'=>$this->author_id, 'serviceId'=>self::SERVICE_ID));
        }

        /**
         * Возвращает массив названий помещений, принадлежащих текущему интерьеру
         */
        public function getRoomsNames()
        {
                $list = unserialize($this->rooms_list);

                $result = array();

                foreach($list as $key=>$room) {
                        $result[$key] = $room['room_name'];
                }
                return $result;
        }
	
	/**
	 * Возвращает количество интерьеров (В каталоге идей)
	 */
	public static function getInteriorsQuantity()
	{
		$key = 'Interior::getInteriorsQuantity';
		$value = Yii::app()->cache->get($key);
		if (!$value) {
			$command = Yii::app()->db->createCommand();
			$command->from('interior');
			$command->where('interior.status=:status1 OR interior.status=:status2', 
				array(':status1' => self::STATUS_ACCEPTED, ':status2' => self::STATUS_CHANGED));
			$command->select('count(id)');
			$value = $command->queryScalar();
			Yii::app()->cache->set($key, $value, Cache::DURATION_REAL_TIME);
		}
		return $value;
	}
	
	/**
	 * Возвращает количество планировок 
	 */
	public function getLayoutsCount()
	{
		return LayoutUploadedFile::model()->countByAttributes(array('idea_type_id' => Config::INTERIOR, 'item_id' => $this->id));
	}
	
	/**
	 * Массив uploadedFile для данного интерьера 
	 */
	public function getLayouts()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN layout_uploaded_file as luf ON luf.uploaded_file_id=t.id';
		$criteria->condition = 'luf.idea_type_id=:type_id AND luf.item_id=:idea_id';
		$criteria->params = array(':type_id' => Config::INTERIOR, ':idea_id' => $this->id);
		$criteria->index = 'id';
		return UploadedFile::model()->findAll($criteria);
	}
	
	/**
         * Проверка владения моделью
         * @author Alexey Shvedov
         */
        public function getIsOwner()
        {
                return $this->author_id === Yii::app()->user->id;
        }

        /**
         * Обработчик события комментирования текущего объекта.
         * @param $comment Comment
         * @return Array
         */
        public function afterComment($comment)
        {
                /**
                 * Обновление рейтинга и кол-ва комментариев текущего объекта
                 */
                $count_comment = Comment::getCountComments($this);
                $average_rating = Voting::getAverageRating($this);
                self::model()->updateByPk($this->id, array(
                        'average_rating' => $average_rating,
                        'count_comment'  => $count_comment,
                ));

                /**
                 * Отправка уведомления о новом комментарии
                 */
                $author = User::model()->findByPk($this->author_id);
                $commentAuthor = User::model()->findByPk($comment->author_id);
                if (!is_null($author) && !is_null($commentAuthor) &&
                        $author->data->idea_comment_notify &&
                        in_array($author->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))
                ) {
                        Yii::app()->mail->create('interiorCommentNotify')
                                ->to($author->email)
                                ->params(array(
                                        'user_name' => $this->author->name,
                                        'project_name' => $this->name,
                                        'author_name' 	=> $commentAuthor->name,
                                        'project_link' => $this->getElementLink(),
                                        'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage()
                                ))
                                ->send();
                }

                return array($average_rating, $count_comment);
        }

	/**
	 * Миграция интерьера в архитектуру
	 */
	public function migrateToArchitecture()
	{
		// Получаем id тип сторения
		$object = IdeaHeap::model()->findByAttributes(array(
			'idea_type_id' 	=> Config::ARCHITECTURE,
			'parent_id' 	=> 0,
			'option_key' 	=> 'object',
			//'option_value' 	=> 'Дом, коттедж, особняк'
		));

		if (is_null($object))
			return false;

		$build = IdeaHeap::model()->findByAttributes(array(
			'idea_type_id' 	=> Config::ARCHITECTURE,
			'parent_id' 	=> $object->id,
			'option_key' 	=> 'building_type',
		));

		if (is_null($object))
			return false;

		$arch = new Architecture();

		if ($this->status == Interior::STATUS_MAKING)
			$arch->status = Architecture::STATUS_MAKING;
		else
			$arch->status = Architecture::STATUS_TEMP_IMPORT;

		$tagSql = 'SELECT GROUP_CONCAT(tag SEPARATOR ", ") as tag FROM `interior_content` WHERE interior_id='.$this->id.' GROUP BY interior_id';
		$tags = Yii::app()->db->createCommand($tagSql)->queryRow();

		$tags = Amputate::getLimb($tags['tag'], 2950, '');

		$arch->name 		= $this->name;
		$arch->desc 		= $this->desc;
		$arch->author_id 	= $this->author_id;
		$arch->object_id 	= $object->id;
		$arch->building_type_id = $build->id;
		$arch->tag		= $tags;

		if ( ! $arch->save()) {
			return false;
		}

		// Сохр главного изображения
		$mainImage = UploadedFile::model()->findByPk($this->image_id);
		if (!is_null($mainImage)) {
			$originImg = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.$mainImage->path.'/'.$mainImage->name.'.'.$mainImage->ext;
			if (file_exists($originImg)) {
				$file = new UploadedFile();
				$file->author_id = $arch->author_id;
				$file->path = 'idea'.'/'.intval($arch->author_id / UploadedFile::PATH_SIZE + 1).'/'.$arch->author_id.'/architecture/'.$arch->id;
				$file->name = 'architecture' . $arch->id . '_' . mt_rand(1, 99) . '_'. time();
				$file->ext = $mainImage->ext;
				$file->size = $mainImage->size;
				$file->type = UploadedFile::IMAGE_TYPE;
				$file->desc = $mainImage->desc;

				$folder = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.'idea'.'/'.intval($arch->author_id / UploadedFile::PATH_SIZE + 1).'/'.$arch->author_id.'/architecture/'.$arch->id;

				if ( ! file_exists($folder))
					mkdir($folder, 0700, true);

				if (
					copy($originImg, $folder.'/'.$file->name.'.'. $file->ext)
					&&
					$file->save()
				) {
						Architecture::model()->updateByPk($arch->id, array('image_id' => $file->id));
				} else {
					return false;
				}
			}
		}

		// Получение списка изображений
		$sql = 'SELECT uf.* FROM `uploaded_file` as uf '
			.'INNER JOIN `idea_uploaded_file` as iuf ON iuf.uploaded_file_id = uf.id '
			.'INNER JOIN `interior_content` as ic ON ic.id = iuf.item_id '
			.'WHERE iuf.idea_type_id='.Config::INTERIOR.' AND ic.interior_id='.$this->id;

		$oldFiles = Yii::app()->db->createCommand($sql)->queryAll();

		// Сохр оставшихся изображений
		$cnt = 0;
		foreach ($oldFiles as $oldFile) {
			$cnt++;
			$originImg = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.$oldFile['path'].'/'.$oldFile['name'].'.'.$oldFile['ext'];
			if (file_exists($originImg)) {
				$file = new UploadedFile();
				$file->author_id = $arch->author_id;
				$file->path = 'idea'.'/'.intval($arch->author_id / UploadedFile::PATH_SIZE + 1).'/'.$arch->author_id.'/architecture/'.$arch->id;
				$file->name = 'architecture' . $arch->id . '_' . $cnt . '_'. time();
				$file->ext = $oldFile['ext'];
				$file->size = $oldFile['size'];
				$file->type = UploadedFile::IMAGE_TYPE;
				$file->desc = $oldFile['desc'];

				$folder = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.'idea'.'/'.intval($arch->author_id / UploadedFile::PATH_SIZE + 1).'/'.$arch->author_id.'/architecture/'.$arch->id;

				if ( ! file_exists($folder))
					mkdir($folder, 0700, true);

				if (
					copy($originImg, $folder.'/'.$file->name.'.'. $file->ext)
					&&
					$file->save()
				) {
					$ideaUF = new IdeaUploadedFile();
					$ideaUF->item_id = $arch->id;
					$ideaUF->idea_type_id = Config::ARCHITECTURE;
					$ideaUF->uploaded_file_id = $file->id;

					$ideaUF->save();
				} else {
					return false;
				}
			}
		}
		return $arch->id;

	}

	/**
	 * Генерация ссылки на фильтр с параметрами
	 * @param $options
	 * @return string
	 */
	public function getFilterLink($options=null)
	{

		$options['filter'] = 1;
		$options['ideatype'] = Config::INTERIOR;
		$params = http_build_query($options);
		return '/idea/catalog/index?'.$params;
	}

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'interior': return 'idea/'.intval($this->author_id/UploadedFile::PATH_SIZE + 1).'/'.$this->author_id.'/interior/main';
			case 'layout': return 'idea/'.intval($this->author_id/UploadedFile::PATH_SIZE + 1).'/'.$this->author_id.'/interior/layouts/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'interior': return 'interior'.$this->id.'_'.time();
			case 'layout': return 'interior' . $this->id . '_'.mt_rand(0,100).'_' . time();
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
			case 'interior': return array(
				'realtime' => array(
					self::$preview['crop_210'],
					self::$preview['crop_150'],
				),
				'background' => array(
					self::$preview['crop_80'],
					self::$preview['crop_230'],
					self::$preview['resize_710x475'],
					self::$preview['resize_1920x1080'],
					self::$preview['crop_180'],
				),
			);
			case 'layout': return array(
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

	/**
	 * Название услуги проекта
	 * @return mixed
	 */
	public function getProjectType()
	{
		if (is_null(self::$_service)) {
			self::$_service = Service::model()->findByPk(self::SERVICE_ID);
		}
		return self::$_service->name;
	}

	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink()
	{
		return "/users/{$this->author->login}/project/{$this->service_id}/{$this->id}?t=1";
	}

        public function getIdeaLink()
        {
                return '/idea/interior/' . $this->id;
        }

	/**
	 * Увеличивает количество просмотров для указанного интерьера
	 * @static
	 * @param $interiorId
	 */
	public static function appendView($interiorId)
	{
		return Yii::app()->redis->incr(self::REDIS_VIEW.$interiorId);
	}

	public function getCountView()
	{
		return intval(Yii::app()->redis->get( self::REDIS_VIEW.$this->id) );
	}

	/**
	 * @return Массив id фотографий помещений
	 */
	public function getPhotosId()
	{
		$sql = 'select iuf.uploaded_file_id FROM idea_uploaded_file as iuf '
			.'INNER JOIN interior_content as ic ON ic.id=iuf.item_id '
			.'WHERE iuf.idea_type_id='.Config::INTERIOR.' AND ic.interior_id=:iid';
		$id = $this->id;
		return Yii::app()->db->createCommand($sql)->bindParam(':iid', $id)->queryColumn();
	}

	/**
	 * Получение системного типа объекта (например Config::INTERIOR)
	 * @return mixed
	 */
	public function getTypeId()
	{
		return Config::INTERIOR;
	}

	/**
	 * Получение объектов на изображения интерьера
	 * @return array
	 */
	public function getPhotos()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN idea_uploaded_file as iuf ON iuf.uploaded_file_id=t.id AND idea_type_id=:type';
		$criteria->join .= ' INNER JOIN interior_content as ic ON ic.id=iuf.item_id';
		$criteria->condition = 'ic.interior_id=:id';
		//$criteria->select = 'DISTINCT *';
		$criteria->params = array(':type'=>Config::INTERIOR, ':id'=>$this->id);
		$criteria->index = 'id';
		return UploadedFile::model()->findAll($criteria);
	}


	/**
	 * Увеличивает счетчик количества отклонений для идеи
	 */
	public function increaseReject()
	{
		$sql = 'UPDATE interior SET count_rejected = count_rejected + 1 WHERE id = ' . $this->id;

		Yii::app()->db
			->createCommand($sql)
			->execute();
	}

	/**
	 * Возвращает количество отклонений для идеи
	 *
	 * @return mixed
	 */
	public function getCountReject()
	{
		$sql = 'SELECT count_rejected FROM interior WHERE id = ' . $this->id;

		$qt = Yii::app()->db->createCommand($sql)->queryScalar();

		return $qt;
	}


	/**
	 * Проверяем достигла идея устанволенного количество разрешенных
	 * отклонений или нет.
	 */
	public function reachedLimitReject()
	{
		return $this->getCountReject() >= 3;
	}
}
