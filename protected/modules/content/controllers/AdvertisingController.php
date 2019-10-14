<?php
/**
 * Контроллер для раздела рекламма
 * @author Виталий Секретенко
 *         Class AdvertisingController
 */

class AdvertisingController extends FrontController
{

	public function actionIndex()
	{
		Yii::import('application.modules.catalog.models.StoreOffer');

		$model = new StoreOffer();

		// флаг удачного сохранения
		$goodSave = null;

		$selectedService = '';

		$post = Yii::app()->request->getPost('StoreOffer');

		if (isset($post)) {

			if (isset($post['tariff'])) {
				$selectedService .= 'тариф ';
			}

			if (isset($post['banners'])) {
				$selectedService .= 'баннеры ';
			}

			if (isset($post['pr'])) {
				$selectedService .= 'PR-статьи ';
			}

			if (isset($post['click'])) {
				$selectedService .= 'клики ';
			}

			$post['selected_services'] = $selectedService;
			$model->attributes = $post;

			if ($model->save()) {
				$this->sendMail($model->company, $model->city_name, $model->company_phone,
					$model->email, $model->name, $model->job, $model->site, $model->comment, $model->selected_services);

				$model->unsetAttributes();
				$goodSave = true;
			} else {
				$goodSave = false;
			}
		}

		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-index-style';

		return $this->render('//content/advert/index', array(
			'model'    => $model,
			'goodSave' => $goodSave
		));
	}


	public function actionAdvantages()
	{

		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-advantages-style';

		return $this->render('//content/advert/advantages', array());
	}


	public function actionTips()
	{
		Yii::import('application.modules.catalog.models.StoreOffer');
		$modelAdv = new AdvQuestion();
		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-tips-style';

		$model = new StoreOffer();

		// флаг удачного сохранения
		$goodSave = null;

		$selectedService = '';

		$post = Yii::app()->request->getPost('StoreOffer');

		if (isset($post)) {

			if (isset($post['tariff'])) {
				$selectedService .= 'тариф ';
			}

			if (isset($post['banners'])) {
				$selectedService .= 'баннеры ';
			}

			if (isset($post['pr'])) {
				$selectedService .= 'PR-статьи ';
			}

			if (isset($post['click'])) {
				$selectedService .= 'клики ';
			}

			$post['selected_services'] = $selectedService;
			$model->attributes = $post;

			if ($model->save()) {
				$this->sendMail($model->company, $model->city_name, $model->company_phone,
					$model->email, $model->name, $model->job, $model->site, $model->comment, $model->selected_services);

				$model->unsetAttributes();
				$goodSave = true;
			} else {
				$goodSave = false;
			}
		}

		return $this->render('//content/advert/tips', array(
			'modelAdv' => $modelAdv,
			'model' => $model,
			'goodSave' => $goodSave
		));
	}


	public function actionRates()
	{
		Yii::import('application.modules.catalog.models.StoreOffer');

		$model = new StoreOffer();

		// флаг удачного сохранения
		$goodSave = null;

		$selectedService = '';

		$post = Yii::app()->request->getPost('StoreOffer');

		if (isset($post)) {

			if (isset($post['tariff'])) {
				$selectedService .= 'тариф ';
			}

			if (isset($post['banners'])) {
				$selectedService .= 'баннеры ';
			}

			if (isset($post['pr'])) {
				$selectedService .= 'PR-статьи ';
			}

			if (isset($post['click'])) {
				$selectedService .= 'клики ';
			}

			$post['selected_services'] = $selectedService;
			$model->attributes = $post;

			if ($model->save()) {
				$this->sendMail($model->company, $model->city_name, $model->company_phone,
					$model->email, $model->name, $model->job, $model->site, $model->comment, $model->selected_services);

				$model->unsetAttributes();
				$goodSave = true;
			} else {
				$goodSave = false;
			}
		}

		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-rates-style';

		return $this->render('//content/advert/rates', array(
			'model'    => $model,
			'goodSave' => $goodSave
		));
	}

	public function actionClicks()
	{
		Yii::import('application.modules.catalog.models.StoreOffer');

		$model = new StoreOffer();

		// флаг удачного сохранения
		$goodSave = null;

		$selectedService = '';

		$post = Yii::app()->request->getPost('StoreOffer');

		if (isset($post)) {

			if (isset($post['tariff'])) {
				$selectedService .= 'тариф ';
			}

			if (isset($post['banners'])) {
				$selectedService .= 'баннеры ';
			}

			if (isset($post['pr'])) {
				$selectedService .= 'PR-статьи ';
			}

			if (isset($post['click'])) {
				$selectedService .= 'клики ';
			}

			$post['selected_services'] = $selectedService;
			$model->attributes = $post;

			if ($model->save()) {
				$this->sendMail($model->company, $model->city_name, $model->company_phone,
					$model->email, $model->name, $model->job, $model->site, $model->comment, $model->selected_services);

				$model->unsetAttributes();
				$goodSave = true;
			} else {
				$goodSave = false;
			}
		}

		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-click-style';

		return $this->render('//content/advert/clicks', array(
			'model'    => $model,
			'goodSave' => $goodSave
		));
	}

