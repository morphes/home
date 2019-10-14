<?php

/**
 * @author alexsh
 */
class ReportCommand extends CConsoleCommand
{
	public function init()
	{
		Yii::import('application.modules.admin.models.Report');
		Yii::import('application.modules.catalog.models.Product');
		Yii::import('application.modules.member.models.Service');
		Yii::import('application.modules.catalog.models.Store');
	}

	public function actionWorker()
	{
		/** @var $worker GearmanWorker */
		$worker = Yii::app()->gearman->worker();
		$worker->addFunction('report', array($this, 'parse'));

		while($worker->work() ){
			if (GEARMAN_SUCCESS != $worker->returnCode()) {
				echo "Worker failed: " . $worker->error();
			}
			echo "\n";
		}
	}


	public function parse(GearmanJob $job)
	{
		$workload = $job->workload();
		try {
			$reportId = unserialize($workload);
			/** @var $report Report */
			$report = Report::model()->findByPk( $reportId );
			if (is_null($report))
				return false;

			$data = unserialize($report->data);

			switch ($report->type_id) {
				case Report::TYPE_CONSOLIDATE: {
					$this->consolidateReport($report, $data);
				} break;
				case Report::TYPE_CITY: {
					$this->cityReport($report, $data);
				} break;
				case Report::TYPE_STORE: {
					$this->storeReport($report, $data);
				} break;
				case Report::TYPE_VENDOR: {
					$this->vendorReport($report, $data);
				} break;
				case Report::TYPE_CONTRACTOR: {
					$this->contractorReport($report, $data);
				} break;
				case Report::TYPE_SPECIALIST: {
					$this->specialistReport($report, $data);
				} break;
				case Report::TYPE_STORE_VIEW: {
					$this->storeViewReport($report, $data);
				} break;
				default: throw new CException('Invalid report type');
			}



		} catch(Exception $e){
			echo 'Error data: '.$workload.' '.$e->getMessage(). "\n";
			$report->status = Report::STATUS_ERROR;
			$report->save(false);
		}
	}

	private function specialistReport($report, $data)
	{
		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);

		$cityType = $data['city_type'];
		$cityList = array(); // Список ID городов

