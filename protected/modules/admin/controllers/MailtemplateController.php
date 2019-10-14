<?php

/**
 * @brief Работа с шаблонами писем сайта (письма регистрации, активации и т.п.)
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class MailtemplateController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';
	
        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'view', 'create', 'update', 'delete', 'templateTest'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
        
	/**
	 * @brief Show templates list
	 */
	public function actionIndex()
	{
		$this->rightbar = null;
		
		$dataProvider = new CActiveDataProvider('MailTemplate', array(
				'criteria' => array(
				    'order' => 'create_time DESC'
				),
				'pagination' => array(
					'pageSize' => 20,
				),
			));

		$this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}

	/**
	 * @brief Interface for update template
	 * @param string $key 
	 */
	public function actionUpdate($key = null)
	{
		$template = $this->loadModel($key);

		if (isset($_POST['MailTemplate'])) {
			$template->attributes = $_POST['MailTemplate'];
			if ($template->validate()) {
				$template->save(false);
				Yii::app()->user->setFlash('success', 'Шаблон успешно изменен');
				$this->redirect($this->createUrl('update', array('key' => $template->key)));
			}
		}

		$this->render('edit', array('template' => $template));
	}

	/**
	 * @brief Interface for create template
	 */
	public function actionCreate()
	{
		$template = new MailTemplate();
		if (isset($_POST['MailTemplate'])) {
			$template->attributes = $_POST['MailTemplate'];
			if ($template->validate()) {
				$template->save(false);
				Yii::app()->user->setFlash('success', 'Шаблон успешно создан');
				$this->redirect($this->createUrl('update', array('key' => $template->key)));
			}
		}

		$this->render('edit', array('template' => $template));
	}

	/**
	 * @brief Template view
	 * @param string $key 
	 */
	public function actionView($key)
	{
		$this->render('view', array(
			'template' => $this->loadModel($key),
		));
	}

	/**
	 * @brief Remove template
	 * @param string $key 
	 */
	public function actionDelete($key)
	{
		if (Yii::app()->request->isPostRequest) {
			$this->loadModel($key)->delete();
		}
		else
			throw new CHttpException(400);
	}

	/**
	 * @brief Find template model by key or throw exception
	 * @param string $key
	 * @return Template
	 */
	public function loadModel($key)
	{
		$model = MailTemplate::model()->findByPk($key);
		if ($model === null)
			throw new CHttpException(404);
		return $model;
	}

        /**
         * Тестовая отправка письма по шаблону
         * @throws CHttpException
         */
        public function actionTemplateTest()
        {
                // кому отправить
                $mail_to = Yii::app()->request->getParam('mail_to');
                // ключ тестируемого шаблона
                $template_key = Yii::app()->request->getParam('template_key');

		$layout = Yii::app()->request->getParam('layout');

                if(!$mail_to || !$template_key)
                        throw new CHttpException(404);

                Yii::app()->mail->create($template_key)
                        ->to(CHtml::encode($mail_to))
			->useView($layout)
                        ->send();

                $response = CJSON::encode(array('text'=>'Письмо отправлено'));
                die($response);
        }

}