<?php

/**
 * Поддержание актуальности рейтинга спецов
 *
 * @author alexsh
 */
class UserServiceCommand extends CConsoleCommand
{
	//Коэффициент заполнености
	public $kZap;

	//Коэффициент RU
	public $rU;

	//Количество отзывов
	public $n;

	public $debug = false;


	public function actionWorker()
	{
		$worker = Yii::app()->gearman->worker();
		$worker->addFunction('userService', array($this, 'update'));
//		$worker->addFunction('userRating', array($this, 'userRating'));

		while ($worker->work()) {
			if (GEARMAN_SUCCESS != $worker->returnCode()) {
				echo "Worker failed: " . $worker->error();
			}
			echo "\n";
		}
	}


	public function update(GearmanJob $job)
	{

		echo date('[Y-m-d H:i:s] ').'SERVICE ';
		$data = $job->workload();
		try {
			$this->kZap = 0.05;
			$this->rU = 0;
			$this->n = 0;

			$params = unserialize($data);
			if (!isset($params['userId']))
				throw new Exception('Invalid parameters ');

			/** Обновление для услуги и пользователя */

			Yii::import('application.modules.member.models.*');
			Yii::import('application.modules.idea.models.*');

			//Рассчет коэффициента заполнености
			$this->countKZap($params['userId']);

			//Рассчет коффициента Ru
			$this->countRu($params['userId']);

			$count = 0;
			if (!empty($params['serviceId'])) {
				$count += $this->countRating($params['userId'], $params['serviceId']);
			} else {
				/** Обновление всех услуг для пользователя */
				$sql = 'SELECT service_id FROM user_service WHERE user_id=' . intval($params['userId']);
				$services = Yii::app()->db->createCommand($sql)->queryAll();
				foreach ($services as $service) {
					$count += $this->countRating($params['userId'], $service['service_id']);
				}
			}
			// Пересчет общего рейтинга
			$this->countRating($params['userId'], null);
//			Yii::app()->gearman->appendJob('userRating', $params['userId']);
			echo 'Affected: ' . $count . ' update data: ' . $data;
		} catch (Exception $e) {
			echo 'Error data: ' . $data . "\n";
			echo $e->getMessage();
		}
	}

	/**
	 * Обновление рейтинга для юзера
	 * @param GearmanJob $job
	 * @throws Exception
	 */
//	public function userRating(GearmanJob $job)
//	{
//		echo date('[Y-m-d H:i:s] ').'RATING ';
//		$data = $job->workload();
//		try {
//			$userId = intval( unserialize($data) );
//			if (empty($userId))
//				throw new Exception('Invalid parameters ');
//
//			// расчет среднего рейтинга по услугам
//			$sql = 'SELECT AVG(rating) FROM user_service_data WHERE user_id=:uid';
//			$rating = Yii::app()->db->createCommand($sql)->bindParam(':uid', $userId)->queryScalar();
//
//			$sql = 'UPDATE user_data SET service_rating=:rating WHERE user_id=:uid';
//
//			$count = Yii::app()->db->createCommand($sql)->bindParam(':rating', $rating)->bindParam(':uid', $userId)->execute();
//
//			echo 'Affected: ' . $count . ' UID: ' . $userId;
//		} catch (Exception $e) {
//			echo 'Error data: ' . $data . "\n";
//			echo $e->getMessage();
//		}
//	}


