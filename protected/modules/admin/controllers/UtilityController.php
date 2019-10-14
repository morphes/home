<?php

/**
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class UtilityController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
			array('allow',
			'actions' => array('acStore', 'acContractor', 'acBank', 'AcCatCategory', 'AcCatCategory2'),
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_STORE
				),
			),
			array('allow',
				'actions' => array('acVendor'),
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_STORES_MODERATOR,
					User::ROLE_STORES_ADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_STORE,
				),
			),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }

	/**
	 * Автокомплит по магазинам
	 *
	 * @param $term string Поисковая строка
	 * @param $largeReturn boolean Флаг при выставлении, которого возвращает
	 *                             большая выборка при нахождении
	 *
	 * @throws CHttpException
	 */
	public function actionAcStore($term, $largeReturn = false, $city_id = 0)
	{
		$largeReturn = (bool)$largeReturn;

		if (empty($term))
			die( json_encode(array()) );

		// Инициализируем пустой фильтр для Sphinx
		$filters = array();

		/* Проверяем передан ли город. Если да, то фильтруем магазины
		по городному признаку */
		$city = City::model()->findByPk((int)$city_id);
		if ($city) {
			$filters['city_id'] = $city->id;
		}


		$sphinxClient = Yii::app()->search;
		$terms = explode(' ', $term);
		$query = '';
		foreach ($terms as $item) {
			$query .= $sphinxClient->EscapeString($item).'* ';
		}

		//$term = $sphinxClient->EscapeString($term);

		Yii::import('catalog.models.Store');

		$storeProvider = new CSphinxDataProvider($sphinxClient, array(
			'index'      => 'store',
			'modelClass' => 'Store',
			'query'      => $query,
			'sortMode'   => SPH_SORT_RELEVANCE,
			'matchMode'  => SPH_MATCH_ANY,
			'weight'     => array('name' => 100, 'str_id' => 150, 'address' => 20),
			'pagination' => array('pageSize' => ($largeReturn === true) ? 30 : 10),
			'filters'    => $filters
		));
		$stores = $storeProvider->getData();
		$arr = array();

		foreach ($stores as $store) {
			$value = $store->id.', '.$store->name.' ('.$store->address.')';
			$arr[] = array(
				'label' => $value,
				'id' => $store->id,
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

	/**
	 * Автокомплит по производителям
	 * @param $term
	 * @throws CHttpException
	 */
	public function actionAcVendor($term)
	{
		if(!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);
		$this->layout = false;

		$term_trans = Amputate::rus2translit($term);
		$command = Yii::app()->db;

		$data = $command->createCommand(
			"SELECT t.id, t.name, t.country_id, t.city_id FROM `cat_vendor` t"
				. " WHERE t.name LIKE '%" . CHtml::encode($term) . "%'"
				. " OR t.name LIKE '%" . CHtml::encode($term_trans) . "%'"
				. " LIMIT 30"
		)->queryAll();
		$results = array();
		foreach($data as $record) {

			$results[] = array(
				'label' => $record['name'] . ' (' . Country::getNameById($record['country_id'])
					. ', ' . City::getNameById($record['city_id']) . ')',
				'value' => $record['name'],
				'id'    => $record['id'],
			);
		}
		die( json_encode($results, JSON_NUMERIC_CHECK) );
	}

	/**
	 * Автокомплит на контрагентов
	 * @param $term
	 * @throws CHttpException
	 */
	public function actionAcContractor($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		Yii::import('catalog.models.Contractor');

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index' => 'contractor',
			'modelClass' => 'Contractor',
			'query' => $term . '*',
			'sortMode' => SPH_SORT_EXTENDED,
			'sortExpr' => 'id asc',
			'filters' => array( 'status'=>array('exclude'=>true, 'val'=>Contractor::STATUS_DELETED) ),
			'matchMode' => SPH_MATCH_ANY,
			'pagination' => array('pageSize' => 10),
		));
		$contractors = $dataProvider->getData();
		$arr = array();

		foreach ($contractors as $contractor) {
			$arr[] = array(
				'label' => $contractor->name,
				'id' => $contractor->id,
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

	/**
	 * Автокомплит по банкам
	 * @param $term
	 * @throws CHttpException
	 */
	public function actionAcBank($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		Yii::import('catalog.models.Bank');

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index' => 'bank',
			'modelClass' => 'Bank',
			'query' => $term . '*',
			'sortMode' => SPH_SORT_EXTENDED,
			'sortExpr' => 'id asc',
			'matchMode' => SPH_MATCH_ANY,
			'pagination' => array('pageSize' => 10),
		));
		$banks = $dataProvider->getData();
		$arr = array();

		foreach ($banks as $bank) {
			$arr[] = array(
				'label' => $bank->name.' (БИК: '.$bank->bic.' Корр.счет: '.$bank->corr_account.')',
				'id' => $bank->id,
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}


	/**
	 * Автокомплит по категориям каталога товаров.
	 * Поиск реализован на MySql
	 *
	 * @throws CHttpException
	 */
	public function actionAcCatCategory($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		Yii::import('catalog.models.Category');

		$criteria = new CDbCriteria();
		$criteria->compare('name', $term, true);

		$cats = new CActiveDataProvider('Category', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 10
			)
		));
		$cats = $cats->getData();
		$arr = array();

		foreach ($cats as $cat) {
			$arr[] = array(
				'label' => $cat->name,
				'id' => $cat->id,
			);
		}


		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

    public function actionAcCatCategory2($term)
    {
        if (!Yii::app()->getRequest()->getIsAjaxRequest())
            throw new CHttpException(404);

        if (empty($term))
            die( json_encode(array()) );

        $sphinxClient = Yii::app()->search;
        $term = $sphinxClient->EscapeString($term);

        Yii::import('catalog2.models.*');

        $criteria = new CDbCriteria();
        $criteria->compare('name', $term, true);

        $cats = new CActiveDataProvider('Category', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 10
            )
        ));
        $cats = $cats->getData();
        $arr = array();

        foreach ($cats as $cat) {
            $arr[] = array(
                'label' => $cat->name,
                'id' => $cat->id,
            );
        }


        die ( json_encode($arr, JSON_NUMERIC_CHECK) );
    }
}