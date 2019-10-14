<?php

class CatalogController extends FrontController
{
	public function filters()
        {
                return array('accessControl');
        }

	
	public function accessRules()
        {
                return array(
                    array('allow', 'actions' => array('index', 'catalog', 'ideacounter', 'architecturecounter', 'Interiorpubliccounter', 'getfilter', 'interior', 'interiorpublic', 'architecture', 'tags')),
                    array('deny', 'users' => array('*')),
                );
        }
	
	public function beforeAction($action)
        {
		if (parent::beforeAction($action)) {
			$this->breadcrumbs = array(
				'Идеи',
			);

			return true;
		}
        }

	// TODO: remove after routing fix
	public function actionIndex() // FIXME: use first elem for default
	{
		$ideatype = (int)Yii::app()->getRequest()->getParam('ideatype');

		if ( ! empty(Config::$ideaTypes[$ideatype]))
		{
			$this->menuIsActiveLink = true;
			$this->menuIsActiveLinkOnlyParent = true;
			
			return call_user_func_array (array($this, Config::$ideaTypes[$ideatype].'Catalog'), array($ideatype));
		}
		else
		{
			$this->menuActiveKey = 'interior';

			// Рендерим разводную страницу
			return $this->render('//idea/catalog/index');
		}
	}

        public function actionInterior()
	{
                // Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		return $this->InteriorCatalog(Config::INTERIOR);
	}

	public function actionInteriorpublic()
	{
		return $this->InteriorpublicCatalog(Config::INTERIOR_PUBLIC);
	}

	public function actionArchitecture()
	{
		return $this->ArchitectureCatalog(Config::ARCHITECTURE);
	}


	public function ArchitectureCatalog($ideaType)
	{
		// Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		$this->menuIsActiveLink = true;

		$search = false;

		// --- Получаем все входные параметры ---
		$selected = $this->getSelectedItems($ideaType);

		// Получаем идеи
		$architectureProvider = $this->getArchitectureDataProvider($selected['pageSize']);

		$this->render('architectureCatalog', array(
			'search'               => $search['search'],
			'selected'             => $selected,
			'weight'               => Architecture::$sphinxWeight,
			'architectureProvider' => $architectureProvider,
		));
	}

