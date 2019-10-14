<?php
/**
 * Description of SphinxreindexCommand
 *
 * @author alexsh
 */
class SphinxreindexCommand extends CConsoleCommand
{
	/**
	 * Шаг индексации для различных индексов 
	 */
	const INDEX_STEP = 1000; // Default range
	
	const STEP_INTERIOR_CONTENT = 2000;
	const STEP_USER_LOGIN = 2000;
	const STEP_USER_MESSAGE = 1000;
    const STEP_PRODUCT = 250;
	const STEP_INTERIOR_PUBLIC = 1000;
	const STEP_ARCHITECTURE = 1000;
	const STEP_TENDER = 100;
	const STEP_STORE = 1000;
	const STEP_CONTRACTOR = 100;

	private $_prefix = '';

	public function init()
	{
		$this->_prefix = Yii::app()->sphinx->tablePrefix;
	}
	
	/**
	 * Переиндексация всех индексов 
	 */
	public function actionRun()
	{
		$this->actionUserMessage();
		$this->actionUserLogin();
		$this->actionInteriorContent();
        $this->actionProduct();
        $this->actionProduct2();
		$this->actionInteriorPublic();
		$this->actionArchitecture();
		$this->actionIdea();
		$this->actionMedia();
		$this->actionTender();
		$this->actionStore();
		$this->actionStore2();
		$this->actionContractor();
	}

