<?php

/**
 * @brief This is the class for mailing
 */
class Mail
{
	/**
	 * Функция отправлет пользователю $user напоминание по шаблону с кулючем $key_templ.
	 * 
	 * @param string $key_templ Ключ шаблона письма для отправки
	 * @param array $user Ассоциативный массив с данными пользователя
	 * @param string $data Дополнительная переменная для передачи данных в шаблон
	 * @return boolean true - Если удачно сохранилось письмо в очередь
	 */
	private static function sendUserNotifierMail($key_templ, $user, $data = '', $useView=true)
	{
                Yii::app()->mail->create($key_templ)
                        ->to($user['email'])
                        ->priority(EmailComponent::PRT_LOW)
			->useView($useView)
                        ->params(array(
                                'user_name'	  	=> $user['firstname'].' '.$user['lastname'],
                                'sign_A'	  	=> Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                                'data'	  	=> $data,
                                'login'		=> $user['login'],
                                'activate_link' 	=> CHtml::link(
                                        Yii::app()->homeUrl . '/site/activation/key/' . $user['activateKey'],
                                        Yii::app()->homeUrl . '/site/activation/key/' . $user['activateKey']
                                )))
                        ->send();

                return true;
	}
	
	/**
	 * "Приглашение к регистрации не принято".
	 * 
	 * Отправляется дизайнеру если он имеет статус
	 * "На подтверждении аккаунта" и зарегистрировался
	 * $firstPeriodReg, $secondPeriodReg или $thirdPeriodReg дней назад.
	 * 
	 * В условии выборки пользователей $firstPeriodReg, $secondPeriodReg и $thirdPeriodReg
	 * переводятся в секунды, и береться интервал 1 день, в течение которого
	 * должен был зарегистрироваться пользователь.
	 * 
	 * @param integer $firstPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 * 
	 * @param integer $secondPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 *
	 * @param integer $thirdPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 * 
	 * @return array Массив с двумя числами, обозначающими кол-во "успешно" и 
	 * "неуспешно" сохраненных писем для отправки.
	 * @throws CException
	 */
	public static function notAcceptedInvite($firstPeriodReg = 7, $secondPeriodReg = 14, $thirdPeriodReg = 28)
	{
		$users = Yii::app()->db->createCommand("
			SELECT user.*
			FROM user
			WHERE
				status = '".User::STATUS_VERIFYING."'
				AND
				((
					create_time + ".($firstPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($firstPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($secondPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($secondPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($thirdPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($thirdPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				))
				AND
				(
					user.role = '".User::ROLE_SPEC_FIS."'
					OR
					user.role = '".User::ROLE_SPEC_JUR."'
				)
		")->queryAll();
		
		$result = array('success' => 0, 'fail' => 0);
		foreach ($users as $user) {
			
			// Отправляем письмо.
			if (self::sendUserNotifierMail('notAcceptedInvite', $user, '', false))
				$result['success']++;
		}
		return $result;
	}
	
	
	/**
	 * "Приглашение принято, проекты не выложены"
	 * 
	 * Отправляется дизайнеру, если он имеет статус "Активен"
	 * и зарегистрировался $firstPeriodReg, $secondPeriodReg или $thirdPeriodReg дней назад.
	 * Также обязательно проверяем, чтобы у него была указана хотябы одна услуга.
	 *
	 *
	 * В условии выборки пользователей $firstPeriodReg, $secondPeriodReg и $thirdPeriodReg
	 * переводятся в секунды, и береться интервал 1 день, в течение которого
	 * должен был зарегистрироваться пользователь.
	 * 
	 * @param integer $firstPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 * 
	 * @param integer $secondPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 *
	 * @param integer $thirdPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 * 
	 * 
	 * @return array Массив с двумя числами, обозначающими кол-во "успешно" и 
	 * "неуспешно" сохраненных писем для отправки.
	 * @throws CException 
	 */
	public static function acceptedNoProjects($firstPeriodReg = 7, $secondPeriodReg = 14, $thirdPeriodReg = 28)
	{
		$users = Yii::app()->db->createCommand("
			SELECT user.*
			FROM user
			LEFT JOIN (
				SELECT author_id
				FROM `interior`
				WHERE
					status <> '".Interior::STATUS_DELETED."'
					AND
					status <> '".Interior::STATUS_MAKING."'
				GROUP BY author_id
			) as tmp
				ON tmp.author_id = user.id
			LEFT JOIN (
				SELECT author_id
				FROM `portfolio`
				WHERE
					status <> '".Interior::STATUS_DELETED."'
					AND
					status <> '".Interior::STATUS_MAKING."'
				GROUP BY author_id
			) as tmp_portf
				ON tmp_portf.author_id = user.id
			LEFT JOIN (
				SELECT user_id
				FROM user_service
				GROUP BY user_id
			) as tmp_service
				ON tmp_service.user_id = user.id
			WHERE
				tmp.author_id IS NULL
				AND
				tmp_portf.author_id IS NULL
				AND
				tmp_service.user_id IS NOT NULL
				AND
				status = '".User::STATUS_ACTIVE."'
				AND
				((
					create_time + ".($firstPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($firstPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($secondPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($secondPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($thirdPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($thirdPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				))
				AND
				(
					user.role = '".User::ROLE_SPEC_FIS."'
					OR
					user.role = '".User::ROLE_SPEC_JUR."'
				)
		")->queryAll();



		$result = array('success' => 0, 'fail' => 0);
		foreach ($users as $user) {

			$portfolioUrl = Yii::app()->homeUrl.'/users/'.$user['login'].'/portfolio';
			$data = '
				<p>- <a href="'.$portfolioUrl.'">Добавить в <strong>портфолио</strong></a> проекты,
				демонстрирующие ваше мастерство в выбранных специализациях</p>
			';

			// Отправляем письмо.
			if (self::sendUserNotifierMail('noConditions', $user, $data))
				$result['success']++;
		}
		return $result;
	}

	/**
	 * "Приглашение принято, не указано ни одной услуги"
	 *
	 * Отправляется дизайнеру, если он имеет статус "Активен",
	 * и зарегистрировался $firstPeriodReg, $secondPeriodReg или $thirdPeriodReg дней назад.
	 * и не указал ни одной услуги.
	 *
	 *
	 * В условии выборки пользователей $firstPeriodProject, $secondPeriodProject и $thirdPeriodReg
	 * переводятся в секунды, и береться интервал 1 день, в течение которого
	 * должен был зарегистрироваться пользователь.
	 *
	 * @param integer $firstPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 *
	 * @param integer $secondPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 *
	 * @param integer $thirdPeriodReg Определяет сколько дней назад должен был
	 * зарегистрироваться пользователь, чтобы мы ему отправляли уведомление.
	 *
	 * @return array Массив с двумя числами, обозначающими кол-во "успешно" и
	 * "неуспешно" сохраненных писем для отправки.
	 * @throws CException
	 */
	public static function acceptedNoService($firstPeriodReg = 7, $secondPeriodReg = 14, $thirdPeriodReg = 28)
	{
		$users = Yii::app()->db->createCommand("
			SELECT user.*
			FROM user
			LEFT JOIN (
				SELECT user_id
				FROM user_service
				GROUP BY user_id
			) as tmp_service
				ON tmp_service.user_id = user.id
			WHERE
				tmp_service.user_id IS NULL
				AND
				status = '".User::STATUS_ACTIVE."'
				AND
				((
					create_time + ".($firstPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($firstPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($secondPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($secondPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($thirdPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($thirdPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				))
				AND
				(
					user.role = '".User::ROLE_SPEC_FIS."'
					OR
					user.role = '".User::ROLE_SPEC_JUR."'
				)
		")->queryAll();

		$result = array('success' => 0, 'fail' => 0);

		foreach ($users as $user) {

			$serviceUrl = Yii::app()->homeUrl.'/users/'.$user['login'].'/services';

			$data = '
				<p>- <a href="'.$serviceUrl.'">Выбрать <strong>услуги</strong></a>, которые вы оказываете,
				и указать ваш <strong>стаж работы</strong> по каждой из услуг</p>
			';

			// Отправляем письмо.
			if (self::sendUserNotifierMail('noConditions', $user, $data))
				$result['success']++;
		}
		return $result;
	}
	
	
	/**
	 * "Приглашение принято, выложено менее 3-х проектов"
	 * 
	 * Отправляется дизайнеру если он имеет статус "Активен",
	 * у него менее 3-х работ и прошло $firstPeriodProject, $secondPeriodProject или $thirdPeriodReg дней с последнего
	 * размещения работы.
	 * 
	 * В условии выборки пользователей $firstPeriodProject, $secondPeriodProject и $thirdPeriodReg
	 * переводятся в секунды, и береться интервал 1 день, в течение которого
	 * был выложен последний проект
	 * 
	 * @param integer $firstPeriodProject Определяет сколько дней назад был
	 * добавлен последний проект.
	 * 
	 * @param integer $secondPeriodProject Определяет сколько дней назад был
	 * добавлен последний проект
	 *
	 * @param integer $thirdPeriodReg Определяет сколько дней назад был
	 * добавлен последний проект
	 * 
	 * @return array Массив с двумя числами, обозначающими кол-во "успешно" и 
	 * "неуспешно" сохраненных писем для отправки.
	 * @throws CException 
	 */
	public static function acceptedLess3Projects($firstPeriodProject = 7, $secondPeriodProject = 14, $thirdPeriodReg = 28)
	{
		$users = Yii::app()->db->createCommand("
			SELECT user.*
			FROM user
			LEFT JOIN (
				SELECT COUNT(id) as cnt, author_id
				FROM `interior`
				WHERE
					status <> ".Interior::STATUS_DELETED."
					AND
					status <> ".Interior::STATUS_MAKING."
				GROUP BY author_id
			) as tmp
				ON tmp.author_id = user.id
			LEFT JOIN (
				SELECT COUNT(id) as cnt, author_id
				FROM `portfolio`
				WHERE
					status <> ".Portfolio::STATUS_DELETED."
					AND
					status <> ".Portfolio::STATUS_MAKING."
				GROUP BY author_id
			) as tmp_portf
				ON tmp_portf.author_id = user.id
			LEFT JOIN (
				SELECT user_id
				FROM user_service
				GROUP BY user_id
			) as tmp_service
				ON tmp_service.user_id = user.id
			WHERE
				tmp_service.user_id IS NOT NULL
				AND
				((CASE WHEN tmp_portf.cnt IS NULL THEN 0 ELSE tmp_portf.cnt END) + (CASE WHEN tmp.cnt IS NULL THEN 0 ELSE tmp.cnt END)) < 3
				AND
				((CASE WHEN tmp_portf.cnt IS NULL THEN 0 ELSE tmp_portf.cnt END) + (CASE WHEN tmp.cnt IS NULL THEN 0 ELSE tmp.cnt END)) > 0
				AND
				status = '".User::STATUS_ACTIVE."'
				AND
				((
					create_time + ".($firstPeriodProject * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($firstPeriodProject * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($secondPeriodProject * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($secondPeriodProject * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($thirdPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($thirdPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				))
				AND
				(
					user.role = '".User::ROLE_SPEC_FIS."'
					OR
					user.role = '".User::ROLE_SPEC_JUR."'
				)


		")->queryAll();
		
		
		$result = array('success' => 0, 'fail' => 0);
		foreach ($users as $user) {


			$portfolioUrl = Yii::app()->homeUrl.'/users/'.$user['login'].'/portfolio';
			$data = '
				<p>- <a href="'.$portfolioUrl.'">Добавить в <strong>портфолио</strong></a> больше проектов,
				демонстрирующих ваше мастерство в выбранных специализациях</p>
			';

			// Отправляем письмо.
			if (self::sendUserNotifierMail('noConditions', $user, $data))
				$result['success']++;
		}
		return $result;
	}
	
	
	/**
	 * "Приглашение принято, выложил более 3-х проектов, не заполнена личная информация"
	 * 
	 * Отправляется дизайнеру, если он имеет статус "Активен",
	 * у него 3 и более проекта, НЕ заполнена информация "О себе" или "Фотография",
	 * а также прошло 7 или 14 дней с последнего размещения работы.
	 *
	 * В условии выборки пользователей $firstPeriodProject, $secondPeriodProject и $thirdPeriodReg
	 * переводятся в секунды, и береться интервал 1 день, в течение которого
	 * был выложен последний проект
	 * 
	 * @param integer $firstPeriodProject Определяет сколько дней назад был
	 * добавлен последний проект.
	 * 
	 * @param integer $secondPeriodProject Определяет сколько дней назад был
	 * добавлен последний проект
	 *
	 * @param integer $thirdPeriodReg Определяет сколько дней назад был
	 * добавлен последний проект
	 * 
	 * @return array Массив с двумя числами, обозначающими кол-во "успешно" и 
	 * "неуспешно" сохраненных писем для отправки.
	 * @throws CException 
	 */
	public static function more3ProjectsEmptyInfo($firstPeriodProject = 7, $secondPeriodProject = 14, $thirdPeriodReg = 28)
	{
		$users = Yii::app()->db->createCommand("
			SELECT user.*
			FROM user
			INNER JOIN user_data as ud ON ud.user_id=user.id
			LEFT JOIN (
				SELECT COUNT(id) as cnt, author_id
				FROM `interior`
				WHERE
					status <> ".Interior::STATUS_DELETED."
					AND
					status <> ".Interior::STATUS_MAKING."
				GROUP BY author_id
			) as tmp
				ON tmp.author_id = user.id
			LEFT JOIN (
				SELECT COUNT(id) as cnt, author_id
				FROM `portfolio`
				WHERE
					status <> ".Interior::STATUS_DELETED."
					AND
					status <> ".Interior::STATUS_MAKING."
				GROUP BY author_id
			) as tmp_portf
				ON tmp_portf.author_id = user.id
			WHERE
				((CASE WHEN tmp_portf.cnt IS NULL THEN 0 ELSE tmp_portf.cnt END) + (CASE WHEN tmp.cnt IS NULL THEN 0 ELSE tmp.cnt END)) >= 3
				AND
				(
					user.image_id IS NULL
					OR
					ud.about = ''
				)
				AND
				status = '".User::STATUS_ACTIVE."'
				AND
				((
					create_time + ".($firstPeriodProject * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($firstPeriodProject * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($secondPeriodProject * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($secondPeriodProject * 86400 + 86400)." > UNIX_TIMESTAMP()
				)
				OR
				(
					create_time + ".($thirdPeriodReg * 86400)." < UNIX_TIMESTAMP()
					AND
					create_time + ".($thirdPeriodReg * 86400 + 86400)." > UNIX_TIMESTAMP()
				))
				AND
				(
					user.role = '".User::ROLE_SPEC_FIS."'
					OR
					user.role = '".User::ROLE_SPEC_JUR."'
				)
		")->queryAll();

		$settingsUrl = Yii::app()->homeUrl.'/member/profile/settings';
		$data = '
			<p>- <a href="'.$settingsUrl.'">Добавить свою <strong>фотографию</strong></a></p>
			<p>- <a href="'.$settingsUrl.'">Заполнить поле «<strong>О себе</strong>»</a></p>
		';
		
		$result = array('success' => 0, 'fail' => 0);
		foreach ($users as $user) {

			// Отправляем письмо.
			if (self::sendUserNotifierMail('noConditions', $user, $data))
				$result['success']++;
		}
		return $result;
	}
}