	private function getArchitectureDataProvider($pageSize = 1)
	{
		$sphinxClient = Yii::app()->search;

		// Получаем параметры из фильтра
		$selected = $this->getSelectedItems(Config::ARCHITECTURE);

		// Фильтрация
		$filters = array('status'=>array(Architecture::STATUS_ACCEPTED, Architecture::STATUS_CHANGED));
		$query = '';

		// Тип объект - обязательный фильтр
		$filters['object'] = (int)$selected['object_type'];

		// Фильтр по типам строения
		if ($selected['build_type'])
			$filters['build'] = array_map('intval', explode(', ', $selected['build_type']));

		// Фильтр по стилю
		if ($selected['style'])
			$filters['style'] = array_map('intval', explode(', ', $selected['style']));

		// Фильтр по материалу несущих конструкций
		if ($selected['material'])
			$filters['material'] = (int)$selected['material'];

		// Фильтр по этажам
		if ($selected['floor'])
			$filters['floor'] = (int)$selected['floor'];

		// Фильтр дополнительных помещений
		if ($selected['room']) {
			$tmp = explode(', ', trim($selected['room'],' ,'));
			$query .= ' @room ('.implode(' | ', $tmp).') ';
		}

		if ($selected['color']) {
			$tmp = explode(', ', trim($selected['color'],' ,'));
			$query .= ' @color ('.implode(' | ', $tmp).') ';
		}

		switch ($selected['sortType']) {
			case Config::IDEA_SORT_RELEVANCE:
				$sortString = '@weight DESC, create_time DESC';
				break;
			case Config::IDEA_SORT_DATE:
				$sortString = 'create_time DESC';
				break;
			case Config::IDEA_SORT_RATING:
				$sortString = 'average_rating DESC';
				break;
			default:
				$sortString = '@weight DESC, create_time DESC';
				break;
		}

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index' 	=> 'architecture',
			'modelClass' 	=> 'Architecture',
			'matchMode' 	=> SPH_MATCH_EXTENDED,
			'sortMode' 	=> SPH_SORT_EXTENDED,
			'sortExpr' 	=> $sortString,
			'filters' 	=> $filters,
			'query' 	=> $query,
			'pagination' 	=> array('pageSize' => $pageSize),

		));

		return $dataProvider;
	}

	/**
	 * Interior handler for catalog
	 * @param integer $ideaType
	 * @return type 
	 */
	private function InteriorCatalog($ideaType)
	{
		// другие не допустимы
		$ideaType = Config::INTERIOR;

                // Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		$this->menuIsActiveLink = true;

		$objectTypeId = (int)Yii::app()->request->getParam('objecttype', 72);

		$objectType = IdeaHeap::model()->findByPk($objectTypeId);
		if ( !empty($objectType) && $objectType->option_key==='object' ) {
			$typeConst = IdeaHeap::getBuildTypeByName($objectType->option_value, Config::INTERIOR);

			if ($typeConst == Interior::BUILD_TYPE_PUBLIC)
				$this->redirect('/idea/interiorpublic');
		} else {
			throw new CHttpException(404);
		}



		
                if(Yii::app()->request->getParam('pagesize'))
                        $pagesize = Yii::app()->request->getParam('pagesize');
                else 
                        $pagesize = Yii::app()->session->get('idea_catalog_pagesize');
                
		$pagesize = empty(Config::$ideasPageSizes[(int)$pagesize]) ? 47 : (int)$pagesize;
                Yii::app()->session->add('idea_catalog_pagesize', $pagesize);


		$roomsSelected = 0;

		// Получаем тип соритровки и выбранность в фильтре значений.
		list($sortType, $search) = $this->getIdeaDataProviderSortType();
		// Получаем идеи
                $interiorProvider = $this->getIdeaDataProvider($objectType->id, false, $pagesize, $roomsSelected, $sortType);

		$this->render('interiorCatalog', array(
			'sortType' => $sortType,
			'interiorProvider' => $interiorProvider,
			'ideaType' => $ideaType,
			'objectType' => $objectType,
			'selected' => $this->getSelectedItems($ideaType),
			'pagesize' => $pagesize,
			'roomsSelected' => $roomsSelected,
			'search' => $search,
		));
	}

	/**
	 * Метод вычисляет нужный тип соритровки для идей в каталоге
	 * @return array array(
	 * 	0 - тип сортировки
	 * 	1 - флаг обозначает выставленны в фильтре значения или нет
	 * )
	 */
	private function getIdeaDataProviderSortType()
	{
		$search = false;

		$filter = Yii::app()->request->getParam('filter');
		$sortType = Yii::app()->request->getParam('sortby');
		$rooms = Yii::app()->request->getParam('room');
		$colors = Yii::app()->request->getParam('color');
		$styles = Yii::app()->request->getParam('style');
		$query = Yii::app()->request->getParam('filter-query', '');

		$tmpNames = Config::$ideaSortNames;

		if ( ! empty ($rooms) && $rooms != 'all')
			$search = true;

		if ( ! empty($styles) && $styles != 'all')
			$search = true;

		if ( ! empty($colors))
			$search = true;

		$query = preg_replace('/[\W]+/u', ' ', $query);
		$query = trim($query);
		if ( ! empty($query))
			$search = true;



		if (!$search)
			unset($tmpNames[Config::IDEA_SORT_RELEVANCE]);

		if (!isset($tmpNames[$sortType])) {
			if ($search)
				$sortType = Config::IDEA_SORT_RELEVANCE;
			else
				$sortType = Config::IDEA_SORT_DATE;
		}

		if ($filter == 1 && $search)
			$sortType = Config::IDEA_SORT_RELEVANCE;


		return array($sortType, $search);
	}

	/**
	 * Generate data provider for find in sphinx index by idea type
	 * @param integer $ideaType
	 * @param boolean $simple
	 * @param integer $pagesize
	 * @return CSphinxDataProvider 
	 */
	private function getIdeaDataProvider($objectTypeId, $simple = false, $pagesize = 1, &$roomsSelected=0, $sortType=null)
	{
		// find current page size
		
		if (is_null($sortType))
			list($sortType) = $this->getIdeaDataProviderSortType();
		
		$sphinxClient = Yii::app()->search;
		$filterQuery = '';

		$rooms = Yii::app()->request->getParam('room');
		$colors = Yii::app()->request->getParam('color');
		$styles = Yii::app()->request->getParam('style');
		$tagsList = Yii::app()->request->getParam('tags-list', '');

		if (!empty ($rooms) && $rooms != 'all') {
			$tmp = explode(', ', $rooms);
			$roomsSelected = count($tmp);
			$filterQuery .= ' @rooms ("'. implode('" | "', $tmp) . '")'; // Fix filter search
		}
		if (!empty($styles) && $styles != 'all') {
			$filterQuery .= ' @styles ("'. implode('" | "', explode(', ', $styles)) . '")';
		}
		if (!empty($colors)) {
			$filterQuery .= ' @colors ("'. implode('" | "', explode(', ', $colors)) . '")';
		}

		$tagsList = preg_replace('/[^0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя,]+/u', ' ', $tagsList);
		$tagsList = trim($tagsList);
		if (!empty($tagsList)) {
			$keywords = preg_split("/[,]+/", $tagsList);
			$tmpQuery = ' @(tags) (';
			$cnt = 0;
			foreach ($keywords as $keyword) {
				$keyword = trim($keyword);
				if (empty($keyword))
					continue;
				if ($cnt > 0)
					$tmpQuery .= ' | ';
				$tmpQuery .= '"'.$keyword.'"';
				$cnt++;
			}
			$tmpQuery .= ')';
			if ($cnt > 0)
				$filterQuery .= $tmpQuery;
		}


		if ($simple) {
			$params = array(
				'index' => 'interior_content',
				'modelClass'	=> 'InteriorContent',
				'matchMode' => SPH_MATCH_EXTENDED,
				'filters'	=> array('status' => array(Interior::STATUS_ACCEPTED, Interior::STATUS_CHANGED), 'object_id' => $objectTypeId),
				'query'		=> $filterQuery,
				'group' => array('field'=>'interior_id','mode'=>SPH_GROUPBY_ATTR, 'order'=>'@group ASC'),
			);
		} else {
			switch ($sortType) {
				case Config::IDEA_SORT_RELEVANCE:
					$sortString = '@weight DESC, create_time DESC';
					break;
				case Config::IDEA_SORT_DATE:
					$sortString = 'create_time DESC create_time DESC';
					break;
				case Config::IDEA_SORT_RATING:
					$sortString = 'average_rating DESC create_time DESC';
					break;
				default:
					$sortString = '@weight DESC, create_time DESC';
					break;
			}
			$sortString .= ' @group ASC';
			$params = array(
				'index' => 'interior_content',
				'modelClass'	=> 'InteriorContent',
				'matchMode' => SPH_MATCH_EXTENDED,
				'filters'	=> array('status' => array(Interior::STATUS_ACCEPTED, Interior::STATUS_CHANGED), 'object_id' => $objectTypeId),
				'weight'	=> InteriorContent::$sphinxWeight,
				'query'		=> $filterQuery,
				'group' => array('field'=>'interior_id','mode'=>SPH_GROUPBY_ATTR, 'order'=>$sortString),
				'pagination' => array('pageSize' => $pagesize),
				'useGroupAsPk' => false,
			);
		}
			
		$ideaProvider = new CSphinxDataProvider($sphinxClient, $params);
		return $ideaProvider;
	}

	public function InteriorpublicCatalog($ideaType)
	{
		Yii::app()->getClientScript()->registerScriptFile('/js/interiorpublicFilter.js');

		// Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		$this->menuIsActiveLink = true;


		$objectTypeId = (int)Yii::app()->request->getParam('object_type', 84);

		$objectType = IdeaHeap::model()->findByPk($objectTypeId);
		if ( !empty($objectType) && $objectType->option_key==='object' ) {
			$typeConst = IdeaHeap::getBuildTypeByName($objectType->option_value, Config::INTERIOR);

			if ($typeConst == Interior::BUILD_TYPE_LIVE)
				$this->redirect('/idea/interior');
		} else {
			throw new CHttpException(404);
		}


		// --- Получаем все входные параметры ---
		$selected = $this->getSelectedItems($ideaType);



		// Получаем идеи
		$interiorpublicProvider = $this->getInteriorpublicDataProvider($selected['pageSize']);

		$this->render('interiorpublicCatalog', array(
			'search'	=> $selected['search'],
			'selected' 	=> $selected,
			'weight' 	=> Interiorpublic::$sphinxWeight,
			'interiorpublicProvider' => $interiorpublicProvider,
		));
	}

	private function getInteriorpublicDataProvider($pageSize = 1)
	{
		$sphinxClient = Yii::app()->search;

		// Получаем параметры из фильтра
		$selected = $this->getSelectedItems(Config::INTERIOR_PUBLIC);

		// Фильтрация
		$filters = array('status'=>array(Interiorpublic::STATUS_ACCEPTED, Interiorpublic::STATUS_CHANGED));
		$query = '';

		// Тип объект - обязательный фильтр
		$filters['object'] = (int)$selected['object_type'];

		// Фильтр по типам строения
		if ($selected['build_type'])
			$filters['build'] = array_map('intval', explode(', ', $selected['build_type']));

		// Фильтр по стилю
		if ($selected['style'])
			$filters['style'] = array_map('intval', explode(', ', $selected['style']));


		if ($selected['color']) {
			$tmp = explode(', ', trim($selected['color'],' ,'));
			$query .= ' @color ('.implode(' | ', $tmp).') ';
		}

		switch ($selected['sortType']) {
			case Config::IDEA_SORT_RELEVANCE:
				$sortString = '@weight DESC, create_time DESC';
				break;
			case Config::IDEA_SORT_DATE:
				$sortString = 'create_time DESC';
				break;
			case Config::IDEA_SORT_RATING:
				$sortString = 'average_rating DESC';
				break;
			default:
				$sortString = '@weight DESC, create_time DESC';
				break;
		}

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index' 	=> 'interiorpublic',
			'modelClass' 	=> 'Interiorpublic',
			'matchMode' 	=> SPH_MATCH_EXTENDED,
			'sortMode' 	=> SPH_SORT_EXTENDED,
			'sortExpr' 	=> $sortString,
			'filters' 	=> $filters,
			'query' 	=> $query,
			'pagination' 	=> array('pageSize' => $pageSize),

		));

		return $dataProvider;
	}
	
	/**
	 * Get array with selected items for filter
	 * @param integer $ideaType
	 * @return array
	 */
	private function getSelectedItems($ideaType)
	{
		switch ($ideaType) {
			case Config::INTERIOR : {
				$rooms = Yii::app()->request->getParam('room');
				$styles = Yii::app()->request->getParam('style');
				$tagsList = Yii::app()->request->getParam('tags-list', '');
				$selected['room'] = $rooms;
				$selected['color-data'] = Yii::app()->request->getParam('color');
				$selected['style'] = $styles;
				$selected['tags-list'] = $tagsList;
			}
			break;

			case Config::INTERIOR_PUBLIC:

				$selected['object_type'] = Yii::app()->request->getParam('object_type', 84);

				// Флаг наличия выбранных параметров поиска в фильтре
				$selected['search'] = false;


				$selected['build_type'] = Yii::app()->request->getParam('build_type');
				$selected['style'] = Yii::app()->request->getParam('style');
				$selected['color'] = Yii::app()->request->getParam('color');


				// -- Сортировка
				$sortType = Yii::app()->request->getParam('sortby');
				if ( ! isset(Config::$ideaSortNames[$sortType]))
					$sortType = Config::IDEA_SORT_DATE;

				$filter = Yii::app()->request->getParam('filter');
				if ($selected['style'] || $selected['color']) {
					$selected['search'] = true;
					if ($filter == 1)
						$sortType = Config::IDEA_SORT_RELEVANCE;
				}

				$selected['sortType'] = $sortType;



				// -- Постраничка
				$pageSize = (int)Yii::app()->request->getParam('pagesize');
				if ( ! $pageSize)
					$pageSize = (int)Yii::app()->session->get('idea_catalog_pagesize');
				if ( ! isset(Config::$ideasPageSizes[$pageSize]))
					$pageSize = key(Config::$ideasPageSizes);
				Yii::app()->session->add('idea_catalog_pagesize', $pageSize);
				$selected['pageSize'] = $pageSize;

				break;

			case Config::ARCHITECTURE:

				$selected['object_type'] = Yii::app()->request->getParam('object_type');
				if ( ! $selected['object_type']) {
					$objects = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, 0, 'object');
					$selected['object_type'] = reset($objects)->id;
				}

				// Флаг наличия выбранных параметров поиска в фильтре
				$selected['search'] = false;

				// Другие параметры получаем только в том случае, если не указан параметр
				// change_build_type - который говорит о том, что сменили тип постройки
				if ( ! Yii::app()->getRequest()->getParam('change_build_type'))
				{
					$selected['build_type'] = Yii::app()->request->getParam('build_type');
					$selected['style'] = Yii::app()->request->getParam('style');
					$selected['material'] = Yii::app()->request->getParam('material');
					$selected['floor'] = Yii::app()->request->getParam('floor');
					$selected['room'] = Yii::app()->request->getParam('room');
					$selected['color'] = Yii::app()->request->getParam('color');

				} else {
					$selected['build_type'] = '';
					$selected['style'] = '';
					$selected['material'] = '';
					$selected['floor'] = '';
					$selected['room'] = '';
					$selected['color'] = '';
				}


				// -- Сортировка
				$sortType = Yii::app()->request->getParam('sortby');
				if ( ! isset(Config::$ideaSortNames[$sortType]))
					$sortType = Config::IDEA_SORT_DATE;

				$filter = Yii::app()->request->getParam('filter');
				if ($selected['style'] || $selected['material'] || $selected['floor'] || $selected['room'] || $selected['color']) {
					$selected['search'] = true;
					if ($filter == 1)
						$sortType = Config::IDEA_SORT_RELEVANCE;
				}

				$selected['sortType'] = $sortType;


				// -- Постраничка
				$pageSize = (int)Yii::app()->request->getParam('pagesize');
				if ( ! $pageSize)
					$pageSize = (int)Yii::app()->session->get('idea_catalog_pagesize');
				if ( ! isset(Config::$ideasPageSizes[$pageSize]))
					$pageSize = key(Config::$ideasPageSizes);
				Yii::app()->session->add('idea_catalog_pagesize', $pageSize);
				$selected['pageSize'] = $pageSize;


				break;
			default:
				throw new CHttpException (500, 'Incorrect idea type.');
		}
		
		return $selected;
	}
	
	/**
	 * Get solution count for selected filter items
	 * @return JSON
	 */
	public function actionIdeacounter()
	{
		$objectTypeId = Yii::app()->request->getParam('objecttype');
		$objectTypeId = 72; // TODO: remove hardcode
		
		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(404, 'Документ не найден');
		
		$this->layout = false;

		$ideaProvider = $this->getIdeaDataProvider($objectTypeId, true);
		$count = $ideaProvider->getTotalItemCount();
		$text = 'Показать<span>'.$count.'</span>'.CFormatterEx::formatNumeral($count, array('идею', 'идеи', 'идей'), true);
		$textHint = '<span>'.$count.'</span> '.CFormatterEx::formatNumeral($count, array('вариант', 'варианта', 'вариантов'), true);
		return $this->renderText(CJSON::encode(array('count' => $count, 'text' => $text, 'textHint' => $textHint)));
	}

	public function actionArchitecturecounter()
	{
		$this->layout = false;

		$dataProvider = $this->getArchitectureDataProvider();
		$count = $dataProvider->getTotalItemCount();
		$text = 'Показать<span>'.$count.'</span>'.CFormatterEx::formatNumeral($count, array('идею', 'идеи', 'идей'), true);
		$textHint = '<span>'.$count.'</span> '.CFormatterEx::formatNumeral($count, array('вариант', 'варианта', 'вариантов'), true);
		return $this->renderText(CJSON::encode(array('count' => $count, 'text' => $text, 'textHint' => $textHint)));
	}

	public function actionInteriorpubliccounter()
	{
		$this->layout = false;

		$dataProvider = $this->getInteriorpublicDataProvider();
		$count = $dataProvider->getTotalItemCount();
		$text = 'Показать<span>'.$count.'</span>'.CFormatterEx::formatNumeral($count, array('идею', 'идеи', 'идей'), true);
		$textHint = '<span>'.$count.'</span> '.CFormatterEx::formatNumeral($count, array('вариант', 'варианта', 'вариантов'), true);
		return $this->renderText(CJSON::encode(array('count' => $count, 'text' => $text, 'textHint' => $textHint)));
	}

	/**
	 * Получение списка тэгов
	 */
	public function actionTags($term)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		if (empty($term))
			die ( CJSON::encode( array() ) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		$tagProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'tag',
			'modelClass' => 'Tag',
			'query' => $term . '*',
			'matchMode' => 'SPH_MATCH_ANY',
			'pagination' => array('pageSize' => 10),
		));
		$tags = $tagProvider->getData();

		$arr = array();

		foreach ($tags as $tag) {
			$arr[] = array(
				'label' => $tag->name ,
				'id' => $tag->id,
			);
		}
		die( CJSON::encode( $arr ) );
	}

}