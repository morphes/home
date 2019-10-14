<?php

class VacanciesController extends FrontController
{
	public function actionIndex()
	{
		Yii::import('application.modules.content.models.Content');
		
		
//		$dataProvider = new CActiveDataProvider(array(
//			'condition' => 'status = :st1 OR status = :st2',
//			'params' => array(':st1' => Vacancies::STATUS_ACTIVE)
//		));
		$vacancies = Vacancies::model()->findAllByAttributes(
			array('status' => Vacancies::STATUS_ACTIVE),
			new CDbCriteria(array(
				'order' => 'position ASC'
			))
		);
		
		$this->render('//vacancies/index', array(
			'vacancies' => $vacancies,
			'staticText' => Content::getContentByAlias('pre_vacancies'),
			'staticTextSide' => Content::getContentByAlias('side_vacancies'),
		));
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}