<?php

class TenderController extends FrontController
{
	public function filters()
        {
                return array('accessControl');
        }

	/**
         * @brief Разрешает доступ по ролям
         * @return array
         */
        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'list', 'view', 'servicelist', 'create', 'upload', 'removefile', 'access', 'close', 'success'),
			'users' => array('*'),
                    ),
                   
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
	
	public function beforeAction($action)
	{
		$this->menuIsActiveLink = true;
		$this->menuActiveKey = 'tenders';
		
		Yii::app()->clientScript->registerCssFile('/css/tenders.css');
		Yii::app()->clientScript->registerScriptFile('/js/tenders.js');
		return parent::beforeAction($action);
	}


	/**
	 * Страница, которая открывается после успешного создания тендера.
	 *
	 * @return string
	 */
	public function actionSuccess()
	{
		$uid = Yii::app()->getUser()->getId();
		$user = is_null($uid) ? null : Yii::app()->getUser()->getModel();

		return $this->render('success', array(
			'user' => $user,
		));
	}

	public function actionCreate($id=null)
	{
		$uid = Yii::app()->getUser()->getId();
		$user = is_null($uid) ? null : Yii::app()->getUser()->getModel();

		if (!is_null($id)) {
			$tender = Tender::model()->findByPk( intval($id) );
			if ( is_null($tender) || in_array($tender->status, array(Tender::STATUS_DELETED, Tender::STATUS_MODERATING)) )
				throw new CHttpException(404);

			if ( $tender->getIsClosed() || !$tender->hasAccess() || (!$tender->getIsAuthor() && $tender->status != Tender::STATUS_MAKING ))
				throw new CHttpException(403);
		} else {
			$tender = new Tender('making');
			$tender->author_id = $uid;
			$tender->status = Tender::STATUS_MAKING;
			// Редирект на редактирование созданного проекта
			if($tender->save()) {
				$tender->appendAccess();
				return $this->redirect( $tender->getEditLink() );
			} else {
				throw new CHttpException(404);
			}
		}

		$tender->setScenario('update');

		$oldStatus = $tender->status;
		if (Yii::app()->request->isPostRequest && isset($_POST['Tender'])) {

			if ( in_array($tender->status, array(Tender::STATUS_MAKING, Tender::STATUS_IN_COMPLETITION) ) ) {
				$tender->setScenario('createTender');
				$tender->status = Tender::STATUS_MODERATING;

			} else if ( in_array($tender->status, array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED)) ) {
				$tender->status = Tender::STATUS_CHANGED;
				$tender->setScenario('changeTender');
			}

			if (isset($_POST['time'])) {
				$time = intval ( $_POST['time'] );
				if ( $time >= 1 && $time <=30 )
					$tender->expire = time() + 86400 * $time;
			}

			$tender->attributes = $_POST['Tender'];
			if ($tender->cost_flag == Tender::COST_COMPARE)
				$tender->cost = 0;

			if ( $tender->validate() ) {
				$tender->save(false);
			}

			// Сохранение файлов
			/** Simple form */
			if (isset($_POST['UploadedFile'])) {
				foreach ($_POST['UploadedFile'] as $fileKey => $value) {
					TenderFile::saveFile($tender->id, 'file_'.$fileKey, $value['desc']);
				}
			}

			/** Обновление описания файлов */
			if (isset($_POST['File']['desc'])) {
				foreach ($_POST['File']['desc'] as $key => $item) {
					$link = TenderFile::model()->findByPk(array('tender_id'=>$tender->id, 'file_id'=>$key));
					if (!is_null($link)) {
						$link->desc = $item;
						$link->save();
					}
				}
			}

			if (!$tender->hasErrors()) {
				if ( in_array($oldStatus, array(Tender::STATUS_MAKING, Tender::STATUS_IN_COMPLETITION)) )
					Yii::app()->getUser()->setFlash('tender_create', true);

				$this->redirect('/tenders/success');
			} else {
				$tender->status = $oldStatus;
			}
		}

		return $this->render('create', array(
			'tender' => $tender,
			'user' => $user,
		) );

	}

	/**
	 * Загрузка изображений на fileApi
	 * @param tid - Tender id
	 */
	public function actionUpload($tid = null)
	{
		$fileName = isset($_POST['UploadedFile']['name']) ? $_POST['UploadedFile']['name'] : '';
		if(TenderFile::saveFile($tid, $fileName, false) !== false)
			die('ok');
		else
			die('error');
	}

	/**
	 * Удаление файла из тендера
	 * @return JSON
	 */
	public function actionRemoveFile()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		$this->layout = false;
		$fileId = (int)Yii::app()->request->getParam('file_id');

		$fileLink = TenderFile::model()->findByAttributes(array('file_id' => $fileId) );
		if (!is_null($fileLink)) {
			/** @var $file UploadedFile */
			$file = UploadedFile::model()->findByPk($fileId);
			if ( !is_null($file) && $file->checkAccess() ) {
				$file->removeOriginFile();
				$file->delete();
				$fileLink->delete();
				die( CJSON::encode( array('success'=>true) ) );
			}
		}
		die( CJSON::encode( array('error'=>true) ) );
	}
	
	public function actionList()
	{
		$this->menuIsActiveLink = false;
		Yii::import('application.modules.member.models.Service');
		$user = Yii::app()->user->model;
		
		// Prepare input data
		$cityId = (int)Yii::app()->request->getParam('city_id', 0);
		$mainService = (int) Yii::app()->request->getParam('main_service', 0);
		$childService = (int) Yii::app()->request->getParam('child_service', 0);
		
		$tenderType = (int) Yii::app()->request->getParam('tender_type', Tender::TENDER_TYPE_ALL);
		if (!isset(Tender::$typeNames[$tenderType]))
			$tenderType = Tender::TENDER_TYPE_ALL;
		
		$pageSize = (int) Yii::app()->request->getParam('pagesize', 0);
		if (!isset(Tender::$listPageSizes[$pageSize]))
			$pageSize = key(Tender::$listPageSizes);
		
		$sortType = (int)  Yii::app()->request->getParam('sorttype', Tender::SORT_DATE);
		if (!isset(Tender::$sortNames[$sortType]))
			$sortType = Tender::SORT_DATE;
		
		// configure filter
		$filters = array();

		if (!empty($cityId)) {
			$filters['city_id'] = $cityId;
		}
		if (!empty($mainService) && empty($childService)) {
			$sql = 'SELECT id FROM service WHERE parent_id='.$mainService;
			$servicesId = Yii::app()->db->createCommand($sql)->queryColumn();
			if (!empty($servicesId))
				$filters['services'] = $servicesId;
		}
		if (!empty($childService)) {
			$filters['services'] = $childService;
		}
		// Type filter
		switch ($tenderType) {
			case Tender::TENDER_TYPE_ALL:{
				$filters['status'] = array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED, Tender::STATUS_CLOSED);
			}
			break;
			case Tender::TENDER_TYPE_OPEN:{
				$filters['status'] = array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED);
			}
			break;
			case Tender::TENDER_TYPE_CLOSED:{
				$filters['status'] = Tender::STATUS_CLOSED;
			}
			break;
			default:
				throw new CHttpException(500);
		}
		// sorting
		switch ($sortType) {
			case Tender::SORT_DATE:{
				$sortExpr = 'is_open DESC, @id DESC';
			}
			break;
			case Tender::SORT_RESPONSE:{
				$sortExpr = 'is_open DESC, response DESC, @id DESC';
			}
			break;
			case Tender::SORT_COST:{
				$sortExpr = 'is_open DESC, cost DESC, @id DESC';
			}
			break;
			default:
				throw new CHttpException(500);
		}
		
		$sphinxClient = Yii::app()->search;
		$dataProvider = new CSphinxDataProvider($sphinxClient,
			array('index' => 'tender',
				'modelClass' => 'Tender',
				'filters' => $filters,
				'matchMode' => SPH_MATCH_FULLSCAN,
				'sortMode' => SPH_SORT_EXTENDED,
				'sortExpr' => $sortExpr,
				'pagination' => array('pageSize' => $pageSize, 'pageVar'=>'page'),
			)
		);
		
		$this->render('list', array(
		    'user' => $user,
		    'dataProvider' => $dataProvider,
		    'sortType' => $sortType,
		    'pageSize' => $pageSize,
		    'mainService' => $mainService,
		    'childService' => $childService,
		    'tenderType' => $tenderType,
		    'cityId' => $cityId,
		));
	}
	
	/**
	 * Страница тендера
	 * @param integer $id
	 * @throws CHttpException 
	 */
	public function actionView($id = null)
	{
		/** @var $tender Tender */
		$tender = Tender::model()->findByPk(intval($id));
		if (is_null($tender) || in_array($tender->status, array(Tender::STATUS_DELETED, Tender::STATUS_MAKING)) )
			throw new CHttpException(404);
		
		$tender->incrementViews();
		
		$responseProvider = new CActiveDataProvider('TenderResponse', array(
								'criteria' => array(
								    'condition' => 'tender_id=:tid',
								    'params' => array(':tid'=>$tender->id),
								), 
								'pagination'=>array('pageSize'=>200),
		    ));
		$serviceList = $tender->getServiceList();

		$files = array();
		$hasResponse = false;
		if (!Yii::app()->user->isGuest) {
			$files = Yii::app()->db->createCommand()->select('uf.id, uf.name, uf.ext, uf.size, tender_file.desc')
					->from('uploaded_file as uf')
					->join('tender_file', 'tender_file.file_id=uf.id')
					->where('tender_file.tender_id=:tid', array(':tid'=>  $tender->id))
					->queryAll();
			$hasResponse = TenderResponse::model()->exists('tender_id=:tid AND author_id=:uid', array(':tid'=>$tender->id, ':uid'=>  Yii::app()->user->id));
		}
		
		$isClosed = $tender->getIsClosed();
		$viewsCount = $tender->getViews();
		
		$render = 'defaultView';
		if ( $tender->hasAccess() ) { // author
			$render = 'authorView';
		} else if (Yii::app()->user->isGuest) { // guest
			$render = 'guestView';
		}
		
		$this->pageTitle = $tender->name.' — Заказы — MyHome.ru';
			
		$this->render($render, array('tender'=>$tender, 
					'responseProvider'=>$responseProvider,
					'serviceList' =>$serviceList,
					'files'=>$files,
					'hasResponse'=>$hasResponse,
					'isClosed' => $isClosed,
					'viewsCount' => $viewsCount,
		    ));
	}
	
	public function actionServicelist()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
		Yii::import('application.modules.member.models.Service');
		$this->layout = false;
		
		$serviceId = Yii::app()->request->getParam('service_id');
		
		$services = Service::getChildrens($serviceId);
		$html = '';
		foreach ($services as $service) {
			$html .= CHtml::tag('li', array('data-value'=>$service->id), $service->name);
		}
		
		die( CJSON::encode( array('success'=>true, 'html' => $html) ) );
	}

	/**
	 * Получение доступа к тендеру по url
	 * @param null $id
	 * @param null $token
	 * @throws CHttpException
	 */
	public function actionAccess($id=null, $token=null, $hash=null)
	{
		if ( is_null($id) || is_null($token) )
			throw new CHttpException(400);

		$id = intval($id);
		/** @var $tender Tender */
		$tender = Tender::model()->findByPk($id);

		if ( is_null($tender) || $tender->token != $token )
			throw new CHttpException(400);

		$tender->appendAccess();
		$url = $tender->getLink();
		if (!is_null($hash))
			$url .= '#'.$hash;
		$this->redirect($url);
	}

	public function actionClose($id=null, $token=null)
	{
		if ( is_null($id) || is_null($token) )
			throw new CHttpException(400);

		$id = intval($id);
		/** @var $tender Tender */
		$tender = Tender::model()->findByPk($id);

		if ( is_null($tender) || $tender->token != $token )
			throw new CHttpException(400);

		$tender->appendAccess();
		$tender->status = Tender::STATUS_CLOSED;
		$tender->save(false);
		$this->redirect($tender->getLink());
	}
}