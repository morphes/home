<?php

class GalaxytabController extends FrontController
{

	/**
	 * Выбирает указанный интерьер в конкурсе, как лучший.
	 * @param type $id 
	 */
	public function actionSelect($id = null)
	{
		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException (404);
		
		$success = false;
		
		$session = Yii::app()->session;

		// Если есть доступ, показываем список работ...
		if ($session['listGalaxyTab'] != 'access') {
			die(CJSON::encode(array('success' => $success)));
		}
		
		$select = Yii::app()->db->createCommand("SELECT * FROM konkurs_galaxy WHERE interior_id = '".(int)$id."'")->queryRow();


		if ($select)
			Yii::app()->db->createCommand("DELETE FROM konkurs_galaxy WHERE interior_id = ".(int)$select['interior_id']."'");
		else
			Yii::app()->db->createCommand("INSERT INTO konkurs_galaxy (`interior_id`) VALUES ('".(int)$id."')")->execute();

		$success = true;

		die(CJSON::encode(array('success' => $success)));
	}
	
	/**
	 * Список работ, учавствующих в конкурсе.
	 */
	public function actionList()
	{
		$session = Yii::app()->session;
		
		/* Если из формы пришел секретный ключ и он верный, то
		 * делаем в сессии отметку о доступе к странице.
		 */
		if (Yii::app()->request->isPostRequest)
		{
			$key = Yii::app()->request->getParam('secret_key');
			if ($key == 'march')
				$session['listGalaxyTab'] = 'access';
		}

		// Если есть доступ, показываем список работ...
		if ($session['listGalaxyTab'] == 'access') {

			// Получаем список всех отмеченных конкурсов
			$statusAll = Yii::app()->db->createCommand("SELECT * FROM konkurs_galaxy")->queryAll();
			$statusIdea = array();
			foreach($statusAll as $status) {
				$statusIdea[ $status['interior_id'] ] = $status['interior_id'];
			}

			$inteiros = Yii::app()->cache->get('GalaxyTabInteriors');
			if ($inteiros === false)
			{
				/**
				 * Возвращает массив array( array('author_id', 'interior_id'), ...)
				 * для всех интерьеров по кнокурсным условиям.
				 */
				$interios = Yii::app()->db->createCommand("
					SELECT
						i.author_id, i.id as interior_id
					FROM interior i
						LEFT JOIN user u
							ON u.id = i.author_id
						LEFT JOIN user_data as ud ON ud.user_id = u.id
						LEFT JOIN (SELECT author_id, COUNT(*) as cnt FROM interior WHERE status <> 1 OR status <> 5  GROUP BY author_id) tmp
							ON tmp.author_id = i.author_id
					WHERE
						tmp.cnt >= 3
						AND
						i.create_time > 1328029200
						AND
						(i.status = 3 OR i.status = 7)
						AND
						(u.role = '".User::ROLE_SPEC_FIS."' OR u.role = '".User::ROLE_SPEC_JUR."' )
						AND
						u.image_id IS NOT NULL
						AND
						ud.about <>''

					ORDER BY
						i.author_id ASC, i.create_time ASC
				")->queryAll();

				Yii::app()->cache->set('GalaxyTabInteriors', $inteiros, 3600);
			}
			
			$this->render('//idea/galaxy/list', array(
				'interiors' => $interios,
				'statusIdea' => $statusIdea
			));
		}
		else {
			$this->hide_div_content = true;
			$this->spec_div_class = 'nulled';
			
			//...иначе показываем форму входа.
			$this->render('//idea/galaxy/noaccess');
		}
	}

}