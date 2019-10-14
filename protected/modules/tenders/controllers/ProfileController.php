<?php

class ProfileController extends FrontController
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
			array(
				'allow',
				'actions' => array('tenderclose'),
				'users'=>array('*'),
			),
			array('allow',
			    'actions' => array('create', 'removefile', 'servicelist', 'upload', 'iclient', 'index', 'tenderlist'),
                                'users'=>array('@'),
			),
			array('allow',
			    'actions' => array('suited', 'tenderresponse', 'idoer'),
			    'roles'=>array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function beforeAction($action)
	{
		Yii::app()->clientScript->registerCssFile('/css/tenders.css');
		Yii::app()->clientScript->registerScriptFile('/js/tenders.js');
		return parent::beforeAction($action);
	}
	
	public function actionIndex()
	{
		$user = Cache::getInstance()->user;
		if ( !($user instanceof User) )
			throw new CHttpException(404);
		
		if (in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)))
			$this->redirect ("/users/{$user->login}/tenders/suited");
		else 
			$this->redirect ("/users/{$user->login}/tenders/iclient");
	}

	/**
	 * Вывовд списка подходящих тендеров 
	 */
	public function actionSuited()
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$user = Cache::getInstance()->user;
		
		if ( !($user instanceof User) || $user->id != Yii::app()->user->id )
			throw new CHttpException(404);
		
		$dataProvider = Tender::getSuitedProvider($user->id);
		
		$this->render('suited', array(
				'dataProvider' => $dataProvider, 
				'user' => $user
		    ), false, array('profileSpecialist', array('user' => $user))
		);
	}
	
	/**
	 * Список тендеров "я исполнитель"
	 * @throws CHttpException 
	 */
	public function actionIdoer()
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$user = Cache::getInstance()->user;
		if ( !($user instanceof User) || $user->id != Yii::app()->user->id )
			throw new CHttpException(404);

		$dataProvider = Tender::getIdoerProvider($user->id);
		
		$this->render('idoer', array(
				'dataProvider' => $dataProvider, 
				'user' => $user
		    ), false, array('profileSpecialist', array('user' => $user))
		);
	}
	
	/**
	 * Список тендеров "я заказчик"
	 * @throws CHttpException 
	 */
	public function actionIclient()
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$user = Cache::getInstance()->user;
		if ( !($user instanceof User) || $user->id != Yii::app()->user->id )
			throw new CHttpException(404);
		
		$dataProvider = Tender::getIclienProvider($user->id);
		
		$layout = 'profileUser';
		if (in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
			$layout = 'profileSpecialist';
		}
		
		$this->render('iclient', array(
				'dataProvider' => $dataProvider, 
				'user' => $user
		    ), false, array($layout, array(
				'user' => $user,
			))
		);
	}
	
	/**
	 * Подписаться/отписаться от тендера 
	 */
	public function actionTenderresponse($id = null)
	{
		/** @var $tender Tender */
		$tender = Tender::model()->findByPk(intval($id));
		if ( is_null($tender) || !Yii::app()->request->isAjaxRequest || $tender->hasAccess() )
			throw new CHttpException(404);
		
		$action = Yii::app()->request->getParam('action', '');
		if ($action == 'add') { // create
			if ( $tender->getIsClosed() )
				die( CJSON::encode(array('error'=>'Tender is closed')) );
			// Проверка существования отклика
			$exist = TenderResponse::model()->exists('tender_id=:tid AND author_id=:uid', array(':tid'=>$tender->id, ':uid'=>Yii::app()->user->id));
			if (!$exist) {
				$response = new TenderResponse();
				$response->author_id = Yii::app()->user->id;
				$response->status = TenderResponse::STATUS_ACTIVE;
				$response->tender_id = $tender->id;
				
				$response->content = Yii::app()->request->getParam('content', '');
				$response->cost = Yii::app()->request->getParam('cost', '');
				if ($response->save()) {
					$hasHTML = (bool)Yii::app()->request->getParam('html', false);
					if ($hasHTML) { // Отдача отзыва для страницы тендера
						$html = $this->renderPartial('_responseItem', array('response'=>$response), true);
						$respCount = TenderResponse::model()->countByAttributes(array('tender_id'=>$tender->id));
						$resCntHtml = CFormatterEx::formatNumeral($respCount, array('отклик', 'отклика', 'откликов')); 
						
						die( CJSON::encode( array('success'=>true, 'html'=>$html, 'count'=>$resCntHtml) ) );
					}
					die( CJSON::encode( array('success'=>true) ) );
				}
			}
		} else if ($action == 'edit') { // edit response
			$response = TenderResponse::model()->findByAttributes(array('tender_id'=>$tender->id, 'author_id'=>  Yii::app()->user->id));
			if (!is_null($response)) {
				$content = Yii::app()->request->getParam('content', '');
				$response->content = $content;
				if ($response->save()) {
					die( CJSON::encode( array('success'=>true) ) );
				}
			}
		} else { // delete
			$response = TenderResponse::model()->findByAttributes(array('tender_id'=>$tender->id, 'author_id'=>  Yii::app()->user->id));
			if (!is_null($response))
				$response->delete();
			
			$hasHTML = (bool)Yii::app()->request->getParam('html', false);
			if ($hasHTML) { // Отдача отзыва для страницы тендера
				$respCount = TenderResponse::model()->countByAttributes(array('tender_id'=>$tender->id));
				if ($respCount == 0) {
					$countHtml = $countHtmlFull = 'Откликов нет';
				} else {
					$countHtml = CFormatterEx::formatNumeral($respCount, array('отклик', 'отклика', 'откликов')); 
					$countHtmlFull = CFormatterEx::formatNumeral($respCount, array('Получен', 'Получено', 'Получено'), true).' '.$countHtml;
				}
				die( CJSON::encode( array('success'=>true, 'countFull'=>$countHtmlFull, 'count'=>$countHtml) ) );
			}
			
			die( CJSON::encode( array('success'=>true) ) );
		}
		die( CJSON::encode(array('error'=>true)) );
	}
	
	/**
	 * Закрытие тендера владельцем
	 * @param integer $id
	 * @throws CHttpException 
	 */
	public function actionTenderclose($id = null)
	{

		/** @var $tender Tender */
		$tender = Tender::model()->findByPk(intval($id));
		if ( is_null($tender) || !Yii::app()->request->isAjaxRequest || !$tender->hasAccess() )
			throw new CHttpException(404);

		if ( !in_array($tender->status, array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED)) ) // Проверка состояния тендера
			die ( CJSON::encode( array('error'=>'Invalid params') ) );
		
		$tender->status = Tender::STATUS_CLOSED;
		$tender->expire = time()-1;
		if ($tender->save(false)) {
			$html = 'Заказ закрыт<br> '.Yii::app()->getDateFormatter()->format('d MMMM yyyy', $tender->expire);
			die ( CJSON::encode( array('success'=>true, 'html'=>$html) ) );
		}
		die ( CJSON::encode( array('error'=>'Invalid params') ) );
	}
	
	/**
	 * Страница тендеров
	 * @throws CHttpException 
	 */
	public function actionTenderlist()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
		$action = (int)Yii::app()->request->getParam('action');
		$section = (int)Yii::app()->request->getParam('section');
		
		$filters = array();
		switch ($action) {
			case 1: { // All
				$filters['status'] = array(Tender::STATUS_OPEN, Tender::STATUS_CLOSED);
			}
				break;
			case 2: { // opened
				$filters['status'] = array(Tender::STATUS_OPEN);
			}
			break;
			case 3: { // closed
				$filters['status'] = array(Tender::STATUS_CLOSED);
			}
			break;
			default :
				die ( CJSON::encode( array('error'=>'Invalid action') ) );
		}
		
		switch ($section) {
			case 1: { // suited
				$dataProvider = Tender::getSuitedProvider(Yii::app()->user->id, $filters);
				$render = '_tenderSuitedList';
			}
			break;
			case 2: { // idoer
				$dataProvider = Tender::getIdoerProvider(Yii::app()->user->id, $filters);
				$render = '_tenderIdoerList';
			}
			break;
			case 3: { // iclient
				$dataProvider = Tender::getIclienProvider(Yii::app()->user->id, $filters);
				$render = '_tenderIclientList';
			}
			break;
			default:
				die ( CJSON::encode( array('error'=>'Invalid section') ) );
		}
		
		$html = $this->renderPartial($render, array(
		    'dataProvider' => $dataProvider,
		), true);
		
		die( CJSON::encode( array('success'=>true, 'html'=>$html) ) );
	}
	
	/**
	 * Получение списка подкатегорий услуг 
	 */
	public function actionServicelist()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		$this->layout = false;
		$serviceId = Yii::app()->request->getParam('service_id');
		Yii::import('application.modules.member.models.*');
		
		$services = Service::getChildrens($serviceId);
		
		$data = $this->renderPartial('serviceList', array(
		    'services' => $services,
		), true);
		die( CJSON::encode( array('success'=>true, 'data' => $data) ) );
	}
	
}