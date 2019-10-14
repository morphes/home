<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 08.04.13
 * Time: 16:29
 * To change this template use File | Settings | File Templates.
 */

class NewUrlRules extends EBaseUrlRule
{
	public function createUrl($manager, $route, $params, $ampersand)
	{
		$arrayCatalogRoute = array('catalog/category/list', 'product', 'catalog/vendor', 'catalog/stores');

        $arrayCatalo2gRoute = array('catalog2/category/list', 'product2', 'catalog2/vendor', 'catalog2/stores');

		$arrayStoreRules = array('catalog/store/moneyProducts', 'catalog/store/moneyNews');

        $arrayStore2Rules = array('catalog2/store/moneyProducts', 'catalog2/store/moneyNews');

		$arraySpecialistRoute = array('member/specialist/list');

		$arrayIdeaRoute = array('idea/catalog/index','idea/catalog/architecture','idea/catalog/interiorpublic','idea/catalog/interior');

		$arrayForumRoute = array('forum/category');

		$arrayHelpRoute = array('help/help/index','help/help/article','help/search/index');

		$arrayUsersRoute = array('users','member/review/list','member/profile/activity');

		if (in_array($route, $arrayCatalogRoute)) {
			$section = 'catalog';

        } elseif (in_array($route, $arrayCatalo2gRoute)) {
            $section = 'catalog2';

        } elseif (in_array($route, $arraySpecialistRoute)) {
			$section = 'specialist';

		} elseif (in_array($route, $arrayIdeaRoute)) {
			$section = 'idea';

		} elseif (in_array($route, $arrayForumRoute)) {
			$section = 'forum';

		} elseif (in_array($route, $arrayHelpRoute)) {
			$section = 'help';

		} elseif (in_array($route, $arrayUsersRoute)) {
			$section = 'users';

        } elseif (in_array($route, $arrayStore2Rules)) {
            $section = 'store2';

		} elseif (in_array($route, $arrayStoreRules)) {
			$section = 'store';

		} else {
			return false;
		}

		// cache
//		$key = $route.':'.serialize($params).':'.$ampersand;
//		$data = Yii::app()->cache->get($key);
//		if ($data)
//			return $data;

		switch ($section) {
			case 'catalog':
				$data = CatalogRules::createUrl($manager, $route, $params, $ampersand);
				break;
            case 'catalog2':
                $data = Catalog2Rules::createUrl($manager, $route, $params, $ampersand);
                break;

			case 'store':
				$data = StoreRules::createUrl($manager, $route, $params, $ampersand);
				break;

            case 'store2':
                $data = Store2Rules::createUrl($manager, $route, $params, $ampersand);
                break;

			case 'specialist':
				$data = SpecialistRules::createUrl($manager, $route, $params, $ampersand);
				break;

			case 'idea':
				$data = IdeaRules::createUrl($manager, $route, $params, $ampersand);
				break;

			case 'forum':
				$data = ForumRules::createUrl($manager, $route, $params, $ampersand);
				break;

			case 'help':
				$data = HelpRules::createUrl($manager, $route, $params, $ampersand);
				break;

			case 'users':
				$data = UsersRules::createUrl($manager, $route, $params, $ampersand);
				break;

			default:
				return false;
		}

//		Yii::app()->cache->set($key, $data, Cache::DURATION_DAY);
		return $data;
	}