	/**
	 * Расчет рейтинга специалиста по услуге
	 * @static
	 *
	 * @param $user      CActiveRecord
	 * @param $serviceId integer
	 *
	 * @return bool
	 */
	private function countRating($userId, $serviceId)
	{
		//Коэффициент избранного
		$kFavorite = 0.5;

		//Коэффициент одного проекта по услуге
		$kProject = 0.0075;

		//Наличие фотографии
		$kPhoto = 0.01;

		//Налисие информации о себе
		$kI = 0.03;

		$newRecord = false;

		if (!function_exists('getCountAddFavorite')) {
			function getCountAddFavorite($userId, $serviceType)
			{
				$class = Config::$projectTypes[$serviceType];
				$userId = (int)$userId;
				$countFavorite = 0;

				if (is_array($class)) {
					$projects = array();
					foreach ($class as $typeKey => $c) {
						$tmp = $c::model()->scopeOwnPublic($userId)->findAll();

						if ($tmp) {
							foreach ($tmp as $item) {
								$modelFavorite = FavoriteItem::model()->findByAttributes(array('model' => $c, 'model_id' => $item->id));
								if ($modelFavorite) {
									$countFavorite = $countFavorite + 1;
								}
							}
						}
					}
				} else {
					$projects = $class::model()->scopeOwnPublic($userId)->findAll();

					if ($projects) {
						foreach ($projects as $project) {
							$modelFavorite = FavoriteItem::model()->findByAttributes(array('model' => $class, 'model_id' => $project->id));
							if ($modelFavorite) {
								$countFavorite = $countFavorite + 1;
							}
						}
					}
				}

				return $countFavorite;
			}
		}


		//Наличие личной фотографии в профиле
		if (!User::model()->findByPk($userId)->image_id) {
			$kPhoto = 0;
		}

		//Наличие информации в поле «О себе»

		if (!User::model()->findByPk($userId)->data->about) {
			$kI = 0;
		}

		//Расчет рейтинга по улугам

		$userId = (int)$userId;
		$serviceId = (int)$serviceId;

		//Получаем количество проектов в услуге

		if (empty($serviceId)) {
			$sqlCountRating = 'SELECT SUM(project_qt) FROM user_service_data WHERE user_id=:uid ';
			$projectCountData = Yii::app()->db->createCommand($sqlCountRating)->bindParam(':uid', $userId)->queryScalar();
		} else {
			$sqlCountRating = 'SELECT project_qt FROM user_service_data '
				. 'WHERE user_id=:uid AND service_id=:sid';

			$projectCountData = Yii::app()->db->createCommand($sqlCountRating)->bindParam(':uid', $userId)->bindParam(':sid', $serviceId)->queryScalar();
		}

		if (!is_null($projectCountData)) {
			if($projectCountData>=120){
				$nProject = 120;
			} else{
				$nProject = $projectCountData;
			}
		} else {
			$nProject = 0;
			$newRecord = true;
		}

		//Вычесляем применим ли коэффициент Кзап

		//Получаем список всех услуг специалиста
		$userId = intval($userId);


		//Расчет Ru

		$Rq = $kPhoto + $kI + ($kProject * $nProject) + $this->kZap;

		//Рассчитываем количество добавлений в избранное по данной услуге

		if (empty($serviceId)) {
			$nFav = 0;
			$sql = 'SELECT DISTINCT s.type FROM user_service as us INNER JOIN service as s ON s.id=us.service_id WHERE us.user_id=' . $userId;
			$services = Yii::app()->db->createCommand($sql)->queryColumn();
			foreach ($services as $service) {
				$nFav += getCountAddFavorite($userId, $service);
			}
		} else {
			$serviceModel = Service::model()->findByPk($serviceId);
			$nFav = getCountAddFavorite($userId, $serviceModel->type);
		}


		$rU = $this->rU;
		$n = $this->n;

		$rating = 1000 * ($rU - ($rU - $Rq) /
				(pow($n + 1,
					($n * (1 / 100)) / ($rU + 0.1)
				))) + $nFav * $kFavorite;

		if ($this->debug) {
			echo "\n";
			echo 'Количество добавлений в избранное ' . $nFav;
			echo "\n";

			echo "\n";
			echo 'Фото в профиле ' . $kPhoto;
			echo "\n";

			echo "\n";
			echo 'Информация о себе ' . $kI;
			echo "\n";

			echo "\n";
			echo 'Количество проектов в услуге ' . $nProject;
			echo "\n";

			echo "\n";
			echo 'Кзап ' . $this->kZap;
			echo "\n";

			echo "\n";
			echo 'Ru ' . $rU;
			echo "\n";

			echo "\n";
			echo 'Rq ' . $Rq;
			echo "\n";

			echo "\n";
			echo 'Рейтинг ' . $rating;
			echo "\n";
		}

		//Проверка на эксперта

		$userId = (int)$userId;
		$serviceId = (int)$serviceId;

		if (!empty($serviceId)) {
			$sqlIsExpert = 'SELECT expert FROM user_service '
				. 'WHERE user_id=:uid AND service_id=:sid';

			$isExpert = Yii::app()->db->createCommand($sqlIsExpert)->bindParam(':uid', $userId)->bindParam(':sid', $serviceId)->queryScalar();

			if ($isExpert == 1) {
				$rating = $rating + 25;
			}

			/** Сохранение рейтинга специалиста по услуге */
			if ($newRecord) {
				$rows = Yii::app()->db->createCommand()->insert('user_service_data', array('user_id' => $userId, 'service_id' => $serviceId, 'rating' => $rating, 'project_qt' => 0));
			} else {
				$rows = Yii::app()->db->createCommand()->update('user_service_data', array('rating' => $rating), 'user_id=:uid AND service_id=:sid', array(':uid' => $userId, ':sid' => $serviceId));
			}
		} else {
			$sql = 'UPDATE user_data SET service_rating=:rating WHERE user_id=:uid';
			$rows = Yii::app()->db->createCommand($sql)->bindParam(':rating', $rating)->bindParam(':uid', $userId)->execute();
		}

		return $rows;
	}


