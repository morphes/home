<?php

class DefaultController extends FrontController
{
	public function actionIndex()
	{
		$this->redirect('/journal/knowledge',true,  301);

		$cs = Yii::app()->getClientScript();
		$cs->registerCssFile('/css/media.css');
		$cs->registerScriptFile('/js/CMedia.js');

		$this->menuActiveKey = 'journal';

		// -- Тематики --
		$themes = MediaTheme::model()->findAll(array(
			'condition' => 'status = :st',
			'params'    => array(':st' => MediaTheme::STATUS_ACTIVE),
			'order'     => 'pos ASC'
		));

		$promoBlocks = MediaPromo::model()->findAllByAttributes(
			array(
				'status' => MediaPromo::STATUS_PUBLIC,
			),
			array(
				'order' => 'update_time DESC',
				'limit' => '4'
			)
		);

		// -- ЛЮДИ ГОВОРЯТ --

		$peoples = MediaPeople::model()->findAll(array(
			'condition' => 'status = :st',
			'params' => array(
				':st' => MediaPeople::STATUS_PUBLIC
			),
			'limit' => 4,
			'order' => 'update_time DESC'
		));


		// Самые читаемые материалы
		$bestReading = $this->getBestReading(7);
		if (empty($bestReading)) {
			$bestReading = $this->getBestReading(30);
		}


		$this->render('//media/default/index', array(
			'themes'      => $themes,
			'promoBlocks' => $promoBlocks,
			'peoples'     => $peoples,
			'bestReading' => $bestReading
		));
	}

	/**
	 * Получает и возвращает список самых читаемых записей из журнала за последние $lastDays дней
	 * @param int $lastDays Кол-во дней, за который нужно учитывать записи.
	 * @return array MediaNews|MediaKnowledge
	 */
	private function getBestReading($lastDays)
	{
		$bestReading = array();

		// Последний период в секундах
		$lastSeconds = $lastDays * 86400;

		// -- НОВОСТИ --
		$bestNews = MediaNew::model()->findAll(array(
			'condition' => "status = :st AND public_time <= :pt1 AND public_time >= :pt2",
			'params' => array(
				':st' => MediaNew::STATUS_PUBLIC,
				':pt1' => time(),
				':pt2' => time() - $lastSeconds
			),
			'order' => 'public_time DESC'
		));
		foreach($bestNews as $new) {
			$bestReading[ $new->count_view ] = $new;
		}

		// -- ЗНАНИЯ --
		$bestKnowledge = MediaKnowledge::model()->findAll(array(
			'condition' => "status = :st AND public_time <= :pt1 AND public_time >= :pt2",
			'params' => array(
				':st' => MediaKnowledge::STATUS_PUBLIC,
				':pt1' => time(),
				':pt2' => time() - $lastSeconds
			),
			'order' => 'public_time DESC'
		));
		foreach($bestKnowledge as $know) {
			$bestReading[ $know->count_view ] = $know;
		}

		krsort($bestReading);

		$bestReading = array_slice($bestReading, 0, 8);


		return $bestReading;
	}

	/**
	 * Метод возвращает список последних записей из раздела "Журнал"
	 */
	public function actionAjaxLastItems()
	{
		// Получаем Тематику
		$idTheme = (int)Yii::app()->getRequest()->getParam('id');
		$theme = MediaTheme::model()->findByPk($idTheme);

		// -- ЗНАНИЯ --
		$knowledges = MediaKnowledge::model()->published()->findAll(array(
			'condition' => "mts.theme_id = :idTheme AND mts.model = :model",
			'params'    => array(
				':idTheme' => $idTheme,
				':model'   => 'MediaKnowledge'
			),
			'limit'     => '8',
			'join'      => "LEFT JOIN media_theme_select mts ON mts.model_id = id",
		));


		// -- НОВОСТИ --
		$news = MediaNew::model()->published()->findAll(array(
			'condition' => "mts.theme_id = :idTheme AND mts.model = :model",
			'params'    => array(
				':idTheme' => $idTheme,
				':model'   => 'MediaNew'
			),
			'limit'     => '8',
			'join'      => "LEFT JOIN media_theme_select mts ON mts.model_id = id",
		));

		$html = $this->renderPartial('//media/default/_ajaxLastItems', array(
			'knowledges' => $knowledges,
			'news'       => $news,
			'idTheme'    => $idTheme,
			'theme'      => $theme
		), true);

		exit(json_encode(array(
			'success' => true,
			'html'    => $html
		)));
	}
}