	/**
	 * @param $manager
	 * @param $request CHttpRequest
	 * @param $pathInfo
	 * @param $rawPathInfo
	 * @return bool|mixed|string
	 * @throws CHttpException
	 */
	public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
	{
		//Получаем поддомен
		$sub = $this->getSubdomain();
        $catalog2 = false;
		/* -------------------------------------------------------------
		 *  Установлен Поддомен
		 * -------------------------------------------------------------
		 */
		if ($sub !== null)
		{

			if ( $sub == 'bm') {
				header('Location: http://catalog.medvediza.ru/', true, 301);
				Yii::app()->end();
			}

			/* -----------------------------------------------------
			 *  Торговый центр
			 * -----------------------------------------------------
			 */
			Yii::import('application.modules.catalog.models.MallBuild');
			Yii::import('application.modules.catalog.models.MallService');

			$mall = MallBuild::model()->findByAttributes(array('key' => $sub));

			// Если найден торговый центр
			if (isset($mall)) {
				//И обратились к корню
				if (empty($pathInfo)) {
					Cache::getInstance()->mallBuild = $mall;
					return 'catalog/category/List';
				}
			}


			if (!$mall) {
				/* -----------------------------------------------------
				 *  Магазин
				 * -----------------------------------------------------
				 */
				Yii::import('application.models.Subdomain');
				Yii::import('application.modules.catalog.models.Store');

				$subDomain = Subdomain::model()->findByAttributes(array(
					'model'  => 'Store',
					'domain' => $sub
				));


				if ($subDomain) {

                    $store = Yii::app()->db->createCommand()->from('cat_store')
                        ->where('id=:id', [':id' => $subDomain->model_id])->queryRow();

                    if (!$store) {
                        $store = Yii::app()->db_catalog2->createCommand()->from('cat_store')
                            ->where('id=:id', [':id' => $subDomain->model_id])->queryRow();
                        $catalog2 = true;
                    }

					/*
					 * Редиректим на обычный урл, если тариф не имеет тариф "Минисайт"
					 */
					if ($store['tariff_id'] != Store::TARIF_MINI_SITE) {
                        if ($catalog2) {
                            Yii::app()->request->redirect(Yii::app()->homeUrl . '/catalog2/store/index/id/' . $store['id']);
                        } else {
                            Yii::app()->request->redirect(Yii::app()->homeUrl . '/catalog/store/index/id/' . $store['id']);
                        }
					}

                    $storeModel = new Store();
                    $storeModel->attributes = $store;
					Cache::getInstance()->store = $storeModel;
				} else {
					throw new CHttpException(404);
				}
			}

		}

		/* -------------------------------------------------------------
		 *  Инициализация правил обработки
		 * -------------------------------------------------------------
		 */
		if (isset($store) && !$catalog2) {


			if (preg_match('@^(about|fotos|products|feedback|news)(?:/([\d]+))?$@u', $pathInfo, $pathArray)) {
				$urlRuleClass = new StoreRules($manager, $request, $pathInfo, $rawPathInfo, $pathArray);
			}

			if (empty($pathInfo)) {
				return 'catalog/store/moneyIndex';
			}

			/* Это странное правило нужно для того, чтобы
			 * при обращении к ajax'овому методу не происходил
			 * редирект на адрес БЕЗ поддомена
			 */
			if ($pathInfo == 'catalog/store/AjaxMoneyNewsImageUpload') {
				return 'catalog/store/AjaxMoneyNewsImageUpload';
			}
			if ($pathInfo == 'catalog/store/AjaxMoneyGalleryImageUpload') {
				return 'catalog/store/AjaxMoneyGalleryImageUpload';
			}
			if ($pathInfo == 'catalog/store/AjaxMoneyHeaderUpload') {
				return 'catalog/store/AjaxMoneyHeaderUpload';
			}

		}

        if (isset($store) && $catalog2) {


            if (preg_match('@^(about|fotos|products|feedback|news)(?:/([\d]+))?$@u', $pathInfo, $pathArray)) {
                $urlRuleClass = new StoreRules($manager, $request, $pathInfo, $rawPathInfo, $pathArray);
            }

            if (empty($pathInfo)) {
                return 'catalog2/store/moneyIndex';
            }

            /* Это странное правило нужно для того, чтобы
             * при обращении к ajax'овому методу не происходил
             * редирект на адрес БЕЗ поддомена
             */
            if ($pathInfo == 'products/store/AjaxMoneyNewsImageUpload') {
                return 'catalog2/store/AjaxMoneyNewsImageUpload';
            }
            if ($pathInfo == 'products/store/AjaxMoneyGalleryImageUpload') {
                return 'catalog2/store/AjaxMoneyGalleryImageUpload';
            }
            if ($pathInfo == 'products/store/AjaxMoneyHeaderUpload') {
                return 'catalog2/store/AjaxMoneyHeaderUpload';
            }

        }

		if (!isset($urlRuleClass) && preg_match('@^(catalog|products|idea|specialist|forum|help|users)(?:/([\w, \(\)\.-]+))?(?:/([-\w\.\(\)]+))?(?:/([-\w]+))?(?:/([-\w]+))?(?:/([-\w]+))?$@u', $pathInfo, $pathArray))
		{
     		switch ($pathArray[1]) {
				case 'catalog':
					$urlRuleClass = new CatalogRules($manager, $request, $pathInfo, $rawPathInfo, $pathArray);

					if (isset($mall)) {
						Cache::getInstance()->mallBuild = $mall;
					}
					if (isset($store)) {
						$urlRuleClass->redirectToMainDomain();
					}
					break;
                case 'products':
                    $urlRuleClass = new Catalog2Rules($manager, $request, $pathInfo, $rawPathInfo, $pathArray);

                    if (isset($mall)) {
                        Cache::getInstance()->mallBuild = $mall;
                    }
                    if (isset($store)) {
                        $urlRuleClass->redirectToMainDomain();
                    }
                    break;
				case 'idea':
					$urlRuleClass=new IdeaRules($manager,$request,$pathInfo,$rawPathInfo,$pathArray);

					if (isset($mall)) {
						$urlRuleClass->redirectToMainDomain();
					}
					if (isset($store)) {
						$urlRuleClass->redirectToMainDomain();
					}
					break;
				case 'specialist':
         			$urlRuleClass = new SpecialistRules($manager, $request, $pathInfo, $rawPathInfo, $pathArray);

					if (isset($mall)) {
						$urlRuleClass->redirectToMainDomain();
					}
					if (isset($store)) {
						$urlRuleClass->redirectToMainDomain();
					}
					break;
				case 'forum':
					$urlRuleClass= new ForumRules($manager,$request,$pathInfo,$rawPathInfo,$pathArray);

					if(isset($mall)){
						$urlRuleClass->redirectToMainDomain();
					}
					if (isset($store)) {
						$urlRuleClass->redirectToMainDomain();
					}
					break;
				case 'help':
					$urlRuleClass = new HelpRules($manager,$request,$pathInfo,$rawPathInfo,$pathArray);

					if(isset($mall)){
						$urlRuleClass->redirectToMainDomain();
					}
					if (isset($store)) {
						$urlRuleClass->redirectToMainDomain();
					}
					break;
				case 'users':
					$urlRuleClass = new UsersRules($manager,$request,$pathInfo,$rawPathInfo,$pathArray);

					if(isset($mall)){
						$urlRuleClass->redirectToMainDomain();
					}
					if (isset($store)) {
						$urlRuleClass->redirectToMainDomain();
					}
					break;

				//Все что должно быть обработано но не попадает под условия, но должно быть обработано
				default:

					return false;
			}
		}

		/* -------------------------------------------------------------
		 *  Если имеем экземпляр какого-нибудь правила обработки,
		 *  запускаем обработку правил.
		 * -------------------------------------------------------------
		 */
		if (isset($urlRuleClass) && !empty($urlRuleClass)) {

			$specialRules = $urlRuleClass->getSpecialRules();

			/* Правила которые должны примениться в первую очередь
			 * (Валидация, проверка cookie, установка кешей)
			 */

			if ($specialRules !== null) {
				return $specialRules;
			}

			//Правила для применения статичных маршрутов. Сами маршруты должны быть определены в protected $staticRouteArray = array();
			if ($urlRuleClass->getStaticRoute()) {
				return $urlRuleClass->getStaticRoute();
			}

			$countPath = count($pathArray) - 1;

			//Применение правил по количеству слов в URL
			if ($countPath == 1) {
				return $urlRuleClass->getRouteOneWord();
			}

			if ($countPath == 2) {
				return $urlRuleClass->getRouteTwoWord();
			}

			if ($countPath == 3) {

				return $urlRuleClass->getRouteThreeWord();
			}

			if ($countPath == 4) {
				return $urlRuleClass->getRouteFourWord();
			}

			if ($countPath == 5) {
				return $urlRuleClass->getRouteFiveWord();
			}

			if ($countPath == 6) {
				return $urlRuleClass->getRouteSixWord();
			}
		}

		/*
		 * Если установлен мол и запросили about или поиск то кешируем
		 * объект мола. Иначе перенаправляем на основной домен.
		 * Данное правило является временной мерой,
		 * оно необходимо пока часть правила подключена в URL менеджере
		 */
		if(isset($mall) and (in_array($pathInfo, array('about', 'search', 'email' )))){
			Cache::getInstance()->mallBuild = $mall;

		} elseif (isset($mall)) {

			ParseUrlAbstract::staticRedirectToMainDomain($request);
		}


		return false;
	}
}