	/**
	 * Расчет коээфициента заполенности
	 * @param $userId
	 */
	private function countKZap($userId)
	{
		Yii::import('application.modules.member.models.*');
		Yii::import('application.modules.idea.models.*');

		$userId = (int)$userId;

		//Функция для получения количества фотографий во всех проектах по услуге
		if (!function_exists('getPhotoCount')) {
			function getPhotoCount($userId, $service)
			{
				$class = Config::$projectTypes[$service->type];
				$service_id = $service->id;
				$userId = (int)$userId;
				$countPhoto = 0;

				if (is_array($class)) {
					$projects = array();
					foreach ($class as $typeKey => $c) {
						$tmp = $c::model()->scopeOwnPublic($userId)->findAll();

						if ($tmp) {
							foreach ($tmp as $item) {
								$countPhoto = $countPhoto + $item->count_photos;
							}
						}
					}
				} else {
					$projects = $class::model()->scopeOwnPublic($userId)->findAll();

					if ($projects) {
						foreach ($projects as $project) {
							$countPhoto = $countPhoto + $project->count_photos;
						}
					}
				}

				return $countPhoto;
			}
		}

		$sqlServiceList = 'SELECT * FROM user_service_data '
			. 'WHERE user_id=:uid';

		$serviceListData = Yii::app()->db->createCommand($sqlServiceList)->bindParam(':uid', $userId)->queryAll();

		if ($serviceListData) {

			foreach ($serviceListData as $serviceData) {
				$serviceModel = Service::model()->findByPk($serviceData['service_id']);
				$categoryType = $serviceModel->category_type;
				$countPhoto = getPhotoCount($serviceData['user_id'], $serviceModel);

				switch ($categoryType) {
					case 1:
						if ($serviceData['project_qt'] < 5) {
							$this->kZap = 0;
							break;
						}
						if ($countPhoto < 25) {
							$this->kZap = 0;
							break;
						}
						break;
					case 2:
						if ($serviceData['project_qt'] < 3) {
							$this->kZap = 0;
							break;
						}
						if ($countPhoto < 10) {
							$this->kZap = 0;
							break;
						}
						break;
					case 3:
						break;
					default:
						break;
				}
			}
		}
	}


	/**
	 * @param $userId
	 */
	private function countRu($userId)
	{
		$userId = (int)$userId;
		Yii::import('application.modules.member.models.*');
		Yii::import('application.modules.idea.models.*');

		//Расчитываем коэффициент Ru
		$sqlReview = 'SELECT count(rating) as count,
					SUM(IF(rating=1, 0,
						IF(rating = 2, 0.25,
							IF(rating=3, 0.4,
								IF(rating=4,0.6,
									IF(rating=5,1,NULL)))))) as rating
			      FROM `review` WHERE type=' . Review::TYPE_REVIEW . ' AND status=' . Review::STATUS_SHOW . ' AND spec_id=' . $userId;

		$reviewsRating = Yii::app()->db->createCommand($sqlReview)->queryRow();

		if ($reviewsRating['rating'] !== null) {
			$this->n = $reviewsRating['count'];
			$this->rU = $reviewsRating['rating'] / $reviewsRating['count'];
		} else {
			$this->n = 0;
			$this->rU = 0;
		}
	}


	/**
	 * Ручной запуск пересчета рейтинга специалиста по услуге
	 * с дебаг режимом
	 * @param $userId
	 * @param $serviceId
	 */
	public function actionCountRatingDebug($userId, $serviceId)
	{
		$this->kZap = 0.05;
		$this->rU = 0;
		$this->n = 0;

		$this->debug = true;
		//Рассчет коэффициента заполнености
		$this->countKZap($userId);

		//Рассчет коффициента Ru
		$this->countRu($userId);

		$count = 0;
		if (!empty($serviceId)) {
			$this->countRating($userId, $serviceId);
		}
	}
}