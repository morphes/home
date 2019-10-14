<?php

class EventController extends FrontController
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('bindvisit'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('index', 'view', 'ajaxgetgallery', 'bindnotify'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function beforeAction($action)
	{
		$this->menuActiveKey              = 'journal_event';
		$this->menuIsActiveLink           = true;
		$this->menuIsActiveLinkOnlyParent = true;

		Yii::app()->getClientScript()->registerCssFile('/css/media.css');
		Yii::app()->getClientScript()->registerScriptFile('/js/CMedia.js');
		return parent::beforeAction($action);
	}

	public function actionView($id)
	{
		$this->menuIsActiveLinkOnlyParent = false;

		$id = intval($id);
		/** @var $event MediaEvent */
		$event = MediaEvent::model()->findByPk($id, 'status=:st', array(':st'=>MediaEvent::STATUS_PUBLIC));
		if (is_null($event) || $event->public_time > time())
			throw new CHttpException(404);

		$places = MediaEventPlace::model()->findAllByAttributes( array('event_id'=>$event->id) );
		$themes = MediaTheme::model()->findAllByPk($event->themes);
		$eventType = MediaEventType::model()->findByPk($event->event_type);

		if ( Yii::app()->getUser()->getIsGuest() ) {
			$isVisit = false;
		} else {
			$isVisit = MediaEventVisit::model()->exists('event_id=:eid AND user_id=:uid', array(':uid'=>Yii::app()->getUser()->getId(), ':eid'=>$event->id));
		}

		/** Список и число подписавшихся */
		$visitorsCount = MediaEventVisit::model()->countByAttributes(array('event_id'=>$event->id));
		if ( $visitorsCount == 0 ) {
			$visitors = array();
		} else {
			$criteria = new CDbCriteria();
			$criteria->select = 't.*';
			$criteria->join = 'INNER JOIN media_event_visit as mev ON mev.user_id=t.id';
			$criteria->condition = 'mev.event_id=:eid';
			$criteria->limit = 25;
			$criteria->params = array(':eid'=>$event->id);
			$visitors = User::model()->findAll($criteria);
		}
		/** Список связанных с событием новостей */
		$mediaNews = MediaNew::model()->findAllByAttributes(
			array('status'=>MediaNew::STATUS_PUBLIC, 'event_id'=>$event->id),
			array(
				'condition' => 'public_time<:time',
				'order'=>'public_time DESC',
				'limit'=>3,
				'params'=>array(':time'=>time()),
			)
		);

		MediaEvent::appendView($id);

		$this->render('view', array(
			'event' => $event,
			'places' => $places,
			'themes' => $themes,
			'eventType' => $eventType,
			'isVisit' => $isVisit,
			'visitorsCount' => $visitorsCount,
			'visitors' => $visitors,
			'mediaNews' => $mediaNews,
		));
	}

	/**
	 * Получает и возвращает галлерею для детального описания статьи на фронте
	 */
	public function actionAjaxGetGallery()
	{
		$modelId = (int)$_POST['modelId'];
		$numGallery = (int)$_POST['num'];

		$models = MediaGallery::model()->findAllByAttributes(array(
			'model' => 'MediaEvent',
			'model_id' => $modelId,
			'num' => $numGallery
		));



		$html = $this->renderPartial('_ajaxGallery', array(
			'models' => $models,
			'numGallery' => $numGallery
		), true);

		die(json_encode(array(
			'success' => true,
			'html' => $html
		)));
	}

	/**
	 * Кнопка "я буду" в событии
	 */
	public function actionBindvisit()
	{
		if ( !Yii::app()->getRequest()->getIsAjaxRequest() )
			throw new CHttpException(404);

		$action = Yii::app()->getRequest()->getParam('action');
	 	$eventId = intval( Yii::app()->getRequest()->getParam('eventId') );
		$userId = Yii::app()->getUser()->getId();

		switch ($action) {
			case 'create': {
				$isVisit = MediaEventVisit::model()->exists('event_id=:eid AND user_id=:uid', array(':uid'=>$userId, ':eid'=>$eventId));
				if (!$isVisit) {
					$hasEvent = MediaEvent::model()->exists('id=:id', array(':id'=>$eventId));
					if (!$hasEvent)
						die ( json_encode( array('error'=>true) ) );

					$visit = new MediaEventVisit();
					$visit->event_id = $eventId;
					$visit->user_id = $userId;
					$visit->save(false);

					MediaEvent::updateVisitCount($eventId);
				}
			} break;
			case 'delete': {
				$count = MediaEventVisit::model()->deleteByPk(array('event_id'=>$eventId, 'user_id'=>$userId));
				if ($count > 0)
					MediaEvent::updateVisitCount($eventId);

			} break;
			default : throw new CHttpException(404);
		}

		die ( json_encode( array('success'=>true) ) );
	}

	/**
	 * Кнопка "напомнить мне" в событии
	 */
	public function actionBindnotify()
	{
		if ( !Yii::app()->getRequest()->getIsAjaxRequest() )
			throw new CHttpException(404);

		$eventId = intval( Yii::app()->getRequest()->getParam('eventId') );
		$email = Yii::app()->getRequest()->getParam('email');

		$hasNotify = MediaEventNotify::model()->exists('event_id=:eid AND email=:email', array(':email'=>$email, ':eid'=>$eventId));
		if (!$hasNotify) {
			$hasEvent = MediaEvent::model()->exists('id=:id', array(':id'=>$eventId));
			if (!$hasEvent)
				die ( json_encode( array('error'=>true) ) );
			$visit = new MediaEventNotify();
			$visit->event_id = $eventId;
			$visit->email = $email;
			$visit->user_id = Yii::app()->getUser()->getId(); // для логов
			if ( !$visit->save() )
				die ( json_encode( array('error'=>true) ) );
		}

		die ( json_encode( array('success'=>true) ) );
	}

	public function actionIndex()
	{
		// Получаем и устанавливаем кол-во элементов на странице

		if (Yii::app()->request->getParam('pagesize'))
			$pageSize = Yii::app()->request->getParam('pagesize');
		else
			$pageSize = Yii::app()->session->get('media_pagesize');

		$pageSize = empty(Config::$mediaPageSizes[(int)$pageSize]) ? key(Config::$mediaPageSizes) : (int)$pageSize;
		Yii::app()->session->add('media_pagesize', $pageSize);

		/*$viewType = intval( Yii::app()->getRequest()->getParam('viewtype', 1) );
		if ( !in_array($viewType, array(0, 1)) )
			$viewType = 1;*/

		$viewType = 0;

		$sortType = intval( Yii::app()->getRequest()->getParam('sort', MediaEvent::SORT_DATE) );
		if ( !in_array($sortType, array(MediaEvent::SORT_DATE, MediaEvent::SORT_POPULAR)) )
			$sortType = MediaEvent::SORT_DATE;

		$sortDirect = intval( Yii::app()->getRequest()->getParam('sortdirect') );
		if ( !in_array($sortDirect, array(MediaEvent::SORT_DIRECT_DESC, MediaEvent::SORT_DIRECT_ASC)) ) {
			if ($sortType == MediaEvent::SORT_DATE)
				$sortDirect = MediaEvent::SORT_DIRECT_ASC;
			else
				$sortDirect = MediaEvent::SORT_DIRECT_DESC;
		}

		/** Searching */

		$criteria = new CDbCriteria();
		$criteria->select = 'DISTINCT t.*';

		/** Типы событий */
		$eventTypes = Yii::app()->getRequest()->getParam('type', array());
		if (!empty($eventTypes)) {
			$criteria->addInCondition('t.event_type', $eventTypes);
			$_GET['event_types'] = implode(', ', $eventTypes);
		}

		/** Тематики */
		$eventThemes = Yii::app()->getRequest()->getParam('theme', array());
		if (!empty($eventThemes)) {
			$criteria->join .= 'INNER JOIN media_theme_select as mts ON mts.model_id=t.id ';
			$criteria->addInCondition('mts.theme_id', $eventThemes);
			$criteria->compare('mts.model', 'MediaEvent');
			$_GET['event_theme'] = implode(', ', $eventThemes);
		}

		/** Кому интересно */
		$intSpec = (bool)Yii::app()->getRequest()->getParam('int_spec');
		$intUser = (bool)Yii::app()->getRequest()->getParam('int_user');
		if ($intSpec && $intUser) {
			$criteria->addInCondition('whom_interest', array(MediaEvent::WHOM_SPEC, MediaEvent::WHOM_USER, MediaEvent::WHOM_SPEC_USER));
		} elseif ($intSpec) {
			$criteria->addInCondition('whom_interest', array(MediaEvent::WHOM_SPEC, MediaEvent::WHOM_SPEC_USER));
		} elseif ($intUser) {
			$criteria->addInCondition('whom_interest', array(MediaEvent::WHOM_USER, MediaEvent::WHOM_SPEC_USER));
		}

		/** проверка на онлайн событие */
		$isOnline = (bool)Yii::app()->getRequest()->getParam('is_online');
		if ($isOnline) {
			$criteria->compare('is_online', 1);
		}

		/** City filter */
		$cityId = (int)Yii::app()->getRequest()->getParam('city_id');
		if (!empty($cityId)) {
			$criteria->join .= 'INNER JOIN media_event_place as mep ON mep.event_id=t.id ';
			$criteria->compare('mep.city_id', $cityId);
		}

		/** Time filter  */
		$startTime = intval( Yii::app()->getRequest()->getParam('start_time') );
		if (empty($startTime))
			$startTime = mktime( 0,0,0,date( "m" ),date( "d"),date("y" ) );
		$endTime = intval( Yii::app()->getRequest()->getParam('end_time') );

		if ( !empty($startTime) && !empty($endTime) ) {
			$criteria->addCondition('NOT ( (t.start_time<:start && t.end_time<:start) || t.start_time>:end)');
			$criteria->params[':start'] = $startTime;
			$criteria->params[':end'] = $endTime;
		} elseif (!empty($startTime)) {
			$criteria->addCondition('NOT (t.start_time<:start && t.end_time<:start)');
			$criteria->params[':start'] = $startTime;
		}

		/** Sort  */
		if ($sortType == MediaEvent::SORT_DATE) {
			$criteria->order = 't.start_time '. ( ($sortDirect == MediaEvent::SORT_DIRECT_ASC) ? 'ASC' : 'DESC');
		} else {
			$criteria->order = 't.count_visit '. ( ($sortDirect == MediaEvent::SORT_DIRECT_ASC) ? 'ASC' : 'DESC');
		}

		$eventProvider = new CActiveDataProvider('MediaEvent', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));

		/** Bubble data */
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			$html = $this->renderPartial('_bubble', array('eventProvider'=>$eventProvider), true);
			die ( json_encode( array('success'=>true, 'html'=>$html) ) );
		}

		$this->render('index', array(
			'eventProvider' => $eventProvider,
			'viewType' => $viewType,
			'pageSize' => $pageSize,
			'sortType' => $sortType,
			'sortDirect' => $sortDirect,
			'cityId' => $cityId,
			'startTime' => $startTime,
			'endTime' => $endTime,
		));
	}
}