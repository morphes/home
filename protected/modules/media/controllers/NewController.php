<?php

class NewController extends FrontController
{

	public function beforeAction($action)
	{
		$this->menuActiveKey = 'journal_news';
		$this->menuIsActiveLink = true;
		$this->menuIsActiveLinkOnlyParent = true;


		Yii::app()->getClientScript()->registerCssFile('/css/media.css');
		Yii::app()->getClientScript()->registerCssFile('/css-new/generated/media.css');
		Yii::app()->getClientScript()->registerScriptFile('/js/CMedia.js');
		Yii::app()->getClientScript()->registerScriptFile('/js-new/media.js');


		return parent::beforeAction($action);
	}

	public function actionIndex()
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
		$filter = $this->getFilter();

		$criteria = $this->getCriteria($filter);

		$count = MediaNew::model()->count($criteria);
		$pages = new CPagination($count);

		$pages->pageSize = $pageSize;
		$pages->applyLimit($criteria);


		// -- Тематики ---
		$themes = MediaTheme::model()->findAllByAttributes(
			array('status' => MediaTheme::STATUS_ACTIVE),
			array('order' => 'pos ASC')
		);


		// -- Похожие знания --
		$sameKnowledges = MediaKnowledge::model()->published()->findAll(array(
			'condition' => "mts.theme_id = :idTheme AND mts.model = :model",
			'params'    => array(
				':idTheme' => $filter['f_theme'],
				':model'   => 'MediaKnowledge'
			),
			'limit'     => '3',
			'join'      => "LEFT JOIN media_theme_select mts ON mts.model_id = id",
		));


		// Получаем провайдер для статей
		$newsProvider = new CActiveDataProvider('MediaNew', array(
			'criteria'   => $criteria,
			'pagination' => $pages
		));


		$this->render('//media/new/index', array(
			'newsProvider'   => $newsProvider,
			'pageSize'       => $pageSize,
			'themes'         => $themes,
			'filter'         => $filter,
			'sameKnowledges' => $sameKnowledges,
		));
	}

	/**
	 * Возвращает установки фильтра для Знаний
	 * @return array
	 */
	public function getFilter()
	{
		$filter = array();

		$filter['f_theme'] = Yii::app()->request->getParam('f_theme');
		$filter['f_whom'] = Yii::app()->request->getParam('f_whom');
		$filter['f_viewtype'] = Yii::app()->request->getParam('f_viewtype');


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
		$criteria->params = array(':st' => MediaNew::STATUS_PUBLIC);

		// Фильтрация по ТЕМАТИКАМ
		if (!empty($filter['f_theme'])) {
			$criteria->join = 'LEFT JOIN media_theme_select mts ON mts.model_id = id';

			$criteria->condition = $criteria->condition
				. ' AND mts.theme_id in (:themeId)'
				. ' AND mts.model = :model';

			$criteria->params[':themeId'] = (int)$filter['f_theme'];
			$criteria->params[':model'] = 'MediaNew';
		}

		// Фильтрация по "Кому это интересно"
		if (!empty($filter['f_whom'])) {

			if ($filter['f_whom'] == MediaNew::WHOM_SPEC) {

				$criteria->condition = $criteria->condition
					. ' AND (whom_interest = "' . MediaNew::WHOM_SPEC . '"'
					. ' OR whom_interest = "' . MediaNew::WHOM_SPEC_USER . '")';

			} elseif ($filter['f_whom'] == MediaNew::WHOM_USER) {

				$criteria->condition = $criteria->condition
					. ' AND (whom_interest = "' . MediaNew::WHOM_USER . '"'
					. ' OR whom_interest = "' . MediaNew::WHOM_SPEC_USER . '")';

			} else {

				$criteria->condition = $criteria->condition
					. ' AND (whom_interest = "' . MediaNew::WHOM_SPEC . '"'
					. ' OR whom_interest = "' . MediaNew::WHOM_USER . '"'
					. ' OR whom_interest = "' . MediaNew::WHOM_SPEC_USER . '")';
			}
		}

		/* Выводим только те работы, время публикации
		   которых уже наступило */
		$criteria->addCondition('public_time <= :pub_time');
		$criteria->params[':pub_time'] = time();


		$criteria->order = 'public_time DESC';

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

		/** @var $model MediaNew */
		$model = MediaNew::model()->findByPk((int)$id);

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

		//$relatedProducts = Product::model()->getRelatedProductsInMedia($model, $city->id, 5);
        $relatedProducts = array();

        
		$relatedCategories = Category::model()->getRelatedCategoryInMedia($model, 7);


		$this->render('//media/new/detailGrid', array(
			'model'             => $model,
			'themes'            => $themes,
			'relatedProducts'   => $relatedProducts,
			'relatedCategories' => $relatedCategories,
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
			'model'    => 'MediaNew',
			'model_id' => $modelId,
			'num'      => $numGallery
		));


		$html = $this->renderPartial('_ajaxGallery', array(
			'models'     => $models,
			'numGallery' => $numGallery
		), true);

		die(json_encode(array(
			'success' => true,
			'html'    => $html
		)));
	}
}