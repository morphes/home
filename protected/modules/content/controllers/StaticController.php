<?php

/**
 * @brief Вывод кастомизованных статических страниц
 * @author Alexey Shvedov
 */
class StaticController extends FrontController
{

	/**
	 * Stream zcapitel
	 * @return string
	 */
	public function actionZkapitel()
        {

                return $this->render('//content/static/zkapitel', array());
        }


	/**
	 * Страница конкурса
	 * ipad за спасибо
	 * @return string
	 */
	public function actionIForThanks()
	{
		$this->bodyClass = 'competitions ipad';
		$this->setPageTitle('Акция «iPad4 за спасибо» — MyHome.ru');
		return $this->render('//content/static/iforthanks', array());
	}

	/**
	 * Страница конкурса
	 * ipad за спасибо
	 * @return string
	 */
	public function actionIForThanksTotal()
	{
		$this->bodyClass = 'competitions ipad result';
		$this->setPageTitle('Итоги акции «iPad4 за спасибо» — MyHome.ru');
		return $this->render('//content/static/iforthankstotal', array());
	}


	/**
	 * Ajac метод для рассылки
	 * инвайта со страницы конкурса
	 * @throws CHttpException
	 */
	public function actionAjaxSendInvite()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = Yii::app()->request->getPost('item');
		$email = $post['email'];

		if (!$email) {
			throw new CHttpException(400, 'error data');
		}

		$email = trim($email);

		$validator = new CEmailValidator;

		if (!$validator->validateValue($email)) {
			die(json_encode(array(
				'success' => false,
			), JSON_NUMERIC_CHECK));
		}

		$user = Yii::app()->user->getModel();

		if (!$user) {
			throw new CHttpException(400, 'error data');
		}

		Yii::app()->mail->create('inviteUserSpec')
			->to($email)
			->Subject($user->name . ' приглашает вас зарегистрироваться на Myhome.ru')
			->useView(false)
			->params(array(
				'referrer_name' => $user->name,
			))
			->send();

		//Складируем в редис, так как в будущем понадобиться выборка
		Yii::app()->redis->set('USER_SPEC_INVITE:' . $user->id . ':TIME:' . time(), $email);

		die(json_encode(array(
			'success' => true,
		), JSON_NUMERIC_CHECK));
	}

	public function actionSpecRules()
	{
		$this->bodyClass = 'static static-rating';
		$this->layout = '//layouts/new_main';
		$this->setPageTitle('Система рейтинга — Специалистам — MyHome.ru');
		return $this->render('//content/static/specRules', array());
	}

	public function actionCompetition()
	{
		$this->bodyClass = 'competitions index';
		$this->layout = '//layouts/new_main';
		$this->setPageTitle('Конкурсы для специалистов');
		return $this->render('//content/static/competition', array());
	}

    public function actionCupons()
    {
        $this->bodyClass = 'competitions index';
        $this->layout = '//layouts/new_main';
        $this->setPageTitle('Конкурсы для специалистов');
        return $this->render('//content/static/cupons', array());
    }

}
