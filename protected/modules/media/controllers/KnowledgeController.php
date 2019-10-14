<?php

class KnowledgeController extends FrontController
{

	public function beforeAction($action)
	{
		$this->menuActiveKey              = 'journal_knowledge';
		$this->menuIsActiveLink           = true;
		$this->menuIsActiveLinkOnlyParent = true;

		Yii::app()->getClientScript()->registerCssFile('/css-new/generated/media.css');
		Yii::app()->getClientScript()->registerCssFile('/css/media.css');
		Yii::app()->getClientScript()->registerScriptFile('/js/CMedia.js');
		Yii::app()->getClientScript()->registerScriptFile('/js-new/media.js');



		return parent::beforeAction($action);
	}

	public function actionIndex($section_url = null)
	{
		// Получаем и устанавливаем кол-во элементов на странице

		if (Yii::app()->request->getParam('pagesize'))
			$pageSize = Yii::app()->request->getParam('pagesize');
		else
			$pageSize = Yii::app()->session->get('media_pagesize');

		$pageSize = empty(Config::$mediaPageSizes[(int)$pageSize])
			    ? key(Config::$mediaPageSizes)
			    : (int)$pageSize;
		Yii::app()->session->add('media_pagesize', $pageSize);

		// ПОлучаем значение из фильтра слева
		$filter = $this->getFilter($section_url);

		// Формируем выдачу с учетом фильтра
		$criteria = $this->getCriteria($filter);

		$count = MediaKnowledge::model()->count($criteria);
		$pages = new CPagination($count);

		$pages->pageSize = $pageSize;
		$pages->applyLimit($criteria);


		// -- Тематики ---
		$themes = MediaTheme::model()->findAllByAttributes(
			array('status' => MediaTheme::STATUS_ACTIVE),
			array('order' => 'pos ASC')
		);


		// -- Похожие новости --
		$sameNews = MediaNew::model()->published()->findAll(array(
			'condition' => "mts.theme_id = :idTheme AND mts.model = :model",
			'params' => array(
				':idTheme' => $filter['f_theme'],
				':model' => 'MediaNew'
			),
			'limit' => '3',
			'join' => "LEFT JOIN media_theme_select mts ON mts.model_id = id",
		));



		// Получаем провайдер для статей
		$knowledgeProvider = new CActiveDataProvider('MediaKnowledge', array(
			'criteria'   => $criteria,
			'pagination' => $pages
		));


		$this->render('//media/knowledge/index', array(
			'knowledgeProvider' => $knowledgeProvider,
			'pageSize'          => $pageSize,
			'themes'            => $themes,
			'filter'            => $filter,
			'sameNews'	    => $sameNews
		));
	}

	/**
	 * Возвращает установки фильтра для Знаний
	 * @return array
	 */
	public function getFilter($section_url = null)
	{
		$filter = array();

		$filter['f_theme'] = Yii::app()->request->getParam('f_theme');
		$filter['f_whom'] = Yii::app()->request->getParam('f_whom');
		$filter['f_genre'] = Yii::app()->request->getParam('f_genre');
		$filter['f_viewtype'] = Yii::app()->request->getParam('f_viewtype');
		$filter['sorttype'] = Yii::app()->request->getParam('sorttype');
		$filter['author_id'] = Yii::app()->request->getParam('author_id');
		$filter['section_url'] = ($section_url ? trim($section_url) : null);


		return $filter;
	}

