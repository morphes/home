<?php

/**
 * This is the model class for table "user_service".
 *
 * The followings are the available columns in table 'user_service':
 * @property integer $user_id
 * @property integer $service_id
 * @property integer $experience
 */
class ServiceUser extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ServiceUser the static model class
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
		return 'user_service';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('user_id, service_id', 'required'),
			array('user_id, service_id, experience, segment, segment_supp', 'numerical', 'integerOnly'=>true),
			array('rating', 'numerical'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'service_id' => 'Service',
			'experience' => 'Experience',
		);
	}

        /**
         * Возвращает список лучших специалистов
         * Если указан город - возвращает список лучших в данном городе
         * @param $city
         * @return array
         */
        static public function getBestSpecs($city)
        {
                $sphinx = Yii::app()->sphinx;
                $bestUsers = array();
                $services = Yii::app()->db->createCommand()->select('id, name')->from(Service::model()->tableName())->where('popular=1')->queryAll();

                foreach($services as $s) {
                        if($city)
                                $users = $sphinx->createCommand('SELECT max(rating) as rat, user_id as uid FROM {{user_service}} where service_id='.$s['id'].' and city_id='.$city->id.' order by rat desc, project_qt desc limit 10')
                                        ->queryAll();
                        else
                                $users = $sphinx->createCommand('SELECT max(rating) as rat, user_id as uid FROM {{user_service}} where service_id='.$s['id'].' order by rat desc, project_qt desc limit 10')
                                        ->queryAll();

                        foreach($users as $u) {
                                if(!array_key_exists($u['uid'], $bestUsers)) {
                                        $bestUsers[$u['uid']] = array('uid'=>$u['uid'], 'service_name'=>$s['name']);
                                        break;
                                }
                        }
                }

                return $bestUsers;
        }

	/**
	 * Возвращает массив идентификаторов Услуг с количеством специалистов по каждой из них.
	 * Если указан $city, то данные только по конкретному городу.
	 * @param $city mixed integer|City Принимает два возможных значения. Или объект City или id города.
	 * @return array Ассоциативный массив array('id_услуги' => 'кол-во спецов', ...)
	 */
	static public function getUserQtByCity($city)
        {
		if ($city instanceof City)
			$cityId = $city->id;
		elseif (is_numeric($city))
			$cityId = (int)$city;


                $sphinx = Yii::app()->sphinx;
                $baseQuery = 'SELECT count(DISTINCT user_id) as cnt, service_id FROM {{user_service}} ';
                if(isset($cityId) && $cityId > 0)
                        $sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND `status`=2 AND `city_id`='.$cityId;
                else
                        $sphinxQl = $baseQuery . 'WHERE role IN(3,4) AND `status`=2';
                $sphinxQl .= ' GROUP BY service_id LIMIT 200';

                $result =  $sphinx->createCommand($sphinxQl)->queryAll();

                $services = array();
                foreach($result as $item) {
                        $services[$item['service_id']]=$item['cnt'];
                }

                return $services;
        }
}