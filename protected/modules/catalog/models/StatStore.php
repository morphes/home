<?php

/**
 * This is the model class for table "stat_store".
 *
 * The followings are the available columns in table 'stat_store':
 * @property integer $id
 * @property integer $store_id
 * @property integer $type
 * @property integer $time
 */
class StatStore extends CActiveRecord
{
	/* Список типов статистических данных */

	// Кол-во посещений страницы магазина
	const TYPE_HIT_STORE = 1;
	// Кол-во просмотров товаров через собственный каталог
	const TYPE_HIT_OWN_PRODUCT = 2;
	// Кол-во просмотров товаров через общий каталог
	const TYPE_HIT_COMMON_PRODUCT = 3;
	// Кол-во посещений сайта магазина
	const TYPE_SITE = 4;

	public $timeFrom;
	public $timeTo;



	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StatStore the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'stat_store';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('store_id, type, view, time', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, store_id, type, time', 'safe', 'on'=>'search'),
			array('id, store_id, type, time, view,typeLabels', 'safe', 'on'=>'getStatGroupByDat'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	public static $typeLabels = array(
		self::TYPE_HIT_STORE       => 'Посещения магазина',
		self::TYPE_HIT_OWN_PRODUCT => 'Просмотры товара со страницы магазина',
		self::TYPE_HIT_COMMON_PRODUCT  => 'Просмотры товара через каталог',
		self::TYPE_SITE  => 'Переходы на сайт',

	);

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'       => 'ID',
			'store_id' => 'Магазин',
			'type'     => 'Тип',
			'time'     => 'Время перехода',
			'view'	   => 'Переходов',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('time',$this->time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Увеличивает количество просмотров на 1 для магазина $store_id
	 * по типу $type
	 *
	 * @param $store_id Идентификатор магазина
	 * @param $type Тип накапливаемой статистики
	 */
	static public function hit($store_id, $type)
	{
		Yii::app()->redis->incr( self::getRedisKeyStore($store_id, $type) );
	}


	/**
	 * Возвращает ключ для Redis'а
	 *
	 * @param $store_id Идентификатор магазина
	 * @param $type Тип накапливаемой статистики
	 *
	 * @return string Строка-ключ для Redis
	 */
	static public function getRedisKeyStore($store_id, $type)
	{
		$time = date('d.m.Y');
		return 'STAT:STORE:' . $store_id . ':TYPE:' . $type . ':' . $time;
	}


	/**
	 * Всем магазинам, у которых есть платный тариф, в счетчики просмотра
	 * товаров наращиваем по единице.
	 *
	 * @param $product_id Идентификатор товара по которому ищутся
	 * магазины, продающие его.
	 */
	static public function hitAllStores($product_id)
	{
		$productId = (int)$product_id;

		/* Получаем список всех магазинов, которые
		 * продают товар $product_id
		 */
		$sql = 'SELECT DISTINCT cs.id FROM cat_store cs'
			. ' INNER JOIN cat_store_price csp'
			. ' on csp.store_id=cs.id'
			. ' WHERE csp.product_id = :pid';

		$stores = Yii::app()->db
			->createCommand($sql)
			->bindValue(':pid', $productId)
			->queryAll();

		// Для каждого магазина делаем наращивание просмотра
		foreach ($stores as $store) {
			StatStore::hit($store['id'], StatStore::TYPE_HIT_COMMON_PRODUCT);
		}
	}


	/**
	 * Возвращает массив данных по магазину $storeId в проемежутке времени
	 * от $dateFrom до $dateTo
	 *
	 * @param     $storeId Идентификатор магазина
	 * @param int $dateFrom Начало периода
	 * @param int $dateTo Конец периода
	 *
	 * @return array
	 */
	static public function getStatData($storeId, $dateFrom = 0, $dateTo = 0)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'store_id, time, type, SUM(view) as view',
			'condition' => 'store_id = :sid',
			'group'     => 'type',
			'params'    => array(':sid' => $storeId)
		));

		if ($dateFrom > 0) {
			$criteria->addCondition('time >= :from_time');
			$criteria->params[':from_time'] = (int)$dateFrom;
		}

		if ($dateTo > 0) {
			$criteria->addCondition('time <= :to_time');
			$criteria->params[':to_time'] = (int)$dateTo;
		}


		// Формируем из Критерии обычный DAO запрос
		$builder = new CDbCommandBuilder(Yii::app()->db->getSchema());
		$command = $builder->createFindCommand('stat_store', $criteria);
		$stat = $command->queryAll();



		$result = array(
			StatStore::TYPE_HIT_STORE          => 0,
			StatStore::TYPE_SITE               => 0,
			StatStore::TYPE_HIT_COMMON_PRODUCT => 0,
			StatStore::TYPE_HIT_OWN_PRODUCT    => 0
		);

		if ($stat) {
			foreach ($stat as $s) {
				$result[ $s['type'] ] = $s['view'];
			}
		}

		return $result;
	}