		switch ($cityType) {
			case Report::CITY_PRIORITY: {
				$sql = 'SELECT city_id as id FROM `report_city` ORDER BY pos ASC, city_id ASC';
				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_NOT_EMPTY: {
                        $sql = 'SELECT DISTINCT city.id as id FROM user_servicecity usc '
                                .'INNER JOIN city  ON  city.id=usc.city_id OR (ISNULL(usc.city_id) AND city.region_id = usc.region_id) '
                                .'ORDER BY city.id ASC';

                        $cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_HAND: {
				if ( empty($data['city']) || !is_array($data['city']) )
					throw new CException('empty city list');

				$cityList = $data['city'];
			} break;
			default: throw new CException('Invalid city type');
		}

		$serviceType = $data['service_type'];
		$serviceList = array();

		switch ($serviceType) {
			case Report::SERVICE_PRIORITY: {
				$sql = 'SELECT service_id as id FROM `report_service` ORDER BY pos ASC';
				$serviceList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::SERVICE_ALL: {
				$sql = 'SELECT id FROM `service` WHERE parent_id<>0';
				$serviceList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::SERVICE_HAND: {
				if (empty($data['service']) || !is_array($data['service']))
					throw new CException('empty service list');

				$serviceList = $data['service'];
			} break;
			default:
				throw new CException('Invalid service');
		}

		if ( empty($data['criteria']) || !is_array($data['criteria']) )
			throw new CException('empty criteria list');

		$options = $data['criteria'];

		$sphinx = Yii::app()->sphinx;

		$baseQuery = 'SELECT count(DISTINCT user_id) as cnt, city_id FROM {{user_service}} ';
		$citiesStr = implode(',', $cityList);

		$renderData = array(); // данные для генерации csv
		$columnNames = array(); // Имена столбцов

		foreach ($serviceList as $service) {
			$serviceObj = Service::model()->findByPk($service);
			// Получение списка пользователей с данной услугой
			$sql = 'SELECT DISTINCT user_id as id FROM `user_service` WHERE service_id='.$service;
			$usersArray = Yii::app()->db->createCommand($sql)->queryAll();
			$usersStr = 'AND user_id IN (';
			if (!empty($usersArray)) {
				$cnt = 0;
				foreach ($usersArray as $user) {
					if ($cnt>0){
						$usersStr .= ',';
					}
					$usersStr .= $user['id'];
					$cnt++;
				}
				$usersStr .= ')';
			} else {
				$usersStr = '';
			}

			foreach ($options as $optKey => $optVal) {
				switch ($optKey) {
					case Report::CRITERIA_MAXIMUM: {
						$sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND about=1 AND image=1 AND project_qt > 0 AND service_id='.$service.' AND `status`=2 AND city_id IN ('.$citiesStr.') ';
					} break;
					case Report::CRITERIA_OPTIMUM: {
						$sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND about=1 AND image=1 AND project_qt > 0 AND `status`=2 '.$usersStr.' AND city_id IN ('.$citiesStr.') ';
					} break;
					case Report::CRITERIA_FOTO: {
						$sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND image=1 AND `status`=2 '.$usersStr.' AND city_id IN ('.$citiesStr.') ';
					} break;
					case Report::CRITERIA_PORTFOLIO: {
						$sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND project_qt > 0 AND `status`=2 AND service_id='.$service.' AND city_id IN ('.$citiesStr.') ';
					} break;
					case Report::CRITERIA_ALL: {
						$sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND `status`=2 '.$usersStr.' AND city_id IN ('.$citiesStr.') ';
					} break;
					default: continue;
				}
				$sphinxQl .= ' GROUP BY city_id ORDER BY city_id ASC LIMIT 100000';
				$result = $sphinx->createCommand($sphinxQl)->queryAll();
				foreach ($result as $value) {
					$tmp = $service.':'.$optKey;
					$renderData[$value['city_id']][$tmp] = $value['cnt'];
				}
				// Имена столбцов
				$columnNames[$service.':'.$optKey][0] = $serviceObj->name;
				$columnNames[$service.':'.$optKey][1] = Report::$criteriaNames[$optKey];
			}
		}

		// render csv
		$cellSep = ";";
		$strSep = ';';

		$fileContent = '';
		$head1 = $cellSep;
		$head2 = '№'.$cellSep.'Регион';
		// file header
		foreach ($columnNames as $name) {
			$head1 .= $cellSep.'"'.$name[0].'"';
			$head2 .= $cellSep.'"'.$name[1].'"';
		}
		$fileContent = $head1 . $strSep . "\r\n" . $head2;
		//$fileContent
		$counter = 0;
		foreach ($cityList as $cityId) {
			if (!isset($renderData[$cityId]))
				continue;
			$values = $renderData[$cityId];

			$counter++;
			$fileContent .= $strSep."\r\n"; // line delimiter
			$city = City::model()->findByPk($cityId);

			$fileContent.= $counter.$cellSep.$city->name;
			foreach ($serviceList as $serviceId) {
				foreach ($options as $optKey => $optVal) {
					$tmp = $serviceId.':'.$optKey;
					$value = isset($values[$tmp]) ? $values[$tmp] : '0';
					$fileContent .= $cellSep.$value;
				}
			}
		}
		$fileContent = iconv('UTF-8', 'windows-1251', $fileContent);

		$fileName = 'report_'.Yii::app()->getDateFormatter()->format('d_MMMM_yyyy_HH:mm:ss', time());
		$path = UploadedFile::UPLOAD_PATH .'/reports';
		$fullPath = Yii::app()->basePath.'/../'.$path;

		$fullName = $fullPath.'/'.$fileName.'.csv';
		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}

		$handle = fopen($fullName, 'w+');
		fwrite($handle, $fileContent);
		fclose($handle);

		$report->status = Report::STATUS_SUCCESS;
		$report->file = $path.'/'.$fileName.'.csv';
		$report->save(false);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';
	}

	private function contractorReport($report, $data)
	{
		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);

		$categoryList = array(); // Список ID категорий

		if (empty($data['Category'])) {
			throw new CException('Empty catgory list');
		} else {
			$categoryList = $data['Category'];
		}

		// Фильтры по времени создания товара
		$timeCondition = '';
		if ( isset($data['start_time']) || isset($data['end_time'])) {
			// проверки на сущ обоих параметров
			$startTime = !empty($data['start_time']) ? strtotime($data['start_time']) : 0;
			$endTime = !empty($data['end_time']) ? strtotime($data['end_time'])+86400 : time();

			if ($startTime > $endTime) {
				$startTime = $endTime;
			}

			$timeCondition = ' AND p.create_time>'.$startTime.' AND p.create_time<'.$endTime.' ';
		}

		// Фильтр по магазинам
		$condition = '';
		if ( !empty($data['contractor']) ) {
			$condition .= ' AND cco.id IN ('.implode(',', $data['contractor']).') ';
		}

		$categoryStr = implode(',', $categoryList);

		// Выборка данных для отчета..
		$sql = 'select COUNT(DISTINCT p.id) as total, cco.id as contractor_id, cvc.vendor_id, p.category_id '
			.'from cat_contractor as cco '
			.'INNER JOIN cat_vendor_contractor as cvc ON cvc.contractor_id=cco.id '
			.'INNER JOIN cat_store as cs ON cs.contractor_id=cco.id '
			.'INNER JOIN cat_store_price as csp ON csp.store_id=cs.id AND csp.by_vendor=0 '
			.'INNER JOIN cat_product as p ON p.id=csp.product_id AND p.vendor_id=cvc.vendor_id '
			.'WHERE p.status=2 AND p.category_id IN ('.$categoryStr.') '.$timeCondition.$condition
			.'GROUP BY cco.id, cvc.vendor_id, p.category_id';

		$status = Product::STATUS_ACTIVE;
		$contractorData = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// выборка первых 2х колонок
		$sql = 'select COUNT(DISTINCT p.id) as total, cco.id as contractor_id, cco.name as contractor_name, cvc.vendor_id, cv.name as vendor_name '
			.'from cat_contractor as cco '
			.'INNER JOIN cat_vendor_contractor as cvc ON cvc.contractor_id=cco.id '
			.'INNER JOIN cat_store as cs ON cs.contractor_id=cco.id '
			.'INNER JOIN cat_store_price as csp ON csp.store_id=cs.id AND csp.by_vendor=0 '
			.'INNER JOIN cat_product as p ON p.id=csp.product_id AND p.vendor_id=cvc.vendor_id '
			.'INNER JOIN cat_vendor as cv ON cv.id=cvc.vendor_id '
			.'WHERE p.status=:st '.$timeCondition.$condition
			.'GROUP BY cco.id, cvc.vendor_id';


		$specContractors = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// крнвертация выборки данных
		$contractors = array();
		foreach ($contractorData as $item) {
			$contractors[$item['contractor_id']][$item['vendor_id']][$item['category_id']] = $item['total'];
		}

		unset ($contractorData);

		$forRender = array();
		$forRender[0][] = 'Контрагент';
		$forRender[0][] = 'Производитель';
		$forRender[0][] = 'Товаров всего';

		// выборка названий категорий
		$categoryNames = array();
		$sql = 'SELECT id, name FROM cat_category WHERE id IN ('.$categoryStr.')';
		$tmp = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($tmp as $category) {
			$categoryNames[$category['id']] = $category['name'];
			// запись легенды
			$forRender[0][] = $category['name'];
		}
		unset($tmp);

		$cnt=1;
		foreach ($specContractors as $contractor) {

			$forRender[$cnt][] = $contractor['contractor_name'];
			$forRender[$cnt][] = $contractor['vendor_name'];
			$forRender[$cnt][] = $contractor['total'];

			foreach ($categoryNames as $catKey=> $catName) {

				$forRender[$cnt][] = isset( $contractors[ $contractor['contractor_id'] ][ $contractor['vendor_id'] ][$catKey] ) ? $contractors[ $contractor['contractor_id'] ][ $contractor['vendor_id'] ][$catKey] : 0 ;
			}
			$cnt++;
		}


		$fileName = 'report_'.Yii::app()->getDateFormatter()->format('d_MMMM_yyyy_HH:mm:ss', time());

		$path = UploadedFile::UPLOAD_PATH .'/reports';
		$fullPath = Yii::app()->basePath.'/../'.$path;

		$fullName = $fullPath.'/'.$fileName.'.csv';
		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}
		$fp = fopen($fullName, 'w');

		$this->saveCsvRow($fp, $forRender);

		$report->status = Report::STATUS_SUCCESS;
		$report->file = $path.'/'.$fileName.'.csv';
		$report->save(false);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';
	}

