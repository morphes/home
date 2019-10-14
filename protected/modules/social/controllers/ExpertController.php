<?php

class ExpertController extends FrontController
{
	public function actionIndex()
	{
		Yii::import('application.modules.member.models.Service');

		$topExperts = User::model()->findAllByAttributes(array('status'=>User::STATUS_ACTIVE, 'expert_type'=>User::EXPERT_TOP), array('limit'=>20));
		$experts = User::model()->findAllByAttributes(array('status'=>User::STATUS_ACTIVE, 'expert_type'=>User::EXPERT), array('limit'=>100));

		// Список услуг для формы заявки на Экспертность
		$services = Service::model()->findAll(array('condition' => 'parent_id <> 0'));

		$this->render('index', array(
			'topExperts' => $topExperts,
			'experts'    => $experts,
			'services'   => $services
		));
	}

	public function actionSendDesire()
	{
		$success = true;
		$errorMsg = '';
		$errorFields = array();

		$data = Yii::app()->request->getParam('Expert');

		$name 		= isset($data['name']) ? CHtml::encode($data['name']) : '';
		$phone 		= isset($data['phone']) ? CHtml::encode($data['phone']) : '';
		$service 	= isset($data['service']) ? CHtml::encode($data['service']) : '';
		$serviceCustom 	= isset($data['serviceCustom']) ? CHtml::encode($data['serviceCustom']) : '';
		$url 		= isset($data['service']) ? CHtml::encode($data['url']) : '';

		if (empty($name))
			$errorFields[] = 'name';

		if (empty($service) && empty($serviceCustom)) {
			$errorFields[] = 'service';
			$errorFields[] = 'serviceCustom';
		}


		// Проверка на обязательные поля.
		if ( ! empty($errorFields) ) {
			$success = false;
			$errorMsg = 'Не все обязательные поля заполнены.';
			goto the_end;
		}


		// Формируем данные из формы для письма.
		$data_form = '';
		if ( ! empty($name))
			$data_form .= '<p><strong>Имя</strong>: ' . $name . '</p>';

		if ( ! empty($phone))
			$data_form .= '<p><strong>Телефон</strong>: ' . $phone . '</p>';

		if ( ! empty($service))
			$data_form .= '<p><strong>Услуга</strong>: ' . $service . '</p>';

		if ( ! empty($serviceCustom))
			$data_form .= '<p><strong>Услуга (свой вариант)</strong>: ' . $serviceCustom . '</p>';

		if ( ! empty($url))
			$data_form .= '<p><strong>Сайт</strong>: ' . $url . '</p>';


		Yii::app()->mail->create('wantToBeExpert')
			->to('bkv@myhome.ru')
			->priority(EmailComponent::PRT_NORMAL)
			->params(array(
				'user_ip' 	=> $_SERVER['REMOTE_ADDR'],
				'user_agent' 	=> CHtml::encode($_SERVER['HTTP_USER_AGENT']),
				'data_form'	=> $data_form,
				'user_email'	=> Yii::app()->user->model->email,
			))
			->send();

		the_end:
		die(json_encode(array(
			'success'     => $success,
			'errorMsg'    => $errorMsg,
			'errorFields' => $errorFields,
			'name'        => $name
		)));
	}
}