	/**
	 * Вернуть статистику магазина сгрупированную по дням
	 * @param $storeId
	 *
	 * @return CActiveDataProvider
	 */
	public function getStatGroupByDay($storeId)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'sum(view) as view,time,type',
			'group'     => 'type,time',
			'order'     => 'time DESC,type ',
			'condition' => 'store_id = :sid',
			'params'    => array(':sid' => $storeId),

		));


		return new CActiveDataProvider($this, array(
			'criteria'   => $criteria,
			'pagination' => array('pageSize' => 20),
		));
	}


	/**
	 * Вернуть статистику сгруппированную по период
	 * @param     $storeId
	 * @param int $dateFrom
	 * @param int $dateTo
	 *
	 * @return CActiveDataProvider
	 */
	public function getStatGroupByPeriod($storeId, $dateFrom = 0, $dateTo = 0)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'store_id, time, type, SUM(view) as view',
			'condition' => 'store_id = :sid',
			'group'     => 'type',
			'params'    => array(':sid' => $storeId)
		));

		if ($dateFrom > 0) {
			$criteria->compare('time', '>=' . $dateFrom);
		}


		if ($dateTo > 0) {
			$criteria->compare('time', '<=' . $dateTo);
		}


		return new CActiveDataProvider($this, array(
			'criteria'   => $criteria,
			'pagination' => array('pageSize' => 20),
		));
	}


	/**
	 * Возвращает массив магазинов, по которым есть собранная статистика
	 * за период $dateFrom — $dateTo
	 *
	 * @param int $dateFrom Дата начала периода
	 * @param int $dateTo Дата окончания периода
	 * @param int $limit Лимит на количество возвращаемых магазинов.
	 * 	По-умолчанию 100.
	 *
	 * @return array Результирующий массив вида:
	 * <pre>
	 * array(
	 * 	'id магазина' => array(
	 * 		'views' => array(
	 * 			'тип статистики' => 'кол-во просмотров',
	 * 			...
	 * 		),
	 * 		'tariff_id' => 'id тарифа',
	 * 		'name' => 'название магазина',
	 * 		'city' => 'Город магазина',
	 * 		'address' => 'Адрес магазина',
	 * 	),
	 * 	...
	 * )
	 * </pre>
	 */
	static public function getStatAllStoresByPeriod($dateFrom = 0, $dateTo = 0, $limit = 100)
	{
		$result = array();

		/*
		 * Шаг 1.
		 * Получаем список всех магазинов, на которых есть статистика
		 * просмотров за указанный период.
		 */
		$sql = 'SELECT
				stat.store_id as id, stat.type as type, SUM(stat.view) as view,
				s.tariff_id, s.tariff_expire_date,
				city.name as city, s.address, s.name
			FROM stat_store stat
			LEFT JOIN cat_store s ON s.id = stat.store_id AND s.type='.Store::TYPE_OFFLINE.'
			LEFT JOIN cat_store_city as sc ON sc.store_id=s.id
			LEFT JOIN city ON city.id = sc.city_id
			WHERE stat.time >= :dateFrom AND stat.time < :dateTo
			GROUP BY stat.store_id, stat.type
			HAVING view > 0
			ORDER BY s.tariff_id DESC, s.id
			LIMIT :limit';

		$res = Yii::app()->db
			->createCommand($sql)
			->bindValue(':dateFrom', $dateFrom)
			->bindValue(':dateTo', $dateTo)
			->bindValue(':limit', $limit)
			->queryAll();

		/*
		 * Шаг 2.
		 * Формируем из списка магизнов со статистикой массив для выдачи.
		 */
		foreach ($res as $stat) {
			$result[$stat['id']]['views'][$stat['type']] = $stat['view'];
			$result[$stat['id']]['tariff_id'] = $stat['tariff_id'];
			$result[$stat['id']]['tariff_expire_date'] = $stat['tariff_expire_date'];
			$result[$stat['id']]['name'] = $stat['name'];
			$result[$stat['id']]['city'] = $stat['city'];
			$result[$stat['id']]['address'] = $stat['address'];
		}

		return $result;
	}


	/**
	 * Метод переносит данные из Redis
	 * В mysql
	 * Паттерн для поиска ключе задается в переменной $pattern
	 *
	 * @param $pattern
	 */
	static public function updateStatStoreMySql($pattern)
	{
		$keys = Yii::app()->redis->keys($pattern);
		$sqlInsert = 'INSERT INTO stat_store (`store_id`, `type`, `view`, `time`) VALUES (:store_id, :type, :view, :time) ';
		$sqlSelect = 'SELECT * FROM stat_store WHERE time = :time AND store_id = :store_id AND type = :type';
		$sqlUpdate = 'UPDATE stat_store SET `view`= `view` + :view WHERE id = :id';
		$sqlValues = '';

		// Ключи вида STAT:SPECIALIST:3024:TYPE:3:19.04.2013
		$transaction = Yii::app()->db
			->beginTransaction();
		try {
			foreach ($keys as $key) {
				preg_match('#^STAT:STORE:([\d]+):TYPE:([\d]+):([\d]{1,2}\.[\d]{1,2}\.[\d]{4})#', $key, $matches);

                try {
                    if (!isset($matches[1]) || !isset($matches[2]) || !isset($matches[3])) {
                        continue;
                    }
                    $store_id = (int)$matches[1];
                    $type = (int)$matches[2];
                    $time = strtotime($matches[3]);
                    $view = Yii::app()->redis->get($key);
                } catch (Exception $e) {
                    continue;
                }


				$selectResult = Yii::app()->db->createCommand($sqlSelect)
					->bindParam(':time', $time)
					->bindParam(':store_id', $store_id)
					->bindParam(':type', $type)
					->queryRow();

				if (!$selectResult) {
					Yii::app()->db->createCommand($sqlInsert)
						->bindParam(':store_id', $store_id)
						->bindParam(':type', $type)
						->bindParam(':view', $view)
						->bindParam(':time', $time)
						->execute();

				} else {
					$id = (int)$selectResult['id'];
					Yii::app()->db->createCommand($sqlUpdate)
						->bindParam(':id', $id)
						->bindParam(':view', $view)
						->execute();
				}
			}

			$transaction->commit();

			//Удаляем ключи из редиса
			foreach ($keys as $key) {
				Yii::app()->redis->delete($key);
			}
		} catch (Exception $e) {
			$transaction->rollBack();
		}
	}

    static public function updateStatStoreMySqlSafe($pattern)
    {
        $keys = Yii::app()->redis->keys($pattern);
        $sqlInsert = 'INSERT INTO stat_store (`store_id`, `type`, `view`, `time`) VALUES (:store_id, :type, :view, :time) ';
        $sqlSelect = 'SELECT * FROM stat_store WHERE time = :time AND store_id = :store_id AND type = :type';
        $sqlUpdate = 'UPDATE stat_store SET `view`= `view` + :view WHERE id = :id';
        $sqlValues = '';


        foreach ($keys as $key) {
            preg_match('#^STAT:STORE:([\d]+):TYPE:([\d]+):([\d]{1,2}\.[\d]{1,2}\.[\d]{4})#', $key, $matches);

            try {
                if (!isset($matches[1]) || !isset($matches[2]) || !isset($matches[3])) {
                    continue;
                }
                $store_id = (int)$matches[1];
                $type = (int)$matches[2];
                $time = strtotime($matches[3]);
                $view = Yii::app()->redis->get($key);
            } catch (Exception $e) {
                continue;
            }

            $selectResult = Yii::app()->db->createCommand($sqlSelect)
                ->bindParam(':time', $time)
                ->bindParam(':store_id', $store_id)
                ->bindParam(':type', $type)
                ->queryRow();

            if (!$selectResult) {
                Yii::app()->db->createCommand($sqlInsert)
                    ->bindParam(':store_id', $store_id)
                    ->bindParam(':type', $type)
                    ->bindParam(':view', $view)
                    ->bindParam(':time', $time)
                    ->execute();

            } else {
                $id = (int)$selectResult['id'];
                Yii::app()->db->createCommand($sqlUpdate)
                    ->bindParam(':id', $id)
                    ->bindParam(':view', $view)
                    ->execute();
            }
            Yii::app()->redis->delete($key);
        }
    }
}