	public function actionContractor()
	{
		echo "====> {$this->_prefix}contractor indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM cat_contractor';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = intval( $result['min'] );
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_CONTRACTOR) {

				$sql = 'SELECT t.id, t.name, t.status, t.create_time, t.update_time, t.worker_id '
					.'FROM cat_contractor as t '
					.'WHERE t.id>='.$i.' AND t.id<'.($i+self::STEP_CONTRACTOR).' ';

				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				/** Очистка индекса */
				$sphinxDel = 'DELETE FROM {{contractor}} WHERE id IN (';
				$cnt = 0;
				for ($j = $i; $j < $i+self::STEP_CONTRACTOR; $j++) {
					if ($cnt > 0)
						$sphinxDel .= ',';
					$sphinxDel .= $j;
					$cnt++;
				}
				$sphinxDel .= ')';
				Yii::app()->sphinx->createCommand($sphinxDel)->execute();
				// завершение очистки

				$result = 0;
				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{contractor}} (`id`, `name`, `worker_id`, `status`, `create_time`, `update_time`) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0)
							$sphinxQl .= ',';
						else
							$cnt++;

						$sphinxQl .= '('.$object['id'].',\''.addslashes($object['name']).'\','.intval($object['worker_id']).','.intval($object['status']).','
							.$object['create_time'].','.$object['update_time'].')';
					}
					$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
				}

				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}


	/**
	 * Общая переиндексация всех магазинов
	 */
	public function actionStore($indexName = 'store', $conn = null)
	{
		echo "====> {$this->_prefix}store indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

        if (!$conn) {
            $conn = Yii::app()->db;
        }

		try {
			/* =====================================================
			 *  Вычисляем данные по магазинам из БД
			 * =====================================================
			 */
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM cat_store';

			$result = $conn->createCommand($sql)->queryRow();
			$min = intval( $result['min'] );
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			/* =====================================================
			 *  Индексация магазинов
			 * =====================================================
			 */
			for ($i = $min; $i <= $max; $i += self::STEP_STORE) {

				/* ---------------------------------------------
				 *  Выбираем магазины из БД
				 * ---------------------------------------------
				 */
				$sql = 'SELECT tmp.*, cs.chain_id, tmp2.product_qt FROM'
					. '(SELECT'
					. ' s.id, s.name, s.address, s.type, s.status, '
					. ' s.create_time, s.update_time,'
					. ' s.user_id, s.tariff_id, geocode, '
					. ' GROUP_CONCAT(DISTINCT p.category_id SEPARATOR ",") as category_ids'
					. ' FROM cat_store as s'
					. ' INNER JOIN cat_store_price sp ON sp.store_id = s.id'
					. ' INNER JOIN cat_product p ON p.id = sp.product_id'
					. ' WHERE '
					. ' 	s.id >= ' . $i . ' AND s.id < ' . ($i + self::STEP_STORE).' AND s.status=1'
					. ' GROUP BY s.id) as tmp'
					. ' LEFT JOIN cat_chain_store cs'
					. ' 	ON cs.store_id = tmp.id '
					. ' LEFT JOIN ('
					. ' SELECT store_id, COUNT(product_id) as product_qt'
					. ' FROM cat_store_price as csp'
					. ' INNER JOIN cat_product as p ON p.id=csp.product_id AND p.status=2'
					. ' WHERE by_vendor=0 AND store_id >= '.$i.' AND store_id < '. ($i + self::STEP_STORE)
					. ' GROUP BY store_id) as tmp2 ON tmp2.store_id = tmp.id';


				$objects = $conn->createCommand($sql)->queryAll();


				/* ---------------------------------------------
				 *  Очистка индекса
				 * ---------------------------------------------
				 */
				$sphinxDel = 'DELETE FROM {{' . $indexName . '}} WHERE id IN (';
				$cnt = 0;
				for ($j = $i; $j < $i+self::STEP_STORE; $j++) {
					if ($cnt > 0) {
						$sphinxDel .= ',';
					}
					$sphinxDel .= $j;
					$cnt++;
				}
				$sphinxDel .= ')';
				Yii::app()->sphinx->createCommand($sphinxDel)->execute();



				/* ---------------------------------------------
				 *  Собираем строку запроса и выполняем ее
				 *  в сфинкс
				 * ---------------------------------------------
				 */
				$result = 0;
				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{' . $indexName . '}} '
						. ' (`id`, `status`'
						. ', `category_ids`, `first_letter`'
						. ', `str_id`, `name`, `address`'
						. ', `type`, `user_id`, `chain_id`, `is_chain`'
						. ', `tariff_id`'
						. ', `product_qt`'
						. ', `longitude`, `latitude`'
						. ', `create_time`, `update_time`)'
						. ' VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0) {
							$sphinxQl .= ',';
						} else {
							$cnt++;
						}

						// Получаем код первого символа
						$intFirstLetter = crc32(
							mb_strtoupper(
								mb_substr($object['name'], 0, 1, 'UTF-8'),
								'UTF-8'
							)
						);

						/* -----------------------------------------------------------
						 * Вводим два дополнительных параметра
						 * chain_id - хранит ID сети магазина, к которой относится магазин
						 * is_chain - флаг принадлежности магазина к сети магазинов
						 *
						 * Если магазин не принадлежит сети, то вместо
						 * id сети пишем его идентификатор в отрицаельном
						 * значении (сфинкс его по кругу превратит в большое
						 * положительное) и ставим флаг is_chain в ноль.
						 *
						 * chain_id нужен для группировки при полнотекстовом поиске.
						 * -----------------------------------------------------------
						 */
						$chainId = intval($object['chain_id']);
						if ($chainId > 0) {
							$isChain = 1;
						} else {
							$isChain = 0;
							$chainId = -1 * $object['id'];
						}

						// Получаем геокоординаты магазина
						$geo = unserialize($object['geocode']);
						$longitude = (isset($geo[0])) ? $geo[0] : 0.0;
						$latitude = (isset($geo[1])) ? $geo[1]: 0.0;

						$sphinxQl .= '('
							. $object['id']
							. ',' . $object['status']
							. ',(' . $object['category_ids'] . ')'
							. ',' . $intFirstLetter
							. ',\'' . $object['id'] . '\''
							. ',\'' . addslashes($object['name']) . '\''
							. ',\'' . addslashes($object['address']) . '\''
							. ',' . intval($object['type'])
							. ',' . intval($object['user_id'])
							. ',' . $chainId
							. ',' . $isChain
							. ',' . intval($object['tariff_id'])
							. ',' . intval($object['product_qt'])
							. ',' . $longitude
							. ',' . $latitude
							. ',' . $object['create_time']
							. ',' . $object['update_time']
							. ')';
					}
					$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
				}

				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getMessage();
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

    /**
     * Общая переиндексация всех магазинов
     */
    public function actionStore2()
    {
        $this->actionStore('store2', Yii::app()->dbcatalog2);
    }

	public function actionTender()
	{
		echo "====> {$this->_prefix}tender indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		Yii::import('application.modules.tenders.models.Tender');

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM tender';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = intval( $result['min'] );
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_TENDER) {

				$sql = 'SELECT t.id, t.author_id, t.name, t.desc, t.status, t.cost, t.city_id, '
					.'t.response_count, GROUP_CONCAT(DISTINCT ts.service_id SEPARATOR ",") as services, t.create_time, t.update_time, '
					.'GROUP_CONCAT(DISTINCT tr.author_id SEPARATOR ",") as users, GROUP_CONCAT(DISTINCT s.name SEPARATOR ", ") as services_name, c.name as city_name '
					.'FROM tender as t '
					.'LEFT JOIN tender_service as ts ON ts.tender_id=t.id '
                                        .'INNER JOIN service s ON s.id = ts.service_id '
					.'LEFT JOIN tender_response as tr ON tr.tender_id=t.id '
                                        .'LEFT JOIN city c ON c.id = t.city_id '
					.'WHERE t.id>='.$i.' AND t.id<'.($i+self::STEP_TENDER).' '
					.'GROUP BY t.id ';
				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				/** Очистка индекса */
				$sphinxDel = 'DELETE FROM {{tender}} WHERE id IN (';
				$cnt = 0;
				for ($j = $i; $j < $i+self::STEP_TENDER; $j++) {
					if ($cnt > 0)
						$sphinxDel .= ',';
					$sphinxDel .= $j;
					$cnt++;
				}
				$sphinxDel .= ')';
				Yii::app()->sphinx->createCommand($sphinxDel)->execute();
				// завершение очистки

				$result = 0;
				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{tender}} (`id`, `author_id`, `name`, `desc`, `services_name`, `city_name`, `status`, `is_open`, `city_id`, `response`, `cost`, `services`, `users`, `create_time`, `update_time`) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0)
							$sphinxQl .= ',';
						else
							$cnt++;
						$isOpen = (int) in_array($object['status'], array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED));

						$sphinxQl .= '('.$object['id'].','.intval($object['author_id']).',\''.addslashes($object['name']).'\',\''.addslashes($object['desc']).'\','
                                                        .'\''.addslashes($object['services_name']).'\', \''.addslashes($object['city_name']).'\','
							.$object['status'].','.$isOpen.','.intval($object['city_id']).','.$object['response_count'].','
							.$object['cost'].',('.$object['services'].'),('.$object['users'].'),'.$object['create_time'].','.$object['update_time'].')';
					}
					$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
				}

				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	public function actionMedia()
	{
		echo "====> {$this->_prefix}media indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n\n";

		Yii::import('application.commands.models.MediaReindex');
		$reindex = new MediaReindex();
		$reindex->run();

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	public function actionIdea()
	{

		echo "====> {$this->_prefix}idea indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		Yii::import('application.commands.models.IdeaReindex');
		$reindex = new IdeaReindex();
		$reindex->run();

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	public function actionInteriorPublic()
	{
		echo "====> {$this->_prefix}interiorpublic indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";
		Yii::import('application.modules.idea.models.Interiorpublic');

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM interiorpublic';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_INTERIOR_PUBLIC) {


				$sql = 'SELECT ip.id, ip.object_id as object, ip.status, ip.building_type_id as build, ip.style_id as style, '
					.'ip.create_time, ip.update_time, ip.name as name, '
					.'CONCAT_WS(" ", ih_color.option_value, GROUP_CONCAT(ihc.option_value SEPARATOR " " )) as color, ip.average_rating '
				.'FROM interiorpublic as ip '
				.'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ip.color_id '
				.'LEFT JOIN (SELECT * FROM idea_additional_color WHERE idea_type_id = '.Config::INTERIOR_PUBLIC.') as iac ON iac.item_id = ip.id '
				.'LEFT JOIN idea_heap AS ihc ON iac.color_id = ihc.id '
				.'WHERE ip.id>='.$i.' AND ip.id<'.($i+self::STEP_INTERIOR_PUBLIC).' '
				.'GROUP BY ip.id ';

				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				/** Очистка индекса */
				$sphinxDel = 'DELETE FROM {{interiorpublic}} WHERE id IN (';
				$cnt = 0;
				for ($j = $i; $j < $i+self::STEP_INTERIOR_PUBLIC; $j++) {
					if ($cnt > 0)
						$sphinxDel .= ',';
					$sphinxDel .= $j;
					$cnt++;
				}
				$sphinxDel .= ')';
				Yii::app()->sphinx->createCommand($sphinxDel)->execute();
				// завершение очистки

				$result = 0;
				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{interiorpublic}} (`id`, `name`, `color`, `status`, `object`, `build`, `style`, `average_rating`, `create_time`, `update_time`) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0)
							$sphinxQl .= ',';
						else
							$cnt++;

						$sphinxQl .= '('.$object['id'].',\''.addslashes($object['name']).'\',\''.addslashes($object['color']).'\','
							.$object['status'].','.intval($object['object']).','.intval($object['build']).','.intval($object['style']).','
							.$object['average_rating'].','.$object['create_time'].','.$object['update_time'].')';
					}
					$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
				}

				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}
	
	/**
	 * Переиндексация interior_content 
	 */
	public function actionInteriorContent()
	{
		echo "====> {$this->_prefix}interior_content indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM interior_content';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_INTERIOR_CONTENT) {

				$sql = 'SELECT ic.id, i.id AS interior_id, i.name AS name, i.average_rating, i.desc as `desc`, i.object_id, '
					.'ih_room.option_value AS rooms, ih_room.desc AS room_desc, ih_style.option_value AS styles, '
					.'ih_style.desc AS style_desc, CONCAT_WS(" ", ih_color.option_value, addcolors.addcolors) AS colors, '
					.'ih_color.desc AS color_desc, ic.tag AS tags, i.create_time, i.update_time, i.author_id AS interior_authorid, '
					.'i.status AS status '
				.'FROM interior_content as ic '
					.'LEFT JOIN interior as i ON i.id = ic.interior_id '
						.'LEFT JOIN idea_heap AS ih_obj ON ih_obj.id = i.object_id '
					.'LEFT JOIN idea_heap AS ih_style ON ih_style.id = ic.style_id '
					.'LEFT JOIN idea_heap AS ih_room ON ih_room.id = ic.room_id '
					.'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ic.color_id '
					.'LEFT JOIN ( '
						.'SELECT  item_id , GROUP_CONCAT(idea_heap.option_value SEPARATOR " ")  as addcolors '
						.'FROM idea_additional_color as ic '
						.'INNER JOIN idea_heap ON idea_heap.id = ic.color_id '
						.'WHERE idea_heap.idea_type_id = 1 AND item_id>='.$i.' AND item_id<'.($i+self::STEP_INTERIOR_CONTENT).' '
						.'group by item_id '
					.') as addcolors ON addcolors.item_id = ic.id '
				.'WHERE (i.status = 3 or i.status = 7) AND ic.id>='.$i.' AND ic.id<'.($i+self::STEP_INTERIOR_CONTENT);
				//.'WHERE ic.id>='.$i.' AND ic.id<'.($i+self::STEP_INTERIOR_CONTENT);

				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				/**
				 * Очистка индекса
				 */
				$sphinxDel = 'DELETE FROM {{interior_content}} WHERE id IN (';
				$cnt = 0;
				for ($j = $i; $j < $i+self::STEP_INTERIOR_CONTENT; $j++) {
					if ($cnt > 0)
						$sphinxDel .= ',';
					$sphinxDel .= $j;
					$cnt++;
				}
				$sphinxDel .= ')';
				Yii::app()->sphinx->createCommand($sphinxDel)->execute();
				// завершение очистки

				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{interior_content}} (id, `name`, `desc`, `rooms`, `room_desc`, `styles`, `style_desc`, `colors`, `color_desc`, `tags`, `status`, `interior_id`, `object_id`, `average_rating`, `create_time`, `update_time`) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0) {
							$sphinxQl .= ',';
						}
						$cnt++;

						$sphinxQl .= '('.$object['id'].', \''.addslashes($object['name']).'\', \''.addslashes($object['desc']).'\', \''
						.addslashes($object['rooms']).'\', \''.addslashes($object['room_desc']).'\', \''.addslashes($object['styles']).'\', \''
						.addslashes($object['style_desc']).'\', \''.addslashes($object['colors']).'\', \''.addslashes($object['color_desc']).'\', \''
						.addslashes($object['tags']).'\', '.$object['status'].', '.$object['interior_id'].', '.$object['object_id'].', '
						.$object['average_rating'].', '.$object['create_time'].', '.$object['update_time'].')';
					}
					$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
					$total += $result;
					echo "{$result} items was written \n";
				}
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	/**
	 * Переиндексация user_login
	 */
	public function actionUserLogin()
	{
		echo "====> {$this->_prefix}user_login indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM user';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_USER_LOGIN) {

				$sql = 'SELECT user.id, user.login, CONCAT_WS(" ",user.firstname,user.lastname,user.secondname) as name, user.status as status, '
				.'IF( ISNULL(intCnt.cnt),0,intCnt.cnt) as count_interior, user.create_time, user.update_time, ' 
				.'user.role as role '
				.'FROM user '
				.'LEFT JOIN ( '
					.'SELECT COUNT(interior.id) as cnt, author_id '
					.'FROM interior WHERE (status=2 OR status=3 OR status=4) AND author_id>='.$i.' AND author_id<'.($i+self::STEP_USER_LOGIN).' '
				.') as intCnt ON intCnt.author_id=user.id '
				.'WHERE user.id>='.$i.' AND id<'.($i+self::STEP_USER_LOGIN);

				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{user_login}} (`id`, `login`, `name`, `status`, `count_interior`, `role`, `create_time`, `update_time`) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0)
							$sphinxQl .= ',';
						$cnt++;
						$sphinxQl .= "({$object['id']}, '".addslashes($object['login'])."', '".addslashes($object['name'])
							."',{$object['status']},{$object['count_interior']},{$object['role']},{$object['create_time']}, {$object['update_time']})";
					}

				}
				$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}
	
	/**
	 * Переиндексация user_message 
	 */
	public function actionUserMessage()
	{
		echo "====> {$this->_prefix}user_message indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM msg_body';
			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_USER_MESSAGE) {
				$sql = 'SELECT mb.id, mb.author_id, mb.recipient_id'
					. ', mb.author_status, mb.recipient_status, mb.create_time, mb.message '
					. ', CONCAT_WS(" ", u1.login, u1.firstname, u1.lastname) as author_name_login'
					. ', CONCAT_WS(" ", u2.login, u2.firstname, u2.lastname) as recipient_name_login'
					. ' FROM msg_body mb'
					. ' LEFT JOIN user u1 ON u1.id = mb.author_id'
					. ' LEFT JOIN user u2 ON u2.id = mb.recipient_id'
					. ' WHERE mb.id >= '.$i.' AND mb.id < '.($i+self::STEP_USER_MESSAGE).'';

				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{user_message}}'
						. ' (id, author_id, recipient_id, author_status'
						. ', recipient_status, create_time, message) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0)
							$sphinxQl .= ',';
						$cnt++;
						$msgPlus = addslashes(
							$object['author_name_login']
							. ' ' . $object['recipient_name_login']
							. ' ' . $object['message']
						);
						$sphinxQl .= "({$object['id']}, '{$object['author_id']}',"
							. " '{$object['recipient_id']}', '{$object['author_status']}',"
							. " '{$object['recipient_status']}', {$object['create_time']},"
							. " '".$msgPlus."')";
					}

				}
                                try {
                                        $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
                                } catch (Exception $e) {
                                        echo $e->getMessage();
                                }

				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

    public function actionProduct($indexName = 'product', $conn = null)
    {
        echo "====> {$this->_prefix}product_value indexing <===\n";
        $start = time();
        echo 'Time start: ' . date('d-M-Y H:i:s') . "\n";

        if (!$conn) {
            $conn = Yii::app()->db;
        }

        try {
            $sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM cat_product';
            $result = $conn->createCommand($sql)->queryRow();
            $min = (int) $result['min'];
            $max = (int) $result['max'];
            $cnt = (int) $result['cnt'];
            $total = 0;
            echo "{$cnt} items to index\n";

            Yii::import('application.components.interfaces.*');
            Yii::import('application.modules.catalog.models.*');

            // Получение общих данных для ускорения
            $tmpStyles = $conn->createCommand()->select('id, name')->from('cat_style')->queryAll();
            $styles = array();
            foreach ($tmpStyles as $item) {
                $styles[$item['id']] = $item['name'];
            }

            $tmpColors = $conn->createCommand()->select('id, name')->from('cat_color')->queryAll();
            $colors = array();
            foreach ($tmpColors as $item) {
                $colors[$item['id']] = $item['name'];
            }

            $usageplaces = MainRoom::getAllRooms();

            for ($i = $min; $i <= $max; $i += self::STEP_PRODUCT) {
                /**
                 * Очистка индекса
                 */
                $sphinxDel = 'DELETE FROM {{' . $indexName . '}} WHERE id IN (';
                $cnt = 0;
                for ($j = $i; $j < $i + self::STEP_PRODUCT; $j++) {
                    if ($cnt > 0)
                        $sphinxDel .= ',';
                    $sphinxDel .= $j;
                    $cnt++;
                }
                $sphinxDel .= ')';
                Yii::app()->sphinx->createCommand($sphinxDel)->execute();
                // завершение очистки

                /**
                 * Получение порции товаров
                 */
                $products = $conn->createCommand()
                    ->select('p.id, p.average_price, p.name, p.desc, p.category_id, p.vendor_id,
                                        	p.create_time, p.average_rating, c.params,
                                        	v.name as vendor_name, c.name as category_name, country.name as country_name, tmp_store.is_vitrina,
                                        	tmp_store.max_price as max_price, tmp_store.store_ids as store_ids, tmp_store.mall_ids as mall_ids,
                                        	tmp_room.rooms, tmp_store.is_minisite as is_minisite, tmp_store.is_istore as is_istore')
                    ->from('cat_product p')
                    ->leftJoin('(
						SELECT
							cpr.product_id as product_id, GROUP_CONCAT(cpr.room_id) as rooms
						FROM cat_product_room as cpr
						WHERE cpr.product_id>=:min AND cpr.product_id<:max
						group by cpr.product_id
						) as tmp_room
					', 'tmp_room.product_id=p.id', array(':min' => $i, ':max' => ($i + self::STEP_PRODUCT)))
                    ->leftJoin('cat_category c', 'c.id=p.category_id')
                    ->leftJoin('cat_vendor v', 'v.id=p.vendor_id')
                    ->leftJoin('country country', 'country.id=p.country')
                    ->leftJoin('(SELECT
							csp.product_id, MAX(csp.price) as max_price, SUM( IF(cat_store.tariff_id=:tid AND csp.by_vendor=0, 1, 0) ) as is_vitrina,
							SUM( IF(cat_store.tariff_id=:minisite AND csp.by_vendor=0, 1, 0) ) as is_minisite,
							SUM( IF(cat_store.type=:istore AND csp.by_vendor=0, 1, 0) ) as is_istore,
							GROUP_CONCAT(DISTINCT cat_store.id) as store_ids,
							GROUP_CONCAT(DISTINCT cat_store.mall_build_id) as mall_ids
						FROM cat_store
						INNER JOIN cat_store_price csp ON csp.store_id = cat_store.id
						WHERE (csp.product_id>=:min AND csp.product_id<:max) and cat_store.status=:st_active
						GROUP BY csp.product_id
						) as tmp_store
					', 'tmp_store.product_id = p.id', array(':min' => $i, ':max' => ($i + self::STEP_PRODUCT), ':tid' => Store::TARIF_VITRINA, ':minisite' => Store::TARIF_MINI_SITE, ':istore' => Store::TYPE_ONLINE, ':st_active' => Store::STATUS_ACTIVE))
                    ->where(
                        'p.status=:st and p.id>=:min and p.id<:max',
                        array(':st' => Product::STATUS_ACTIVE, ':min' => $i, ':max' => ($i + self::STEP_PRODUCT))
                    )
                    ->queryAll();
//				print_r($products->getText());
//				die();
//				$products->queryAll();
                if (!$products)
                    continue;

                /**
                 * Список дополнительных полей в индексе для хранения некоторых значений опции, по
                 * которым нужно дать возможность сортировки
                 */
                $opt_val_array = array();
                for ($j = 1; $j < Option::MAX_FILTERABLE_OPTION_QT + 1; $j++) {
                    $opt_val_array[] = 'opt_val_' . $j;
                }

                /**
                 * Обработка имен доп. полей для передачи в запрос
                 */
                $fields = array();
                foreach ($opt_val_array as $opt_val) {
                    $fields[] = '`' . $opt_val . '`';
                }

                /**
                 * Заголовок запроса
                 */
                $sphinxQl = 'REPLACE INTO {{' . $indexName . '}} (`sort_default`, `sort_rand`, `mall_ids`, `store_ids`, `city_ids`, `sort_date`, `sort_price`, `id`, `price`, `category_id`, `vendor_id`, `create_time`, `average_rating`, `name`, `desc`, `vendor_name`, `category_name`, `colors`, `rooms`, `styles`, `country_name`, `prt_by_cat`, `options`, ' . implode(', ', $fields) . ') VALUES ';
                $sphinxQlArray = array();

                foreach ($products as $product) {

                    $catParams = Value::serializeToArrray($product['params']);

                    /**
                     * Получение опций и значений опций для идексируемого товара
                     */
                    $values = $conn->createCommand()
                        ->select('v.value as option_value, o.key as option_key, o.type_id, o.id as option_id')
                        ->from('cat_value v')->leftJoin('cat_option o', 'o.id = v.option_id')
                        ->where('v.product_id=:pid', array(':pid' => $product['id']))->queryAll();

                    $options = array();
                    $rooms_names = array();
                    $colors_names = array();
                    $styles_names = array();

                    /**
                     * Подготовка доп. индексируемых значений опций для вставки
                     */
                    $opt_vals = array();
                    foreach ($opt_val_array as $opt_val) {
                        $opt_vals[$opt_val] = -1;
                    }

                    /**
                     * Индексация опций и значений товара
                     */
                    foreach ($values as $value) {

                        /**
                         * Обрабока multiValue опций
                         */
                        if (Option::$typeParams[$value['type_id']]['multiValue']) {
                            $multiValues = Value::serializeToArrray($value['option_value']);
                            foreach ($multiValues as $mval) {
                                $options[] = $value['option_key'] . ':' . $mval;
                            }
                        } else {
                            /**
                             * Обработка singleValue опций
                             */
                            $options[] = $value['option_key'] . ':' . $value['option_value'];
                        }

                        /**
                         * Обработка фильтруемых опций
                         */
                        if (isset($catParams['filterable_' . $value['type_id']])) {
                            foreach ($catParams['filterable_' . $value['type_id']] as $opt_val_key => $opt_val) {
                                if ($opt_val == $value['option_id']) {
                                    if (!empty($value['option_value']) && preg_match("/^\d+$/", $value['option_value']) != 0)
                                        $opt_vals[$opt_val_key] = (int)$value['option_value'];
                                }
                            }
                        }

                        if ($value['type_id'] == Option::TYPE_COLOR) {
                            $colors_array = Value::serializeToArrray($value['option_value']);
                            foreach ($colors_array as $col) {
                                if (isset($colors[$col]))
                                    $colors_names[] = $colors[$col];
                            }

                        }
                        if ($value['type_id'] == Option::TYPE_STYLE) {
                            if (isset($styles[$value['option_value']]))
                                $styles_names[] = $styles[$value['option_value']];
                        }
                    }

                    $opt_vals = implode(', ', $opt_vals);

                    if (is_string($product['rooms'])) {
                        $rooms = explode(',', $product['rooms']);
                        foreach ($rooms as $room) {
                            $rooms_names[] = isset($usageplaces[$room]) ? $usageplaces[$room] : '';
                            $options[] = 'room' . ':' . intval($room);
                        }
                    }

                    $id = (int)$product['id'];
                    $name = $product['name'];
                    $desc = $product['desc'];
                    $price = (float)$product['average_price'];
                    $cid = (int)$product['category_id'];
                    $cat_name = $product['category_name'];
                    $vendor_id = (int)$product['vendor_id'];
                    $vendor_name = $product['vendor_name'];
                    $country_name = $product['country_name'];
                    $create_time = (int)$product['create_time'];
                    $average_rating = (int)$product['average_rating'];
                    $options = implode(' ', $options);
                    $rooms_names = implode(' ', $rooms_names);
                    $styles_names = implode(' ', $styles_names);
                    $colors_names = implode(' ', $colors_names);
                    $mall_ids = $product['mall_ids'];
                    $store_ids = $product['store_ids'];
                    $city_ids_tmp = StoreGeo::GetStoresCity($store_ids, $conn);
                    $city_ids = implode(',', $city_ids_tmp);

                    /*
                     * ----- Определяем доп. свойства по сортировке даты и цены -----
                     */
                    if ($product['max_price']) {
                        if ($product['max_price'] > 0) { // Если у товара есть цена через магазин
                            $sort_date = 1;
                            $sort_price = 1;
                        } else {             // Если магазин есть, а цена не указана
                            $sort_date = 2;
                            $sort_price = 2;
                        }

                    } else {                 // Если у товара нет привязанных магазинов
                        $sort_date = 3;
                        $sort_price = 2;
                    }

                    /**
                     *  Заполнение дефолтной сортировки
                     * 0 - все товары
                     * 1 - с ценой от бесплатных магазов
                     * 2 - без цены от платных
                     * 3 - с ценой от платных
                     * 4 - без цены от ИМ минисайт
                     * 5 - с ценой от ИМ минисайт
                     */
                    if ($product['is_minisite'] && $product['is_istore']) {

                        if ($product['max_price'] && $product['max_price'] > 0) {
                            $defaultSort = 60;
                        } else {
                            $defaultSort = 55;
                        }

                    } elseif ($product['is_vitrina']) {
                        if ($product['max_price'] && $product['max_price'] > 0) {
                            $defaultSort = 3;
                        } else {
                            $defaultSort = 2;
                        }
                    } else {
                        if ($product['max_price'] && $product['max_price'] > 0) {
                            $defaultSort = 1;
                        } else {
                            $defaultSort = 0;
                        }
                    }

                    $prt_by_cat = 0;
                    // поднятие товаров из приоритетных категорий
                    if (in_array($product['category_id'], Config::$prioritizedCategories)) {
                        $defaultSort = $defaultSort + 2;
                        $prt_by_cat = 1;
                    }


                    $sortRand = rand(0, 50);

                    /**
                     * Сохранение данных о товаре для добавления в индекс
                     */
                    $sphinxQlArray[] = "( {$defaultSort}, {$sortRand}, ({$mall_ids}), ({$store_ids}),({$city_ids}), {$sort_date}, {$sort_price}, {$id}, {$price}, {$cid}, {$vendor_id}, {$create_time}, {$average_rating}, '" . addslashes($name) . "', '" . addslashes($desc) . "', '"
                        . addslashes($vendor_name) . "', '" . addslashes($cat_name) . "', '" . addslashes($colors_names) . "','" . addslashes($rooms_names) . "','"
                        . addslashes($styles_names) . "', '" . addslashes($country_name) . "', '" . addslashes($prt_by_cat) . "','" . addslashes($options) . "', {$opt_vals})";
                }

                /**
                 * Запись пачки данных о товарах в индекс
                 */
                $sphinxQl = $sphinxQl . implode(',', $sphinxQlArray);
                $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
                $total += $result;
                echo "{$result} items was written \n";

            }

        } catch (Exception $e) {
            //echo $e->getTraceAsString()."\n";
            echo $e->getMessage();
        }


        // Обновляем поле defaultSort в индексе для товаров
        $totalUpdate = $this->_updateDefaultSortProduct($indexName, $conn);
        echo "Было проранжировано товаров от платных магазинов: " . $totalUpdate . "шт.\n";


        echo 'Time stop: ' . date('d-M-Y H:i:s') . "\n";
        echo 'Total time: ' . (time() - $start) . "\n\n";
    }

    public function actionProduct2()
    {
        $this->actionProduct('product2', Yii::app()->dbcatalog2);
    }


	/**
	 * Обновляет поле defaultSort в индексе myhome_product таким образом,
	 * чтобы для каждой категории товаров было специально отмечено
	 * по одному товару каждого платного магазина.
	 *
	 * Это делает для того, чтобы на первой странице категории в начале
	 * списка можно было гаранитровано вывести товары от каждого платного магазина.
	 *
	 * Учитываются товары только с ценой.
	 */
	private function _updateDefaultSortProduct($indexName = 'product', $conn)
	{
		Yii::import('application.modules.catalog.models.Category');
		Yii::import('application.modules.catalog.models.Store');


		// Счетчик удачного обновления товаров
		$totalUpdate = 0;

		/* Шаг 1.
		   Получаем список всех конечных категорий */


		/** @var $category Category */
		$category = Category::model()->findByPk(1);
		if (!$category) {
			echo "\nНевозможно обновить товары платных магазинов\n";
			return $totalUpdate;
		}

		$res = $category->getLastDescendants();

		/* Назначаем идекс, который будем писать в defaultSort
		   Назначаем 10 в связи с тем, что резервируем с 1 по 9 */
		$sortIndex = 10;

		if ($res) {
			foreach ($res as $cat){

				/* Шаг 2.
				   Получаем список всех платных магазинов,
				   продающих товары из категории $cat */

				$catId = intval($cat['id']);

				// поднимаем товары из приоритетных категорий
				if ( in_array($catId, Config::$prioritizedCategories) )
					$sortIndex = 11;
				else
					$sortIndex = 10;

				$sql = 'SELECT DISTINCT s.id, s.name
						FROM cat_store s
					INNER JOIN cat_store_price sp
						ON sp.store_id = s.id
					INNER JOIN cat_product p
						ON p.id = sp.product_id
					WHERE
						sp.price > 0
						AND
						p.category_id = :cid
						AND
						s.tariff_id > :vizitka';

				$shops = $conn
					->createCommand($sql)
					->bindValue(':cid', $catId)
					->bindValue(':vizitka', Store::TARIF_FREE)
					->queryAll();

				foreach ($shops as $s) {
					/* Шаг 3.
					   Для каждого магазина находим один товар из категории $cat
					   с ценой и обновляем его defaultSort в индексе */

					$storeId = intval($s['id']);

					$sql = 'SELECT sp.product_id as id
						FROM cat_store_price sp
							INNER JOIN cat_product p
								ON p.id = sp.product_id
						WHERE p.category_id = :cid AND sp.price > 0 AND sp.store_id = :storeid AND p.status = :active
						ORDER BY sp.update_time DESC
						LIMIT 3';

					$prods = $conn
						->createCommand($sql)
						->bindValues(array(':cid' => $catId, ':storeid' => $storeId, ':active'=>Product::STATUS_ACTIVE))
						->queryAll();

					/*
					 * Если товар нужной категории нужного магазина
					 * имеется в наличии, то обновляем в его индексе
					 * поле defaultSort
					 */
					foreach($prods as $prod) {
						$prodId = intval($prod['id']);

						$sphinxQl = 'UPDATE {{' . $indexName . '}} SET sort_default = :sortIndex WHERE id = :pid';

						$result = Yii::app()->sphinx->createCommand($sphinxQl)
							->bindValue(':sortIndex', $sortIndex)
							->bindValue(':pid', $prodId)
							->execute();

						if ($result) {
							$totalUpdate++;
						}
					}

				}
			}
		}

		return $totalUpdate;
	}

	public function actionArchitecture()
	{
		echo "====> {$this->_prefix}architecture indexing <===\n";
		$start = time();
		echo 'Time start: '.date('d-M-Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM architecture';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_ARCHITECTURE) {

				$sql = 'SELECT ar.id, ar.object_id as object, ar.building_type_id as build, ar.style_id as style, ar.material_id as material, '
					.'ar.floor_id as floor, ar.create_time, ar.update_time, ar.average_rating, ar.status, '
					.'CONCAT_WS(" ", '
						.'CASE WHEN ar.room_mansard = 1 THEN "mansard" ELSE "" END, '
						.'CASE WHEN ar.room_garage = 1 THEN "garage" ELSE "" END, '
						.'CASE WHEN ar.room_ground = 1 THEN "ground" ELSE "" END, '
						.'CASE WHEN ar.room_basement = 1 THEN "basement" ELSE "" END '
					.') as room, ar.name as name, CONCAT_WS(" ", ih_color.option_value, GROUP_CONCAT(ihc.option_value SEPARATOR " " )) as color '
					.'FROM architecture as ar '
					.'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ar.color_id '
					.'LEFT JOIN (SELECT * FROM idea_additional_color WHERE idea_type_id = '.Config::ARCHITECTURE.') as iac ON iac.item_id = ar.id '
					.'LEFT JOIN idea_heap AS ihc ON iac.color_id = ihc.id '
					.'WHERE ar.id>='.$i.' AND ar.id<'.($i+self::STEP_ARCHITECTURE).' '
					.'GROUP BY ar.id';

				$objects = Yii::app()->db->createCommand($sql)->queryAll();

				/** Очистка индекса */
				$sphinxDel = 'DELETE FROM {{architecture}} WHERE id IN (';
				$cnt = 0;
				for ($j = $i; $j < $i+self::STEP_ARCHITECTURE; $j++) {
					if ($cnt > 0)
						$sphinxDel .= ',';
					$sphinxDel .= $j;
					$cnt++;
				}
				$sphinxDel .= ')';
				Yii::app()->sphinx->createCommand($sphinxDel)->execute();
				// завершение очистки

				$result = 0;
				if (!empty($objects)) {
					$sphinxQl = 'REPLACE INTO {{architecture}} (`id`, `name`, `color`, `room`, `status`, `object`, `build`, `material`, `floor`, `style`, `average_rating`, `create_time`, `update_time`) VALUES ';
					$cnt = 0;
					foreach ($objects as $object) {
						if ($cnt > 0)
							$sphinxQl .= ',';
						else
							$cnt++;
						$sphinxQl .= '('.$object['id'].',\''.addslashes($object['name']).'\',\''.addslashes($object['color']).'\',\''.addslashes($object['room']).'\','
							.$object['status'].','.intval($object['object']).','.intval($object['build']).','.intval($object['material']).','.intval($object['floor']).','
							.intval($object['style']).','.$object['average_rating'].','.$object['create_time'].','.$object['update_time'].')';
					}
					$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
				}

				$total += $result;
				echo "{$result} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}
}