	/**
	 * Возвращает критерий по выбору данных из таблицы Знания
	 * @return CDbCriteria
	 */
	public function getCriteria($filter)
	{
		$criteria = new CDbCriteria();

		$criteria->condition = 'status = :st';
		$criteria->params = array(':st' => MediaKnowledge::STATUS_PUBLIC);

		// Фильтрация по ТЕМАТИКАМ
		if (!empty($filter['f_theme'])) {
			$criteria->join = 'LEFT JOIN media_theme_select mts ON mts.model_id = id';

			$criteria->condition = $criteria->condition
				. ' AND mts.theme_id in (:themeId) AND mts.model = :model';

			$criteria->params[':themeId'] = (int)$filter['f_theme'];
			$criteria->params[':model']   = 'MediaKnowledge';
		}

		if (!empty($filter['section_url'])) {
			$criteria->condition = $criteria->condition . ' AND section_url=:section_url';
			$criteria->params[':section_url'] = $filter['section_url'];
		}

		// Фильтрация по "Кому это интересно"
		if (!empty($filter['f_whom']))
		{
			if ($filter['f_whom'] == MediaKnowledge::WHOM_SPEC) {

				$criteria->condition = $criteria->condition
					.' AND (whom_interest = "'.MediaKnowledge::WHOM_SPEC.'"'
					.' OR whom_interest = "'.MediaKnowledge::WHOM_SPEC_USER.'")';

			} elseif ($filter['f_whom'] == MediaKnowledge::WHOM_USER) {

				$criteria->condition = $criteria->condition
					.' AND (whom_interest = "'.MediaKnowledge::WHOM_USER.'"'
					.' OR whom_interest = "'.MediaKnowledge::WHOM_SPEC_USER.'")';
			} else {

				$criteria->condition = $criteria->condition
					.' AND (whom_interest = "'.MediaKnowledge::WHOM_SPEC.'"'
					.' OR whom_interest = "'.MediaKnowledge::WHOM_USER.'"'
					.' OR whom_interest = "'.MediaKnowledge::WHOM_SPEC_USER.'")';
			}
		}

		// Фильтрация по "Жанрам"
		if (!empty($filter['f_genre'])) {
			$criteria->compare('genre', $filter['f_genre']);
		}

		if ( !empty($filter['author_id']) ) {
			$criteria->compare('author_id', $filter['author_id']);
			$criteria->addCondition('author_name=""');
		}



		// Соритровка
		switch($filter['sorttype'])
		{
			case MediaKnowledge::SORT_COMMENT:
				$criteria->order = 'count_comment DESC';
				break;
			case MediaKnowledge::SORT_VIEW:
				$criteria->order = 'count_view DESC';
				break;
			default:
				$criteria->order = 'public_time DESC';
				break;
		}

		/* Выводим только те работы, время публикации
		   которых уже наступило */
		$criteria->addCondition('public_time <= :pub_time');
		$criteria->params[':pub_time'] = time();


		return $criteria;
	}

	/**
	 * Детальная страница Знания
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionDetail($id)
	{
		$this->menuIsActiveLinkOnlyParent = false;
		$this->bodyClass = 'article';

		Yii::import('application.modules.member.models.LikeItem');

		/** @var $model MediaKnowledge */
		$model = MediaKnowledge::model()->findByPk((int)$id);

		if (!$model) {
			throw new CHttpException(404);
		}


		// Увиличиваем кол-во просмотров
		$model->incrementView();


		$themes = MediaTheme::model()->findAllByPk($model->themes);

		// Собираем список ID тем, к которым относится запись
		$ids = array();
		foreach ($themes as $theme) {
			$ids[] = $theme->id;
		}

		//Блок популярные товары

		Yii::import('application.modules.catalog.models.Category');
		Yii::import('application.modules.catalog.models.Product');

		if (Yii::app()->user->getSelectedCity()) {
			$city = Yii::app()->user->getSelectedCity();
		} else {
			$city = Yii::app()->user->getDetectedCity();
		}

//		$relatedProducts = Product::model()->getRelatedProductsInMedia($model, $city->id, 5);
        $relatedProducts = array();


		$relatedCategories = Category::model()->getRelatedCategoryInMedia($model, 10);

		//Дата провайдер для блока интересное в статье
		$limit = InterestData::PAGE_LIMIT;

		$interestProvider = new CActiveDataProvider(InterestData::model(), array(
			'pagination' => array(
				'pageSize'    => (int)$limit,
				'pageVar'     => 'page',
				'currentPage' => 0
			),
		));
		

		$this->render('//media/knowledge/detailGrid', array(
			'model'             => $model,
			'themes'            => $themes,
			'relatedProducts'   => $relatedProducts,
			'relatedCategories' => $relatedCategories,
			'interestProvider'  => $interestProvider,
		));
	}

	/**
	 * Получает и возвращает галлерею для детального описания статьи на фронте
	 */
	public function actionAjaxGetGallery()
	{
		$modelId = (int)$_POST['modelId'];
		$numGallery = (int)$_POST['num'];

		$models = MediaGallery::model()->findAllByAttributes(array(
			'model' => 'MediaKnowledge',
			'model_id' => $modelId,
			'num' => $numGallery
		));



		$html = $this->renderPartial('_ajaxGallery', array(
			'models' => $models,
			'numGallery' => $numGallery
		), true);

		die(json_encode(array(
			'success' => true,
			'html' => $html
		)));
	}

	/**
	 * Ajax метод для получения
	 * следующей блока "Интересное"
	 * в статье
	 * @throws CHttpException
	 */
	public function actionAjaxInterest()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$limit = InterestData::PAGE_LIMIT;

		$page = Yii::app()->request->getParam('page');

		$interestProvider = new CActiveDataProvider(InterestData::model(), array(
			'pagination' => array(
				'pageSize'    => (int)$limit,
				'pageVar'     => 'page',
				'currentPage' => $page,
			),
		));

		$html = $this->renderPartial('//media/interest/_list',
			array('interestProvider' => $interestProvider), true);

		die(json_encode(array(
			'success' => true,
			'html'    => $html,
		), JSON_NUMERIC_CHECK));
	}
}