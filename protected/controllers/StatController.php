<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 02.07.13
 * Time: 8:58
 * To change this template use File | Settings | File Templates.
 */

class StatController extends FrontController
{
	/**
	 * Статистика по клику профиля
	 * в листе специалистов
	 *
	 */
	public function actionStatHitAjax()
	{

		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400);
		}


		$post = Yii::app()->request->getPost('item');

		if (!isset($post['userId'])) {
			throw new CHttpException(400, 'Error data!');
		}

		if (!isset($post['serviceId'])) {
			throw new CHttpException(400, 'Error data!');
		}

		if (!isset($post['cityId'])) {
			throw new CHttpException(400, 'Error data!');
		}

		$userId = (int)$post['userId'];
		$serviceId = (int)$post['serviceId'];
		$cityId = (int)$post['cityId'];

		Yii::import('application.modules.member.models.*');

		StatUserService::model()->hit($userId, $serviceId, $cityId, StatUserService::TYPE_CLICK_PROFILE_SERVICE);

		die(json_encode(array('success' => true), JSON_NUMERIC_CHECK));
	}
}