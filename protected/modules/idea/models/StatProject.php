<?php

/**
 * This is the model class for table "stat_project".
 *
 * The followings are the available columns in table 'stat_project':
 * @property integer $id
 * @property integer $model_id
 * @property string $model
 * @property integer $view
 * @property integer $type
 * @property integer $time
 */
class StatProject extends CActiveRecord
{

	/**
	 * Просмотр проектов
	 */
	const TYPE_PROJECT_VIEW = 1;

	/**
	 * Добавление проекта
	 * в избранное.
	 */
	const TYPE_PROJECT_TO_FAVORITES = 2;

	/**
	 * Показов прокта в списке
	 * проектов
	 */
	const TYPE_SHOW_PROJECT_IN_LIST = 3;

	/**
	 * Кликов по проекту в списках
	 * проектов
	 */
	const TYPE_CLICK_PROJECT_IN_LIST = 4;

	public $viewString;
	public $typeString;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StatProject the static model class
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
		return 'stat_project';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model_id, view, type, time', 'numerical', 'integerOnly'=>true),
			array('model, viewString, typeString', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, model_id, model, view, type, time', 'safe', 'on'=>'search'),
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

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'model_id' => 'Model',
			'model' => 'Model',
			'view' => 'View',
			'type' => 'Type',
			'time' => 'Time',
		);
	}

	/**
	 * Увеличивает количество просмотров на 1 для проекта $project_id
	 * по типу $type
	 *
	 * @param $store_id Идентификатор магазина
	 * @param $type Тип накапливаемой статистики
	 */
	static public function hit($model_id, $model, $author_id,   $type)
	{
		Yii::app()->redis->incr( self::getRedisKeyStore($model_id, $model, $author_id,  $type));
	}

	/**
	 * Возвращает ключ для Redis'а
	 *
	 * @param $specialist_id Идентификатор специалиста
	 * @param $type Тип накапливаемой статистики
	 *
	 * @return string Строка-ключ для Redis
	 */
	static public function getRedisKeyStore($model_id, $model, $author_id, $type)
	{
		$time = date('d.m.Y');
		return 'STAT:PROJECT:' . $author_id  . ':MODEL:' . $model . ':MODELID:' . $model_id . ':TYPE:' . $type . ':' . $time;
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
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('view',$this->view);
		$criteria->compare('type',$this->type);
		$criteria->compare('time',$this->time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Метод переносит данные из Redis
	 * В mysql
	 * Паттерн для поиска ключе задается в переменной $pattern
	 *
	 * @param $pattern
	 */
	static public function updateStatProjectMySql($pattern)
	{
		$keys = Yii::app()->redis->keys($pattern);
		$sqlInsert = 'INSERT INTO stat_project (`author_id`, `model_id`, `model`, `view`, `type`, `time`)
						VALUES (:authorId, :modelId, :model , :view, :type, :time)';

		$sqlSelect = 'SELECT * FROM stat_project WHERE author_id = :authorId
					AND model_id = :modelId AND model = :model
					AND type = :type AND time = :time';

		$sqlUpdate = 'UPDATE stat_project SET `view`= `view` + :view WHERE id = :id';
		$sqlValues = '';


		// Ключи вида STAT:PROJECT:3024:TYPE:3:19.04.2013
		$transaction = Yii::app()->db
			->beginTransaction();
		try
		{
			foreach($keys as $key)
			{
				preg_match('#^STAT:PROJECT:([\d]+):MODEL:([\w]+):MODELID:([\d]+):TYPE:([\d]+):([\d]{1,2}\.[\d]{1,2}\.[\d]{4})#', $key, $matches);

				$authorId = (int)$matches[1];
				$model = (string)$matches[2];
				$modelId = (int)$matches[3];
				$type = (int)$matches[4];
				$time = strtotime($matches[5]);
				$view = Yii::app()->redis->get($key);
                if($view < 1) {
                    $view = 1;
                }


				$selectResult = Yii::app()->db->createCommand($sqlSelect)
					->bindParam(':authorId', $authorId)
					->bindParam(':modelId', $modelId)
					->bindParam(':model', $model)
					->bindParam(':time', $time)
					->bindParam(':type', $type)
					->queryRow();

				if(!$selectResult)
				{
					Yii::app()->db->createCommand($sqlInsert)
						->bindParam(':authorId', $authorId)
						->bindParam(':modelId', $modelId)
						->bindParam(':model', $model)
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


	/**
	 * Вернуть статистику за период
	 * @param     $specialistId
	 * @param int $dateFrom
	 * @param int $dateTo
	 *
	 * @return array
	 */
	public function getStat($specialistId, $dateFrom = 0, $dateTo = 0)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'model_id,author_id, time, type, SUM(view) as view',
			'condition' => 'author_id = :sid',
			'group'     => 'type',
			'params'    => array(':sid' => $specialistId)
		));

		if ($dateFrom > 0) {
			$criteria->compare('time', '>=' . $dateFrom);
		}


		if ($dateTo > 0) {
			$criteria->compare('time', '<=' . $dateTo);
		}

		// Формируем из Критерии обычный DAO запрос
		$builder = new CDbCommandBuilder(Yii::app()->db->getSchema());
		$command = $builder->createFindCommand('stat_project', $criteria);
		$stat = $command->queryAll();

		$result = array(
			self::TYPE_PROJECT_VIEW          => 0,
			self::TYPE_PROJECT_TO_FAVORITES  => 0,
			self::TYPE_SHOW_PROJECT_IN_LIST  => 0,
			self::TYPE_CLICK_PROJECT_IN_LIST => 0,
		);

		if ($stat) {
			foreach ($stat as $s) {
				$result[$s['type']] = $s['view'];
			}
		}


		return $result;
	}


	/**
	 * Метод возвращает датапровайдер
	 * для построения таблицы
	 * в статистике по специалистам
	 * @param     $authorId
	 * @param int $dateFrom
	 * @param int $dateTo
	 *
	 * @return CActiveDataProvider
	 *
	 */
	public function getStatTable($authorId, $dateFrom = 0, $dateTo = 0)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'model, model_id,author_id, time, type, SUM(view) as view,
					GROUP_CONCAT(type) as typeString,GROUP_CONCAT(view) as viewString',
			'condition' => 'author_id = :sid',
			'group'     => 'model_id, model',
			'params'    => array(':sid' => $authorId)
		));

		if ($dateFrom > 0) {
			$criteria->compare('time', '>=' . $dateFrom);
		}


		if ($dateTo > 0) {
			$criteria->compare('time', '<=' . $dateTo);
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

		// заплатка для скрытия ошибки
		if ( count($arrayTypes) <> count($arrayView) )
			return 0;

		$dataArray = array_combine($arrayTypes, $arrayView);
		if (isset($dataArray[$type])) {
			return $dataArray[$type];
		} else {
			return 0;
		}
	}


	/**
	 * Узнать размещен ли проект в идеях
	 * или нет
	 * @return bool
	 */
	public function getProjectStatus()
	{
		$className = $this->model;
		$model = $className::model();

		if ($model instanceof Portfolio) {
			return false;
		}

		if ($model instanceof Interior || $model instanceof Interiorpublic || $model instanceof Architecture) {
			if ($model->findByPk($this->model_id)->status == Interior::STATUS_ACCEPTED) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Получить ссылку на проект
	 * @return string
	 */
	public function getLink()
	{
		$className = $this->model;
		$model = $className::model()->findByPk($this->model_id);

		if ($model instanceof Portfolio) {
			return $model->getElementLink();
		}

		if ($model->status == Interior::STATUS_ACCEPTED) {
			return $model->getIdeaLink();
		} else {
			return $model->getElementLink();
		}
	}


	/**
	 * Получить обложку изображения
	 * @param $config
	 *
	 * @return mixed
	 */
	public function getImage($config)
	{
		$className = $this->model;
		$model = $className::model()->findByPk($this->model_id);

		if ($model instanceof Architecture) {
			$image = $model->getPreview('crop_80');
		} else {
			$image = $model->getPreview($config);
		}


		return $image;
	}
}