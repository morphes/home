<?php

class CatCsvController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';

        public function filters() {
                return array('accessControl');
        }

        public function accessRules() {

                return array(
                        array('allow',
                                'roles'=>array(
                                        User::ROLE_ADMIN,
                                        User::ROLE_POWERADMIN,
                                        User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_SALEMANAGER
                                ),
                        ),
                        array('deny',
                                'users'=>array('*'),
                        ),
                );
        }

        public function init()
        {
                // отключение твиттер-панели
                $this->rightbar = null;
        }


	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{


                $this->render('index',array(

                ));
	}


	/**
	 * Список всех запущенных заданий
	 */
	public function actionList()
	{
		// Получаем список всех задач
		$models = CatCsv::model()->findAll(array('order' => 'create_time DESC'));

		$this->render('list', array(
			'models' => $models
		));
	}


	/**
	 * Метод инициирует действие по экспорту товаров выбранных производителей.
	 */
	public function actionExportForVendors()
	{
		$success = true;
		$errorMsg = '';


		$ids = Yii::app()->request->getParam('vendor_ids');

		if ( ! is_array($ids)) {
			$success = false;
			$errorMsg = 'Список ID производителей должен быть в массиве.';
			goto the_end;
		}

		$taskExport = new CatCsv();
		$taskExport->user_id  = Yii::app()->user->id;
		$taskExport->action   = 'export';
		$taskExport->type     = CatCsv::TYPE_FOR_VENDORS;
		$taskExport->status   = CatCsv::STATUS_NEW;
		$taskExport->data     = serialize(array('vendor_ids' => $ids));
		$taskExport->progress = serialize(array('totalItems' => 0, 'doneItems' => 0));


		if ( ! $taskExport->save()) {
			$success = false;
			$errorMsg = 'Ошибка создания задачи на экспорт';
			goto the_end;
		}


		// Добавляем очередь в воркер
		Yii::app()->gearman->appendJob('catCsv:exportForVendors', array(
			'task_id' => $taskExport->id,
		));


		the_end:
		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg
		)));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Style::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='style-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Вовзаращет текущие данные по переданным идентификаторам задач.
	 */
	public function actionGetProgressTasks()
	{
		$success = true;
		$errorMsg = '';
		$result = array();

		// ПОлучаем список ID вендоров, для которых будем получать текущее состояние
		$task_ids = Yii::app()->request->getParam('task_ids');

		$tasks = CatCsv::model()->findAllByAttributes(array('id' => $task_ids));


		/** @var $task CatCsv */
		foreach($tasks as $task) {
			$result[] = array(
				'id'          => $task->id,
				'progress'    => $task->getProgressPercent(),
				'status'      => $task->status,
				'statusHtml'  => $task->getStatusColor(),
				'file'        => '/download/catCsv/id/'.$task->id,
				'workTime'    => $task->getWorkTime(),
			);
		}


		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg,
			'tasks'    => $result,
		)));

	}

	public function actionDeleteTask($id = null)
	{
		$success = true;
		$errorMsg = '';

		$task = CatCsv::model()->findByPk((int)$id);

		if ( ! $task) {
			$success = false;
			$errorMsg = 'Элемент не найден';
			goto the_end;
		}


		@unlink(Yii::getPathOfAlias('webroot').$task->file);
		if ( ! $task->delete()) {
			$success = false;
			$errorMsg = 'Ошибка удаления записи';
			goto the_end;
		}



		the_end:
		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg
		)));
	}

}