	private function vendorReport($report, $data)
	{
		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);

		$categoryList = array(); // Список ID категорий

		if (empty($data['Category'])) {
			throw new CException('Empty catgory list');
		} else {
			$categoryList = $data['Category'];
		}

		// Фильтры по времени создания товара
		$timeCondition = '';
		if ( isset($data['start_time']) || isset($data['end_time'])) {
			// проверки на сущ обоих параметров
			$startTime = !empty($data['start_time']) ? strtotime($data['start_time']) : 0;
			$endTime = !empty($data['end_time']) ? strtotime($data['end_time'])+86400 : time();

			if ($startTime > $endTime) {
				$startTime = $endTime;
			}

			$timeCondition = ' AND p.create_time>'.$startTime.' AND p.create_time<'.$endTime.' ';
		}

		// Фильтр по магазинам
		$condition = '';
		if ( !empty($data['vendor']) ) {
			$condition .= ' AND p.vendor_id IN ('.implode(',', $data['vendor']).') ';
		}

		$categoryStr = implode(',', $categoryList);

		// Выборка данных для отчета
		$sql = 'SELECT COUNT( p.id) as total, p.category_id, p.vendor_id '
			.'FROM cat_product as p '
			.'WHERE p.`status`=:st AND p.category_id IN ('.$categoryStr.') '.$timeCondition.$condition
			.'group by p.vendor_id, p.category_id '
			.'order by p.category_id';

		$status = Product::STATUS_ACTIVE;
		$vendorData = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// выборка первых 2х колонок
		$sql = 'SELECT COUNT( p.id) as total, p.vendor_id, cv.name as vendor_name '
			.'FROM cat_product as p '
			.'INNER JOIN cat_vendor as cv ON cv.id=p.vendor_id '
			.'WHERE p.`status`=:st '.$timeCondition.$condition
			.'group by p.vendor_id '
			.'order by cv.name';

		$specVendors = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// крнвертация выборки данных
		$vendors = array();
		foreach ($vendorData as $item) {
			$vendors[$item['vendor_id']][$item['category_id']] = $item['total'];
		}

		unset ($vendorData);

		$forRender = array();
		$forRender[0][] = 'Производитель';
		$forRender[0][] = 'Товаров всего';

		// выборка названий категорий
		$categoryNames = array();
		$sql = 'SELECT id, name FROM cat_category WHERE id IN ('.$categoryStr.')';
		$tmp = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($tmp as $category) {
			$categoryNames[$category['id']] = $category['name'];
			// запись легенды
			$forRender[0][] = $category['name'];
		}
		unset($tmp);

		$cnt=1;
		$tmp=array();
		$summary = 0;
		foreach ($specVendors as $vendor) {

			$tmp[] = $vendor['vendor_name'];
			$tmp[] = $vendor['total'];

			foreach ($categoryNames as $catKey=> $catName) {

				$val = isset( $vendors[ $vendor['vendor_id'] ][$catKey] ) ? $vendors[ $vendor['vendor_id'] ][$catKey] : 0 ;
				$summary += $val;
				$tmp[] = $val;
			}

			if ($summary > 0) {
				$forRender[$cnt] = $tmp;
			}
			$tmp = array();
			$summary = 0;

			$cnt++;
		}


