<?php

/**
 * @brief Контроллер по генерации отчетов
 * @author Alexey Shvedov <alexeii.shvedov@gmail.com>
 */
class ReportController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';
	public function accessRules() {

		return array(
			array('allow',
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
				),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	public function init(){
		Yii::import('application.modules.catalog.models.Category');
	}

	public function actionShow()
	{
		$type = Yii::app()->getRequest()->getParam('type');
		if ( is_null($type) || empty(Report::$typeNames[$type]) )
			throw new CHttpException(404);

		$type = intval($type);

		$reports = Report::model()->findAllByAttributes(array('type_id'=>$type), array('limit'=>20, 'order'=>'id DESC'));

		$model = new Report();

		$model->type_id = $type;
		$this->render('show', array(
			'model'=>$model,
			'reports'=>$reports,
		));
	}


	public function actionUpdatelist()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest())
			throw new CHttpException(404);

		$type = intval($request->getParam('type'));
		$idList = $request->getParam('idList', array());

		if (empty(Report::$typeNames[$type]) || !is_array($idList))
			throw new CHttpException(400);

		$criteria = new CDbCriteria();
		$criteria->addInCondition('id', $idList);
		$criteria->limit = 20;

		$reports = Report::model()->findAll($criteria);
		$html = array();
		foreach ($reports as $report) {
			$html[$report->id] = $this->renderPartial('_reportItem', array('report'=>$report), true);
		}

		Yii::app()->end( json_encode( array('success'=>true, 'reports'=>$html), JSON_NUMERIC_CHECK ) );
	}


	/**
	 * Создание отчета
	 * @return JSON
	 * @throws CHttpException
	 */
	public function actionCreate()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest()) {
			throw new CHttpException(404);
		}

		$type = intval($request->getParam('type'));
		if (empty(Report::$typeNames[$type])) {
			throw new CHttpException(400);
		}

		$model = new Report();
		$model->status = Report::STATUS_NEW;
		$model->type_id = $type;
		$model->data = serialize($_POST);
		$model->user_id = Yii::app()->getUser()->getId();
		$model->save(false);

		$html = $this->renderPartial('_reportItem', array('report' => $model), true);
		Yii::app()->gearman->appendJob('report', $model->id);

		Yii::app()->end(json_encode(array(
			'success' => true,
			'html'    => $html
		), JSON_NUMERIC_CHECK));
	}

	/**
	 * Удаление отчета
	 * @return JSON
	 * @throws CHttpException
	 */
	public function actionDelete()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest())
			throw new CHttpException(404);
		$id = intval($request->getParam('id'));
		$type = intval($request->getParam('type'));

		if (empty(Report::$typeNames[$type]))
			throw new CHttpException(400);

		/** @var $report Report */
		$report = Report::model()->findByPk($id);
		if (is_null($report) || $report->status == Report::STATUS_PROGRESS)
			throw new CHttpException(404);

		 if (!empty($report->file)) {
			 $fname = Yii::getPathOfAlias('webroot') . '/' . $report->file;
			 if (file_exists($fname)) {
				 unlink($fname);
			 }
		 }
		$report->delete();

		Yii::app()->end( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}

	/**
	 * AJAX load
	 * Check modelclass and load tree data
	 */
	public function actionLoad() {

		$url = Yii::app()->getRequest()->getParam('clickAction', '');
		echo json_encode(Category::model()->getTree($url));
	}
}