	public function actionBanners()
	{
		Yii::import('application.modules.catalog.models.StoreOffer');

		$model = new StoreOffer();

		// флаг удачного сохранения
		$goodSave = null;

		$selectedService = '';

		$post = Yii::app()->request->getPost('StoreOffer');

		if (isset($post)) {

			if (isset($post['tariff'])) {
				$selectedService .= 'тариф ';
			}

			if (isset($post['banners'])) {
				$selectedService .= 'баннеры ';
			}

			if (isset($post['pr'])) {
				$selectedService .= 'PR-статьи ';
			}

			if (isset($post['click'])) {
				$selectedService .= 'клики ';
			}

			$post['selected_services'] = $selectedService;
			$model->attributes = $post;

			if ($model->save()) {
				$this->sendMail($model->company, $model->city_name, $model->company_phone,
					$model->email, $model->name, $model->job, $model->site, $model->comment, $model->selected_services);

				$model->unsetAttributes();
				$goodSave = true;
			} else {
				$goodSave = false;
			}
		}

		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-banners-style';

		return $this->render('//content/advert/banners', array(
			'model'    => $model,
			'goodSave' => $goodSave
		));
	}


	public function actionPr()
	{
		Yii::import('application.modules.catalog.models.StoreOffer');

		$model = new StoreOffer();

		// флаг удачного сохранения
		$goodSave = null;

		$selectedService = '';

		$post = Yii::app()->request->getPost('StoreOffer');

		if (isset($post)) {

			if (isset($post['tariff'])) {
				$selectedService .= 'тариф ';
			}

			if (isset($post['banners'])) {
				$selectedService .= 'баннеры ';
			}

			if (isset($post['pr'])) {
				$selectedService .= 'PR-статьи ';
			}

			if (isset($post['click'])) {
				$selectedService .= 'клики ';
			}

			$post['selected_services'] = $selectedService;
			$model->attributes = $post;

			if ($model->save()) {
				$this->sendMail($model->company, $model->city_name, $model->company_phone,
					$model->email, $model->name, $model->job, $model->site, $model->comment, $model->selected_services);

				$model->unsetAttributes();
				$goodSave = true;
			} else {
				$goodSave = false;
			}
		}

		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-pr-style';

		return $this->render('//content/advert/pr', array(
			'model'    => $model,
			'goodSave' => $goodSave
		));
	}


	private function sendMail($company, $cityName, $companyPhone, $companyEmail, $name, $job, $site, $comment, $selectedService)
	{
		// Формируем данные из формы для письма.
		$data_form = '';
		$data_form .= '<p><strong>Компания</strong>: ' . $company . '</p>';
		$data_form .= '<p><strong>Город</strong>: ' . $cityName . '</p>';
		$data_form .= '<p><strong>Телефон</strong>: ' . $companyPhone . '</p>';
		$data_form .= '<p><strong>Email</strong>: ' . $companyEmail . '</p>';
		$data_form .= '<p><strong>ФИО</strong>: ' . $name . '</p>';
		$data_form .= '<p><strong>Должность</strong>: ' . $job . '</p>';
		$data_form .= '<p><strong>Сайт</strong>: ' . $site . '</p>';
		$data_form .= '<p><strong>Комментарий</strong>: ' . $comment . '</p>';
		$data_form .= '<p><strong>Выбранные услуги</strong>: ' . $selectedService . '</p>';

		if(isset(Yii::app()->user->model->email))
		{
			$userEmail = Yii::app()->user->model->email;

		}
		else
		{
			$userEmail = '';
		}


		Yii::app()->mail->create('shopAdvertising')
			->to(array('mms@myhome.ru', 'anna.myhome@yandex.ru'))
			->priority(EmailComponent::PRT_NORMAL)
			->params(array(
				'user_ip'    => $_SERVER['REMOTE_ADDR'],
				'user_agent' => CHtml::encode($_SERVER['HTTP_USER_AGENT']),
				'data_form'  => $data_form,
				'user_email' => $userEmail,
			))
			->send();
	}


	/**
	 * @throws CHttpException
	 * Добавление вопроса из оаздела рекламы
	 */
	public function actionAddQuestionAjax()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);


		if (isset($_POST)) {
			$model = new AdvQuestion();
			$model->attributes = $post = Yii::app()->request->getPost('AdvQuestion');;
			$model->status = $model::STATUS_NEW;
			if ($model->save()) {
				// Формируем данные из формы для письма.
				$data_form = '';
				$data_form .= '<p><strong>Имя</strong>: ' . $model->author_name . '</p>';
				$data_form .= '<p><strong>email</strong>: ' . $model->email . '</p>';
				$data_form .= '<p><strong>Вопрос</strong>: ' . $model->question . '</p>';

				if(isset(Yii::app()->user->model->email))
				{
					$userEmail = Yii::app()->user->model->email;

				}
				else
				{
					$userEmail = '';
				}

				Yii::app()->mail->create('advQuestion')
					->to(array('mms@myhome.ru', 'anna.myhome@yandex.ru', 'sales@myhome.ru'))
					->priority(EmailComponent::PRT_NORMAL)
					->params(array(
						'user_ip'    => $_SERVER['REMOTE_ADDR'],
						'user_agent' => CHtml::encode($_SERVER['HTTP_USER_AGENT']),
						'data_form'  => $data_form,
						'user_email' => $userEmail,
					))
					->send();
			}

			die(json_encode(array(
				'success' => true,
				'error'   => $model->getErrors()
			), JSON_NUMERIC_CHECK));
		}
	}
}