<?php

/**
 * This is the model class for table "stat_user_service".
 *
 * The followings are the available columns in table 'stat_user_service':
 * @property integer $id
 * @property integer $user_id
 * @property integer $city_id
 * @property integer $service_id
 * @property integer $view
 * @property integer $type
 * @property integer $time
 */
class StatUserService extends CActiveRecord
{
	//Показ профиля в списке услуг
	const TYPE_SHOW_PROFILE_SERVICE = 1;

	const TYPE_CLICK_PROFILE_SERVICE = 2;

	public $viewString;
	public $typeString;


	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return StatUserService the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'stat_user_service';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, city_id, service_id, view, type, time, viewString, typeString', 'numerical', 'integerOnly' => true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, city_id, service_id, view, type, time', 'safe', 'on' => 'search'),
		);
	}


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'         => 'ID',
			'user_id'    => 'User',
			'city_id'    => 'City',
			'service_id' => 'Service',
			'view'       => 'View',
			'type'       => 'Type',
			'time'       => 'Time',
		);
	}


	public static $typeLabels = array(
		self::TYPE_SHOW_PROFILE_SERVICE  => 'Просмотров профиля',
		self::TYPE_CLICK_PROFILE_SERVICE => 'Кликов по профилю'
	);


	/**
	 * Увеличивает количество просмотров на 1 для специалиста $specialist_id
	 * по типу $type
	 *
	 * @param $store_id Идентификатор магазина
	 * @param $type     Тип накапливаемой статистики
	 */
	static public function hit($userId, $serviceId, $cityId, $type)
	{
		Yii::app()->redis->incr(self::getRedisKeyStore($userId, $serviceId, $cityId, $type));
	}


	static public function getRedisKeyStore($userId, $serviceId, $cityId, $type)
	{
		$time = date('d.m.Y');

		return 'STAT:USER:' . $userId . ':SERVICE:' . $serviceId . ':CITY:' . $cityId . ':TYPE:' . $type . ':' . $time;
	}


	/**
	 * Метод переносит данные из Redis
	 * В mysql
	 * Паттерн для поиска ключе задается в переменной $pattern
	 *
	 * @param $pattern
	 */
	static public function updateStatUserServMySql($pattern)
	{
		$keys = Yii::app()->redis->keys($pattern);
		$sqlInsert = 'INSERT INTO stat_user_service (`user_id`, `city_id`, `service_id`, `view`, `type`, `time`)
						VALUES (:userId, :cityId, :serviceId , :view, :type, :time)';

		$sqlSelect = 'SELECT * FROM stat_user_service WHERE user_id = :userId
					AND city_id = :cityId AND service_id = :serviceId
					AND type = :type AND time = :time';

		$sqlUpdate = 'UPDATE stat_user_service SET `view`= `view` + :view WHERE id = :id';
		$sqlValues = '';


		// Ключи вида STAT:PROJECT:3024:TYPE:3:19.04.2013
		$transaction = Yii::app()->db
			->beginTransaction();
		try {
			foreach ($keys as $key) {
				preg_match('#^STAT:USER:([\d]+):SERVICE:([\d]+):CITY:([\d]+):TYPE:([\d]+):([\d]{1,2}\.[\d]{1,2}\.[\d]{4})#', $key, $matches);

                try {

                    if (!isset($matches[1]) || !isset($matches[2]) || !isset($matches[3]) || !isset($matches[4]) || !isset($matches[5])) {
                        continue;
                    }

                    $userId = (int)$matches[1];
                    $serviceId = (int)$matches[2];
                    $cityId = (string)$matches[3];
                    $type = (int)$matches[4];
                    $time = strtotime($matches[5]);
                    $view = Yii::app()->redis->get($key);
                } catch (Exception $e) {
                    continue;
                }

				$selectResult = Yii::app()->db->createCommand($sqlSelect)
					->bindParam(':userId', $userId)
					->bindParam(':cityId', $cityId)
					->bindParam(':serviceId', $serviceId)
					->bindParam(':time', $time)
					->bindParam(':type', $type)
					->queryRow();

				if (!$selectResult) {
					Yii::app()->db->createCommand($sqlInsert)
						->bindParam(':userId', $userId)
						->bindParam(':cityId', $cityId)
						->bindParam(':serviceId', $serviceId)
						->bindParam(':view', $view)
						->bindParam(':type', $type)
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

    static public function updateStatUserServMySqlSafe($pattern)
    {
        $keys = Yii::app()->redis->keys($pattern);
        $sqlInsert = 'INSERT INTO stat_user_service (`user_id`, `city_id`, `service_id`, `view`, `type`, `time`)
						VALUES (:userId, :cityId, :serviceId , :view, :type, :time)';

        $sqlSelect = 'SELECT * FROM stat_user_service WHERE user_id = :userId
					AND city_id = :cityId AND service_id = :serviceId
					AND type = :type AND time = :time';

        $sqlUpdate = 'UPDATE stat_user_service SET `view`= `view` + :view WHERE id = :id';
        $sqlValues = '';

        foreach ($keys as $key) {
            preg_match('#^STAT:USER:([\d]+):SERVICE:([\d]+):CITY:([\d]+):TYPE:([\d]+):([\d]{1,2}\.[\d]{1,2}\.[\d]{4})#', $key, $matches);

            try {

                if (!isset($matches[1]) || !isset($matches[2]) || !isset($matches[3]) || !isset($matches[4]) || !isset($matches[5])) {
                    continue;
                }

                $userId = (int)$matches[1];
                $serviceId = (int)$matches[2];
                $cityId = (string)$matches[3];
                $type = (int)$matches[4];
                $time = strtotime($matches[5]);
                $view = Yii::app()->redis->get($key);
            } catch (Exception $e) {
                continue;
            }

            $selectResult = Yii::app()->db->createCommand($sqlSelect)
                ->bindParam(':userId', $userId)
                ->bindParam(':cityId', $cityId)
                ->bindParam(':serviceId', $serviceId)
                ->bindParam(':time', $time)
                ->bindParam(':type', $type)
                ->queryRow();

            if (!$selectResult) {
                Yii::app()->db->createCommand($sqlInsert)
                    ->bindParam(':userId', $userId)
                    ->bindParam(':cityId', $cityId)
                    ->bindParam(':serviceId', $serviceId)
                    ->bindParam(':view', $view)
                    ->bindParam(':type', $type)
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


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('city_id', $this->city_id);
		$criteria->compare('service_id', $this->service_id);
		$criteria->compare('view', $this->view);
		$criteria->compare('type', $this->type);
		$criteria->compare('time', $this->time);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}


	/**
	 * Метод возвращает датапровайдер
	 * со статистикой
	 * возможна фильтрация по городам
	 * и датам
	 * @param      $userId
	 * @param int  $dateFrom
	 * @param int  $dateTo
	 * @param bool $city
	 *
	 * @return CActiveDataProvider
	 */
	public function getStatTable($userId, $dateFrom = 0, $dateTo = 0, $city = false)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'user_id, city_id, type, service_id, SUM(view) as view,
			GROUP_CONCAT(type) as typeString,GROUP_CONCAT(view) as viewString',
			'condition' => 'user_id = :sid',
			'group'     => 'service_id ',
			'params'    => array(':sid' => $userId)
		));

		if ($dateFrom > 0) {
			$criteria->compare('time', '>=' . $dateFrom);
		}


		if ($dateTo > 0) {
			$criteria->compare('time', '<=' . $dateTo);
		}

		if ($city !== false) {
			$criteria->compare('city_id', '=' . $city);
		}

		return new CActiveDataProvider($this, array(
			'criteria'   => $criteria,
			'pagination' => array(
				'pageSize' => 100,
			),
		));
	}


	/**
	 * Получить данные по просмотрам
	 * В качестве параметра передается
	 * тип статистики
	 * @param $type
	 *
	 * @return int
	 *
	 */
	public function getViewData($type)
	{
		$arrayTypes = array();
		$arrayView = array();
		$dataArray = array();

		$arrayTypes = explode(',', $this->typeString);
		$arrayView = explode(',', $this->viewString);

		$tmpT = reset($arrayTypes);
		$tmpV = reset($arrayView);

		$resultArray = array();
		while ($tmpT) {
			if (!isset($resultArray[$tmpT])) {
				$resultArray[$tmpT] = $tmpV;
			} else {
				$resultArray[$tmpT] = $resultArray[$tmpT] + $tmpV;
			}
			$tmpT = next($arrayTypes);
			$tmpV = next($arrayView);
		}

		if (isset($resultArray[$type])) {
			return $resultArray[$type];
		} else {
			return 0;
		}
	}

	/**
	 * Метод возвращает
	 * список городов в
	 * которых есть статистика
	 * @param $userId
	 *
	 * @return array
	 */
	public static function getListCity($userId)
	{
		$criteria = new CDbCriteria;
		$criteria->select = 'city_id';
		$criteria->condition = 'user_id=' . $userId;
		$criteria->group = 'city_id';
		$builder = new CDbCommandBuilder(Yii::app()->db->getSchema());
		$command = $builder->createFindCommand('stat_user_service', $criteria);
		$cityIds = $command->queryColumn();

		if ($cityIds) {
			foreach ($cityIds as $id) {
				$listCity[$id] = City::model()->getNameById($id);
			}
		} else {
			return array();
		}


		return $listCity;
	}
}