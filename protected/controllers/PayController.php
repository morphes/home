<?php

class PayController extends FrontController
{

	const MRH_LOGIN = "My_home";
	const MRH_PASS1 = "uG212abytwHpk0a5FJXy";
	const MRH_PASS2 = "PoT5nP8xq8VhlXgb08nv";
	const ACTION_URL = 'https://auth.robokassa.ru/Merchant/Index.aspx';
	//const ACTION_URL = 'http://test.robokassa.ru/Index.aspx';


	/**
	 * Инициализация оплаты
	 * На текущее действие нужно передать POST данные (cost, orderId,desc)
	 * @throws CHttpException
	 */
	public function actionProcess()
	{
		//Получаем id тарифа
		$rateId = (int)Yii::app()->request->getParam('rateId');
		$rateModel = SpecialistRate::model()->findByPk($rateId);

		if (!$rateModel) {
			throw new CHttpException(404, 'rate');
		}

		//Получаем период размещения
		$packet = (int)Yii::app()->request->getParam('date');


		//Если период не входит в диапазон разрешенных то
		//отбреваем
		if (!in_array($packet, array(SpecialistRate::PACKET_3, SpecialistRate::PACKET_7, SpecialistRate::PACKET_14))) {
			throw new CHttpException(400);
		}


		switch ($packet) {
			case SpecialistRate::PACKET_3:
				if ($rateModel->discount_3 > 0) {
					$cost = $rateModel->discount_3;
				} else {
					$cost = $rateModel->packet_3;
				}
				break;
			case SpecialistRate::PACKET_7:
				if ($rateModel->discount_7 > 0) {
					$cost = $rateModel->discount_7;
				} else {
					$cost = $rateModel->packet_7;
				}
				break;
			case SpecialistRate::PACKET_14:
				if ($rateModel->discount_14 > 0) {
					$cost = $rateModel->discount_14;
				} else {
					$cost = $rateModel->packet_14;
				}
				break;
			default:
				throw new CHttpException(404);
		}

		//Получаем прайс который видел пользователь и на всякий случай проверяем его
		//С реальным
		$priceFromForm = (int)Yii::app()->request->getParam('totalPrice');

		$cost = (int)$cost;

		if(isset($_POST['in_main']) && (int)round($cost+$cost*0.75)!==$priceFromForm){
			throw new CHttpException(404);
		} elseif (!isset($_POST['in_main']) && $priceFromForm !== $cost) {
			throw new CHttpException(404);
		}


		//Проверяем услугу на существование
		$serviceId = (int)Yii::app()->request->getParam('service');
		$serviceModel = Service::model()->findByPk($serviceId);

		if (!$serviceModel) {
			throw new CHttpException(404);
		}


		//Проверяем город на существование
		$cityId = (int)Yii::app()->request->getParam('city');
		$cityModel = City::model()->findByPk($cityId);


		if (!$cityModel) {
			throw new CHttpException(404);
		}

		$userId = (int)Yii::app()->request->getParam('userId');

		$userModel = User::model()->findByPk($userId);


		if (!$userModel) {
			throw new CHttpException(404);
		}



		//проставляем email
		$userEmail = $userModel->email;

		$payModel = new UserServicePriority();

		if(isset($_POST['in_main'])) {
			$cost = $cost + round($cost*0.75);
			$payModel->in_main = 1;
		}

		$payModel->user_id = $userId;
		$payModel->service_id = $serviceId;
		$payModel->city_id = $cityId;
		$payModel->rate_id = $rateModel->id;
		$payModel->packet = $packet;
		$payModel->date_start = time();
		$payModel->date_end = time() + ($packet * 24 * 60 * 60);
		$payModel->status = UserServicePriority::STATUS_PAY_WAIT;


		#$payModel->status = UserServicePriority::STATUS_PAY_SUCCESS;

		$payModel->save();

		$orderId = $payModel->id;

		$desc = Yii::app()->request->getParam('desc');

		if (is_null($orderId))
			throw new CHttpException(400);

		$data = array(
			'MrchLogin'      => self::MRH_LOGIN,
			'OutSum'         => $cost,
			'InvId'          => $orderId,
			'Desc'           => $desc,
			'SignatureValue' => $this->generateSignature($cost, $orderId),
			'sEmail'         => $userEmail,
		);

		$roboUrl = self::ACTION_URL . '?' . http_build_query($data);

		Yii::app()->request->redirect($roboUrl);
	}


	/**
	 * Верификация проведенного платежа
	 */
	public function actionResult()
	{
		$cost = Yii::app()->request->getParam("OutSum");
		$orderId = Yii::app()->request->getParam("InvId");
		$crc = Yii::app()->request->getParam("SignatureValue");
		$payModel = UserServicePriority::model()->findByPk($orderId);

		if (!$this->checkSignature($crc, $orderId, $cost)) {
			if($payModel) {
				$payModel->status = UserServicePriority::STATUS_PAY_ERROR;
				$payModel->save(false, array('status'));
			}

			$this->renderText('fail', true);
		}
		if($payModel) {
			$payModel->status = UserServicePriority::STATUS_PAY_SUCCESS;
			$payModel->save(false, array('status'));
		}

		$this->renderText("OK$orderId", true);
	}


	/**
	 * Платеж завершен успешно
	 */
	public function actionSuccess()
	{
		$cost = Yii::app()->request->getParam("OutSum");
		$orderId = Yii::app()->request->getParam("InvId");
		$crc = Yii::app()->request->getParam("SignatureValue");

		if (!$this->checkSignature($crc, $orderId, $cost)) {


			$this->renderText('fail', true);
		}


		$this->bodyClass = 'profile payment-status';
		$this->render('success');
	}


	/**
	 * Платеж отменен
	 */
	public function actionFail()
	{
		$cost = Yii::app()->request->getParam("OutSum");
		$orderId = Yii::app()->request->getParam("InvId");

		$this->bodyClass = 'profile payment-status';
		$this->render('fail');
	}


	/**
	 * Генерация цифровой подписи
	 * @param $cost - сумма заказа
	 * @param $orderId - id заказа
	 * @return string
	 */
	private function generateSignature($cost, $orderId)
	{
		$mrh_login = self::MRH_LOGIN;
		$mrh_pass1 = self::MRH_PASS1;
		return md5("$mrh_login:$cost:$orderId:$mrh_pass1");
	}


	/**
	 * Проверка корректности полученной от robokassa сигнатуры
	 * @param $crc
	 * @param $orderId
	 * @param $cost
	 * @return bool
	 */
	private function checkSignature($crc, $orderId, $cost)
	{
		$mrhPass2 = self::MRH_PASS2;
		$crc = strtoupper($crc);
		$my_crc = strtoupper(md5("$cost:$orderId:$mrhPass2"));

		if (strtoupper($my_crc) != strtoupper($crc))
			return false;
		else
			return true;
	}

	public function actionGetPay()
	{
		$data = array(
			'MrchLogin'      => self::MRH_LOGIN,
			'OutSum'         => 6000,
			'InvId'          => 100001,
			'Desc'           => '',
			'SignatureValue' => $this->generateSignature(6000, 100001),
			'sEmail'         => 'v.sekretenko@gmail.com',
		);

		$roboUrl = self::ACTION_URL . '?' . http_build_query($data);

		Yii::app()->request->redirect($roboUrl);

	}
}