		$fileName = 'report_'.Yii::app()->getDateFormatter()->format('d_MMMM_yyyy_HH:mm:ss', time());

		$path = UploadedFile::UPLOAD_PATH .'/reports';
		$fullPath = Yii::app()->basePath.'/../'.$path;

		$fullName = $fullPath.'/'.$fileName.'.csv';
		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}
		$fp = fopen($fullName, 'w');

		$this->saveCsvRow($fp, $forRender);

		$report->status = Report::STATUS_SUCCESS;
		$report->file = $path.'/'.$fileName.'.csv';
		$report->save(false);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';
	}

	private function storeReport($report, $data)
	{
		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);

		$cityType = $data['city_type'];
		$cityList = array(); // Список ID городов
		$categoryList = array(); // Список ID категорий

		if (empty($data['Category'])) {
			throw new CException('Empty catgory list');
		} else {
			$categoryList = $data['Category'];
		}

		switch ($cityType) {
			case Report::CITY_PRIORITY: {
				$sql = 'SELECT city_id as id FROM `report_city` ORDER BY pos ASC, city_id ASC';
				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_NOT_EMPTY: {
				$sql = 'select DISTINCT sc.city_id from cat_store_city as sc '
					.'INNER JOIN cat_store as cs ON cs.id=sc.store_id AND cs.type='.Store::TYPE_OFFLINE.' '
					.'INNER JOIN cat_store_price as csp ON csp.store_id=sc.store_id AND csp.by_vendor=0';

				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_HAND: {
				if ( empty($data['city']) || !is_array($data['city']) )
					throw new CException('empty city list');

				$cityList = $data['city'];
			} break;
			default: throw new CException('Invalid city type');
		}

		// Фильтры по времени создания товара
		$condition = '';
		if ( isset($data['start_time']) || isset($data['end_time'])) {
			// проверки на сущ обоих параметров
			$startTime = !empty($data['start_time']) ? strtotime($data['start_time']) : 0;
			$endTime = !empty($data['end_time']) ? strtotime($data['end_time'])+86400 : time();

			if ($startTime > $endTime) {
				$startTime = $endTime;
			}

			$condition = ' AND p.create_time>'.$startTime.' AND p.create_time<'.$endTime.' ';
		}
		// Фильтр по магазинам
		if ( !empty($data['store']) ) {
			$condition .= ' AND cs.id IN ('.implode(',', $data['store']).')';
		}
		// Фильтр по магазинам
		if ( !empty($data['vendor']) ) {
			$condition .= ' AND p.vendor_id IN ('.implode(',', $data['vendor']).') ';
		}

		$cityStr = implode(',', $cityList);
		$categoryStr = implode(',', $categoryList);

		// выборка названий городов
		$cityNames = array();

		$sql = 'SELECT id, name FROM city WHERE id IN ('.$cityStr.')';
		$tmp = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($tmp as $city) {
			$cityNames[$city['id']] = $city['name'];
		}
		unset($tmp);

		// Выборка данных для отчета
		$sql = 'SELECT COUNT( p.id) as total, sc.city_id, p.category_id, cc.name as category_name, city.name as city_name, '
			.'cs.name as store_name, cs.address as store_address, cv.name as vendor_name '
			.'FROM cat_product as p '
			.'INNER JOIN cat_store_price as csp ON csp.product_id=p.id AND csp.by_vendor=0 '
			.'INNER JOIN cat_store as cs ON cs.id=csp.store_id AND cs.type='.Store::TYPE_OFFLINE.' '
			.'INNER JOIN cat_store_city as sc ON sc.store_id=cd.id '
			.'INNER JOIN city ON city.id=sc.city_id '
			.'INNER JOIN cat_category as cc ON cc.id=p.category_id '
			.'INNER JOIN cat_vendor as cv ON cv.id=p.vendor_id '
			.'WHERE p.`status`=:st AND p.category_id IN ('.$categoryStr.') AND sc.city_id IN ('.$cityStr.') '.$condition
			.'group by sc.city_id, cs.id, p.category_id, p.vendor_id '
			.'order by sc.city_id, p.category_id, cs.name';

		$status = Product::STATUS_ACTIVE;
		$productData = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		$tmpData = array();
		foreach ($productData as $item) {
			$tmpData[$item['city_id']][] = $item;
		}
		unset($productData);


		$specColumns = array(
			//'city' => 'Город',
			'store_name' => 'Магазин',
			'store_address' => 'Адрес',
			'category_name' => 'Вид товара',
			'vendor_name' => 'Производитель',
			'total' => 'кол-во товаров',
		);

		$forRender = array();
		$forRender[0][] = 'Город';

		// append columns name
		foreach($specColumns as $name) {
			$forRender[0][] = $name;
		}

		$cnt=1;
		foreach($cityList as $cityKey) {
			$cityName = $cityNames[$cityKey];
			if ( isset($tmpData[$cityKey]) ) {
				foreach ($tmpData[$cityKey] as $item) {
					$forRender[$cnt][] = $cityName;
					foreach ($specColumns as $key=>$name) {
						$forRender[$cnt][] = $item[$key];
					}
					$cnt++;
				}
			}
		}

		$fileName = 'report_'.Yii::app()->getDateFormatter()->format('d_MMMM_yyyy_HH:mm:ss', time());

		$path = UploadedFile::UPLOAD_PATH .'/reports';
		$fullPath = Yii::app()->basePath.'/../'.$path;

		$fullName = $fullPath.'/'.$fileName.'.csv';
		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}
		$fp = fopen($fullName, 'w');

		$this->saveCsvRow($fp, $forRender);

		$report->status = Report::STATUS_SUCCESS;
		$report->file = $path.'/'.$fileName.'.csv';
		$report->save(false);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';
	}

	/**
	 * Отчет по городам
	 * @param $report Report
	 * @param $data array
	 */
	private function cityReport($report, $data)
	{
		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);

		$cityType = $data['city_type'];
		$cityList = array(); // Список ID городов
		$categoryList = array(); // Список ID категорий

		if (empty($data['Category'])) {
			throw new CException('Empty catgory list');
		} else {
			$categoryList = $data['Category'];
		}

		switch ($cityType) {
			case Report::CITY_PRIORITY: {
				$sql = 'SELECT city_id as id FROM `report_city` ORDER BY pos ASC, city_id ASC';
				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_NOT_EMPTY: {
				$sql = 'select DISTINCT sc.city_id from cat_store_city as sc '
					.'INNER JOIN cat_store as cs ON cs.id=sc.store_id AND cs.type='.Store::TYPE_OFFLINE.' '
					.'INNER JOIN cat_store_price as csp ON csp.store_id=sc.store_id AND csp.by_vendor=0';

				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_HAND: {
				if ( empty($data['city']) || !is_array($data['city']) )
					throw new CException('empty city list');

				$cityList = $data['city'];
			} break;
			default: throw new CException('Invalid city type');
		}

		// Фильтры по времени создания товара
		$condition = '';
		if ( isset($data['start_time']) || isset($data['end_time'])) {
			// проверки на сущ обоих параметров
			$startTime = !empty($data['start_time']) ? strtotime($data['start_time']) : 0;
			$endTime = !empty($data['end_time']) ? strtotime($data['end_time'])+86400 : time();

			if ($startTime > $endTime) {
				$startTime = $endTime;
			}

			$condition = ' AND p.create_time>'.$startTime.' AND p.create_time<'.$endTime.' ';
		}

		$cityStr = implode(',', $cityList);
		$categoryStr = implode(',', $categoryList);

		// Выборка обобщенных данных по категориям
		$sql = 'SELECT COUNT(DISTINCT p.id) as total, count(distinct csp.product_id) as price, p.category_id, cc.name as category_name '
			.'FROM cat_product as p '
			.'INNER JOIN cat_category as cc ON cc.id=p.category_id '
			.'LEFT JOIN cat_store_price as csp ON csp.product_id=p.id AND csp.by_vendor=0 '
			.'WHERE p.`status`=:st AND p.category_id IN ('.$categoryStr.') '.$condition
			.'group by  p.category_id '
			.'order by p.category_id';


		$status = Product::STATUS_ACTIVE;
		$specData = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// выборка данных по городам и категориям
		$sql = 'SELECT COUNT(DISTINCT p.id) as price, sc.city_id, p.category_id '
			.'FROM cat_product as p '
			.'INNER JOIN cat_store_price as csp ON csp.product_id=p.id AND csp.by_vendor=0 '
			.'INNER JOIN cat_store_city as sc ON sc.store_id=csp.store_id '
			.'INNER JOIN cat_store as cs ON cs.id=sc.store_id AND cs.type='.Store::TYPE_OFFLINE.' '
			.'WHERE p.`status`=:st AND p.category_id IN ('.$categoryStr.') AND sc.city_id IN ('.$cityStr.') '.$condition
			.'group by sc.city_id, p.category_id '
			.'order by p.category_id, sc.city_id';

		$data = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// выборка названий городов
		$cityNames = array();
		$sql = 'SELECT id, name FROM city WHERE id IN ('.$cityStr.')';
		$tmp = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($tmp as $city) {
			$cityNames[$city['id']] = $city['name'];
		}


		$resultData = array();
		$categoryNames = array();

		$specResultData = array(); // данные в 1х колонках(без городов)

		foreach ($specData as $item) {
			$specResultData[$item['category_id']] = $item;
			$categoryNames[$item['category_id']] = $item['category_name'];
		}


		foreach ($data as $item) {
			$resultData[$item['category_id']][$item['city_id']] = $item['price'];
		}
		unset($data);

		$specColumns = array(
			'total' => 'Товаров',
			'price' => 'С ценой',
		);

		$forRender = array();
		$forRender[0][] = 'Вид товара';

		// append columns name
		foreach($specColumns as $name) {
			$forRender[0][] = $name;
		}

		foreach ($cityList as $cityId) {
			$forRender[0][] = $cityNames[$cityId];
		}

		// insert content
		foreach ($categoryNames as $catKey => $catName) {
			// category name (in line)
			$forRender[$catKey][] = $catName;
			// spec columns
			foreach ($specColumns as $columnKey=>$column) {
				if (isset($specResultData[$catKey]))
					$forRender[$catKey][] = $specResultData[$catKey][$columnKey];
				else
					$forRender[$catKey][] = 0;
			}

			// cities statistic
			foreach ($cityList as $cityId) {
				if ( isset( $resultData[$catKey][$cityId] )  ) {
					$forRender[$catKey][] = $resultData[$catKey][$cityId];
				} else {
					$forRender[$catKey][] = 0;
				}
			}
		}

		$fileName = 'report_'.Yii::app()->getDateFormatter()->format('d_MMMM_yyyy_HH:mm:ss', time());

		$path = UploadedFile::UPLOAD_PATH .'/reports';
		$fullPath = Yii::app()->basePath.'/../'.$path;

		$fullName = $fullPath.'/'.$fileName.'.csv';
		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}
		$fp = fopen($fullName, 'w');

		$this->saveCsvRow($fp, $forRender);

		$report->status = Report::STATUS_SUCCESS;
		$report->file = $path.'/'.$fileName.'.csv';
		$report->save(false);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';

	}

	/**
	 * Сводный отчет
	 * @param $report Report
	 * @param $data array
	 */
	private function consolidateReport($report, $data)
	{
		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);

		$cityType = $data['city_type'];
		$cityList = array(); // Список ID городов
		$categoryList = array(); // Список ID категорий

		if (empty($data['Category'])) {
			throw new CException('Empty catgory list');
		} else {
			$categoryList = $data['Category'];
		}

		switch ($cityType) {
			case Report::CITY_PRIORITY: {
				$sql = 'SELECT city_id as id FROM `report_city` ORDER BY pos ASC, city_id ASC';
				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_NOT_EMPTY: {
				$sql = 'select DISTINCT sc.city_id from cat_store_city as sc '
					.'INNER JOIN cat_store as cs ON cs.id=sc.store_id AND cs.type='.Store::TYPE_OFFLINE.' '
					.'INNER JOIN cat_store_price as csp ON csp.store_id=sc.store_id AND csp.by_vendor=0';

				$cityList = Yii::app()->db->createCommand($sql)->queryColumn();
			} break;
			case Report::CITY_HAND: {
				if ( empty($data['city']) || !is_array($data['city']) )
					throw new CException('empty city list');

				$cityList = $data['city'];
			} break;
			default: throw new CException('Invalid city type');
		}

		// Фильтры по времени создания товара
		$condition = '';
		if ( isset($data['start_time']) || isset($data['end_time'])) {
			// проверки на сущ обоих параметров
			$startTime = !empty($data['start_time']) ? strtotime($data['start_time']) : 0;
			$endTime = !empty($data['end_time']) ? strtotime($data['end_time'])+86400 : time();

			if ($startTime > $endTime)
				$startTime = $endTime;

			$condition = ' AND p.create_time>'.$startTime.' AND p.create_time<'.$endTime.' ';
		}
		
		$cityStr = implode(',', $cityList);
		$categoryStr = implode(',', $categoryList);

		// Выборка обобщенных данных по категориям
		$sql = 'SELECT count(DISTINCT p.id) as total, count(DISTINCT csp.product_id) as price, '
			.'count(DISTINCT p.vendor_id) as vendor, count(DISTINCT cvc.contractor_id) as contractor, '
			.'count(DISTINCT csp.store_id) as store, p.category_id, cc.name as category_name '
			.'FROM cat_product as p '
			.'LEFT JOIN cat_store_price as csp ON csp.product_id=p.id AND csp.by_vendor=0 '
			.'LEFT JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=p.vendor_id '
			.'INNER JOIN cat_category as cc ON cc.id=p.category_id '
			.'WHERE p.`status`=:st AND p.category_id IN ('.$categoryStr.') '.$condition
			.'group by p.category_id';

		$status = Product::STATUS_ACTIVE;
		$specData = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// выборка данных по городам и категориям
		$sql = 'SELECT COUNT(DISTINCT p.id) as price, COUNT(DISTINCT p.vendor_id) as vendor, '
			.'COUNT(DISTINCT csp.store_id) as store, COUNT(DISTINCT cs.contractor_id) as contractor, sc.city_id, '
			.'p.category_id, cc.name as category_name '
			.'FROM cat_product as p '
			.'INNER JOIN cat_store_price as csp ON csp.product_id=p.id AND csp.by_vendor=0 '
			.'INNER JOIN cat_store_city as sc ON sc.store_id=csp.store_id '
			.'INNER JOIN cat_store as cs ON cs.id=sc.store_id AND cs.type='.Store::TYPE_OFFLINE.' '
			.'INNER JOIN cat_category as cc ON cc.id=p.category_id '
			.'WHERE p.`status`=:st AND p.category_id IN ('.$categoryStr.') AND sc.city_id IN ('.$cityStr.') '.$condition
			.'group by sc.city_id, p.category_id '
			.'order by p.category_id';

		$data = Yii::app()->db->createCommand($sql)->bindParam(':st', $status)->queryAll();

		// выборка названий городов
		$cityNames = array();
		$sql = 'SELECT id, name FROM city WHERE id IN ('.$cityStr.')';
		$tmp = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($tmp as $city) {
			$cityNames[$city['id']] = $city['name'];
		}


		$resultData = array();
		$categoryNames = array();

		$specResultData = array(); // данные в 1х колонках(без городов)

		foreach ($specData as $item) {
			$specResultData[$item['category_id']] = $item;
			$categoryNames[$item['category_id']] = $item['category_name'];
		}


		foreach ($data as $item) {
			$resultData[$item['category_id']][$item['city_id']] = $item;
		}
		unset($data);

		$cityColumns = array(
			'price' => 'Товаров',
			'vendor' => 'Производителей',
			'contractor' => 'Контрагентов',
			'store' => 'Магазинов',
		);

		$specColumns = array(
			'total' => 'Товаров',
			'price' => 'С ценой',
			'vendor' => 'Производителй',
			'contractor' => 'Контрагентов',
			'store' => 'Магазинов',
		);



		$forRender = array();
		$forRender[0][] = 'Вид товара';

		// append columns name
		foreach($specColumns as $name) {
			$forRender[0][] = $name;
		}

		foreach ($cityList as $cityId) {
			foreach($cityColumns as $column) {
				$forRender[0][] = $cityNames[$cityId].':'.$column;
			}
		}

		// insert content
		foreach ($categoryNames as $catKey => $catName) {
			// category name (in line)
			$forRender[$catKey][] = $catName;
			// spec columns
			foreach ($specColumns as $columnKey=>$column) {
				if (isset($specResultData[$catKey]))
					$forRender[$catKey][] = $specResultData[$catKey][$columnKey];
				else
					$forRender[$catKey][] = 0;
			}

			// cities statistic
			foreach ($cityList as $cityId) {
				foreach($cityColumns as $columnKey=>$column) {
					if ( isset( $resultData[$catKey][$cityId] )  ) {
						$forRender[$catKey][] = $resultData[$catKey][$cityId][$columnKey];
					} else {
						$forRender[$catKey][] = 0;
					}
				}
			}
		}

		$fileName = 'report_'.Yii::app()->getDateFormatter()->format('d_MMMM_yyyy_HH:mm:ss', time());

		$path = UploadedFile::UPLOAD_PATH .'/reports';
		$fullPath = Yii::app()->basePath.'/../'.$path;

		$fullName = $fullPath.'/'.$fileName.'.csv';
		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}
		$fp = fopen($fullName, 'w');

		$this->saveCsvRow($fp, $forRender);

		$report->status = Report::STATUS_SUCCESS;
		$report->file = $path.'/'.$fileName.'.csv';
		$report->save(false);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';

	}

	/**
	 * Сохраняет набор данных в файл.
	 * @param $fp указатель на файл, в который будем сохранять
	 * @param $result Массив массивов данных для сохранения
	 */
	private function saveCsvRow($fp, $result)
	{
		foreach ($result as $d) {
			// Конвертим в кодировку cp1251
			$d_cp1251 = array_map(function($n){ return iconv('UTF-8', 'cp1251//TRANSLIT', $n); }, $d);
			fwrite($fp, implode(';', $d_cp1251)."\r\n");
		}
	}


	/**
	 * Генерация отчетов по просмотру магаизнов.
	 *
	 * @param $report
	 * @param $data
	 *
	 * @throws CException
	 */
	private function storeViewReport($report, $data)
	{
		Yii::import('application.modules.catalog.models.StatStore');
		Yii::import('application.modules.catalog.models.Store');
		Yii::import('ext.PHPExcel.PHPExcel');

		$report->status = Report::STATUS_PROGRESS;
		$report->save(false);


		$month = (int)$data['month'];
		$year = (int)$data['year'];

		// Создаем объект Excel документа
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("MyHome")
			->setLastModifiedBy("MyHome")
			->setTitle("Статистика посещяемости " . Yii::app()->locale->getMonthName($month) . ' ' . $year)
			->setSubject("Статистика посещаемости")
			->setDescription("Список магазинов с данными о посещаемости их страниц, товаров, переходов на сайт и т.п.")
			->setKeywords("myhome");

		$objPHPExcel->setActiveSheetIndex(0);
		$sheet = $objPHPExcel->getActiveSheet();
		// Объединяем ячейки под заголовок
		$sheet->mergeCells('B1:D1');
		$sheet->mergeCells('B2:D2');

		// Выводим шапку таблицы
		$sheet
			->setCellValue('B1', 'Статистика посещаемости за ' . Yii::app()->locale->getMonthName($month))
			->setCellValue('B2', 'Дата создания отчета ' . date('Y-m-d'))
			->setCellValue('A3', '№ пп')
			->setCellValue('B3', 'Город')
			->setCellValue('C3', 'Наименование магазина')
			->setCellValue('D3', 'Адрес магазина')
			->setCellValue('E3', 'Тариф')
			->setCellValue('F3', 'Дата отключения тарифа')
			->setCellValue('G3', 'Кол-во товаров в каталоге')
			->setCellValue('H3', 'Посещений страницы магазина')
			->setCellValue('I3', 'Просмотр товаров Витрины')
			->setCellValue('J3', 'Просмотр товаров в общем каталоге')
			->setCellValue('K3', 'Переходов на сайт клиента')
			->setCellValue('L3', 'Доля переходов на сайт от  кол-ва посещений магазина')
			->setCellValue('M3', 'Средняя посещаемость магазина в день за месяц')
			->setCellValue('N3', 'Среднее число просмотров 1 товара');

		// Добавляем стили для шапки таблицы
		$styleA3_N3 = $sheet->getStyle('A3:N3');
		$styleA3_N3->getFont()->setBold(true);
		$styleA3_N3->getFill()
			->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()->setARGB('FFEEECE1');

		$sheet->getStyle('A3:N3')->getAlignment()
			->setWrapText(true)
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$sheet->getColumnDimension('A')->setWidth(6); 	// № пп
		$sheet->getColumnDimension('B')->setWidth(18); 	// Города
		$sheet->getColumnDimension('C')->setWidth(25); 	// Наименоваине магазина
		$sheet->getColumnDimension('D')->setWidth(50); 	// Адрес магазина
		$sheet->getColumnDimension('E')->setWidth(18); 	// Тариф
		$sheet->getColumnDimension('F')->setWidth(12);  // Дата отключения тарифа
		$sheet->getColumnDimension('G')->setWidth(12);	// Кол-во товаров в каталоге
		$sheet->getColumnDimension('H')->setWidth(12); 	// Посещений страницы магазина
		$sheet->getColumnDimension('I')->setWidth(12); 	// Просмотр товаров Витрины
		$sheet->getColumnDimension('J')->setWidth(12);	// Просмотр товаров в общем каталоге
		$sheet->getColumnDimension('K')->setWidth(12);	// Кол-во переходов на сайт клиента
		$sheet->getColumnDimension('L')->setWidth(18);	// Доля переходов на сайт от  кол-ва посещений магазина
		$sheet->getColumnDimension('M')->setWidth(12);	// Средняя посещаемость магазина в день
		$sheet->getColumnDimension('N')->setWidth(12);	// Среднее число просмотров 1 товара
		$sheet->getRowDimension(3)->setRowHeight(65);


		/* -------------------------------------------------------------
		 *  Заполняем табличку данными по магазину
		 * -------------------------------------------------------------
		 */

		// Получаем список магазинов со статистикой, попадающих в период
		$shops = StatStore::getStatAllStoresByPeriod(
			mktime(0, 0, 0, $month, 1, $year),
			mktime(0, 0, 0, $month + 1, 1, $year),
			15000
		);


		$rowIndex = 4; // Индекс строки ячейки в которую пишем данные
		$num = 1;
		foreach ($shops as $shopId => $item) {

			$store = Store::model()->findByPk($shopId);

			if (!$store) {
				continue;
			}

			if ($item['tariff_id'] > Store::TARIF_FREE) {
				$sheet->getStyle('C' . $rowIndex)->getFont()->setBold(true);
			}

			$tariff_id = isset(Store::$tariffs[ $item['tariff_id'] ])
				? Store::$tariffs[ $item['tariff_id'] ]
				: '—';

			$tariff_expire_date = ((int)$item['tariff_expire_date'] > 0)
				? date('d.m.Y', (int)$item['tariff_expire_date'])
				: '—';

			$hit_store = isset($item['views'][StatStore::TYPE_HIT_STORE])
				? $item['views'][StatStore::TYPE_HIT_STORE]
				: '';

			$hit_own_product = isset($item['views'][StatStore::TYPE_HIT_OWN_PRODUCT])
				? $item['views'][StatStore::TYPE_HIT_OWN_PRODUCT]
				: '';

			$hit_common_product = isset($item['views'][StatStore::TYPE_HIT_COMMON_PRODUCT])
				? $item['views'][StatStore::TYPE_HIT_COMMON_PRODUCT]
				: '';

			$view_site = isset($item['views'][StatStore::TYPE_SITE])
				? $item['views'][StatStore::TYPE_SITE]
				: '';


			$sheet
				->setCellValue('A' . $rowIndex, $num)
				->setCellValue('B' . $rowIndex, $item['city'])
				->setCellValue('C' . $rowIndex, $item['name'])
				->setCellValue('D' . $rowIndex, $item['address'])
				->setCellValue('E' . $rowIndex, $tariff_id)
				->setCellValue('F' . $rowIndex, $tariff_expire_date)
				->setCellValue('G' . $rowIndex, $store->productQt)
				->setCellValue('H' . $rowIndex, $hit_store)
				->setCellValue('I' . $rowIndex, $hit_own_product)
				->setCellValue('J' . $rowIndex, $hit_common_product)
				->setCellValue('K' . $rowIndex, $view_site)
				->setCellValue('L' . $rowIndex, "=(K{$rowIndex}/H{$rowIndex})")
				->setCellValue('M' . $rowIndex, "=(H{$rowIndex}/31)")
				->setCellValue('N' . $rowIndex, "=(J{$rowIndex}/G{$rowIndex})")
			;
			/*$sheet->getStyle('L' . $rowIndex)
				->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
			$sheet->getStyle('M' . $rowIndex)
				->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			$sheet->getStyle('N' . $rowIndex)
				->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);*/


			$rowIndex++;
			$num++;
		}

		/* -------------------------------------------------------------
		 *  Получаем путь до файла и сохраняем его на диск
		 * -------------------------------------------------------------
		 */
		$fileName = 'report_' . Yii::app()
				->getDateFormatter()
				->format('d_MMMM_yyyy_HH:mm:ss', time());
		$path = UploadedFile::UPLOAD_PATH .'/reports';

		$fullPath = Yii::app()->basePath.'/../'.$path;
		$fullName = $fullPath.'/'.$fileName.'.xlsx';

		if (!file_exists($fullPath)) {
			mkdir($fullPath, 0700, true);
		}

		// Сохраняем Excel файл
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($fullName);

		// Обновляем данные в отчете
		$report->file = $path.'/'.$fileName.'.xlsx';
		$report->status = Report::STATUS_SUCCESS;
		$report->save(false);


		$sheet->disconnectCells();
		unset($sheet);
		$objPHPExcel->disconnectWorksheets();
		unset($objPHPExcel);

		echo date('[Y-m-d H:i:s] ').__METHOD__.'Generate success';
	}
}