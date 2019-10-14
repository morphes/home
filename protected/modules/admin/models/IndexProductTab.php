<?php

/**
 * This is the model class for table "index_product_tab".
 *
 * The followings are the available columns in table 'index_product_tab':
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property integer $position
 * @property string $rubric
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexProductTab extends CActiveRecord
{
	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class' => 'application.components.ModelTimeBehavior',
			),
			'PositionBehavior' => array(
				'class' => 'application.components.PositionBehavior'
			)
		);
	}


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IndexProductTab the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	public function init()
	{
		$this->onAfterSave = array($this, 'clearCache');

		parent::init();
	}

	public function clearCache()
	{
		// Удаляем кеш хранящий список табов
		Yii::app()->cache->delete(self::getCacheKeyTab());

		// Удалаем кеш хранящий список рубрик
		Yii::app()->cache->delete(self::getCacheKeyRubruc($this->id));
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'index_product_tab';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, rubric', 'required'),
			array('position, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>32),
			array('url', 'length', 'max'=>255),
			array('rubric', 'validateRubric', 'message' => 'Неверно указаны Рубрики'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, url, position, rubric, create_time, update_time', 'safe', 'on'=>'search'),
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
			'id'          => 'ID',
			'name'        => 'Название',
			'url'         => 'Url на все категории',
			'position'    => 'Позиция',
			'rubric'      => 'Рубрики',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('position',$this->position);
		$criteria->compare('rubric',$this->rubric,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('position' => CSort::SORT_ASC);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort'     => $sort,
			'pagination' => array(
				'pageSize' => 20
			)
		));
	}

	/**
	 * Возвращает максимально допустимую позицию.
	 * По факту это количество записей в таблице услуг.
	 * Счетчик увеличивается на единицу для случая, когда
	 * мы создаем новую запись.
	 */
	public function getMaxPos()
	{
		return self::count() + 1;
	}
	
	public function validateRubric($value)
	{
		$result = true;

		if (is_string($this->$value) && ! empty($this->$value)) {
			$this->$value = serialize(explode(',', $this->$value));
		} else {
			$result = false;
		}

		return false;
	}


	/**
	 * Возвращает url для разных типов
	 *
	 * @param      $type Строка с названием типа ссылки.
	 * @param null $id Идентификатор
	 *
	 * @return string
	 */
	public static function getLink($type, $id = null)
	{
		switch ($type) {
			case 'ajaxTab':
				$url = '/site/ajaxTabPromo/id/' . $id;
				break;
			default:
				$url = '#';
				break;
		}

		return $url;
	}


	/**
	 * Формирует список вкладок для промоблока.
	 *
	 * @param int  $limit Ограничение на кол-во возвращаемых табочек.
	 * @param bool $no_cache Флаг, при выставлении которого, кеш не используется.
	 *
	 * @return array Последовательный массив данных по вкладкам.
	 */
	public static function getActiveTabs($limit = 8, $no_cache = false)
	{
		$result = Yii::app()->cache->get(self::getCacheKeyTab());

		if ($no_cache === true) {
			$result = false;
		}

		if ($result === false) {

			$result = array();

			$res = Yii::app()->db->createCommand()
				->select('name, id')
				->from(IndexProductTab::model()->tableName())
				->order('position ASC')
				->limit((int)$limit)
				->queryAll();

			if ($res) {
				foreach ($res as $item) {
					$result[] = array(
						'id'   => $item[ 'id' ],
						'name' => $item[ 'name' ],
						'url'  => IndexProductTab::getLink('ajaxTab', $item[ 'id' ])
					);
				}
			}

			Yii::app()->cache->set(self::getCacheKeyTab(), $result, Cache::DURATION_MAIN_PAGE);
		}


		return $result;
	}


	/**
	 * Возвращает список рубрик (категорий товаров), привязанных к
	 * указанной в параметре вкладке.
	 *
	 * @param integer $tab_id идентификатор вкладки, для которой получаем список
	 *                       категорий товаров.
	 * @param bool $no_cache Флаг, при выставлении которого, кеш не используется.
	 *
	 * @return array|bool|mixed
	 */
	public static function getActiveRubrics($tab_id, $no_cache = false)
	{
		Yii::import('application.modules.catalog.models.Category');

		$tabId = (int)$tab_id;

		if (!IndexProductTab::model()->exists(
			'id = :id',
			array(':id' => $tabId)
		)) {
			throw new CHttpException(404);
		}

		$result = Yii::app()->cache->get(self::getCacheKeyRubruc($tabId));

		if ($no_cache === true) {
			$result = false;
		}

		if ($result === false) {

			$result = array();

			$model = IndexProductTab::model()->findByPk((int)$tabId);

			if ($model) {
				$rubrics = unserialize($model->rubric);
				if (is_array($rubrics) && !empty($rubrics)) {
					foreach($rubrics as $id) {
						$cat = Category::model()->findByPk((int)$id);
						if ($cat) {
							$result[] = array(
								'id'   => $cat->id,
								'name' => $cat->name,
								'url'  => Yii::app()->controller->createUrl(
									'/catalog/category/list',
									array('id' => $cat->id)
								)
							);

							if (count($result) >= 15) {
								break;
							}
						}
					}
				}

			}

			Yii::app()->cache->set(self::getCacheKeyRubruc($tabId), $result, Cache::DURATION_MAIN_PAGE);
		}

		return $result;
	}

    public static function getActiveRubrics2($tab_id, $no_cache = false)
    {
        Yii::import('application.modules.catalog2.models.*');

        $tabId = (int)$tab_id;

        if (!IndexProductTab::model()->exists(
            'id = :id',
            array(':id' => $tabId)
        )) {
            throw new CHttpException(404);
        }

        $result = Yii::app()->cache->get(self::getCacheKeyRubruc($tabId));

        if ($no_cache === true) {
            $result = false;
        }

        if ($result === false) {

            $result = array();

            $model = IndexProductTab::model()->findByPk((int)$tabId);

            if ($model) {
                $rubrics = unserialize($model->rubric);
                if (is_array($rubrics) && !empty($rubrics)) {
                    foreach($rubrics as $id) {
                        $cat = Category::model()->findByPk((int)$id);
                        if ($cat) {
                            $result[] = array(
                                'id'   => $cat->id,
                                'name' => $cat->name,
                                'url'  => Yii::app()->controller->createUrl(
                                    '/catalog2/category/list',
                                    array('id' => $cat->id)
                                )
                            );

                            if (count($result) >= 15) {
                                break;
                            }
                        }
                    }
                }

            }

            Yii::app()->cache->set(self::getCacheKeyRubruc($tabId), $result, Cache::DURATION_MAIN_PAGE);
        }

        return $result;
    }


	/**
	 * Возваращает ключ для Кеша, в котором хранится выборка вкладок.
	 *
	 * @return string Ключ, по которому в кеш складываются значения.
	 */
	private static function getCacheKeyTab()
	{
		return 'INDEX:PROMO_PRODUCT:TABS';
	}


	/**
	 * Возвращает ключ для Кеша, в котором хранится выборка рубрик для
	 * указанной вкладки $tabId
	 *
	 * @param $tabId Идентификатор вкладки
	 *
	 * @return string Ключ, по которому в кеш складываются значения
	 * @throws CHttpException Вызывает ошибку 400, если $tabId <= 0
	 */
	private static function getCacheKeyRubruc($tabId)
	{
		if (intval($tabId) <= 0) {
			throw new CHttpException(
				400,
				'Параметр $tabId должен быть положительным числом'
			);
		}

		return 'INDEX:PROMO_PRODUCT:RUBRIC:' . $tabId;
	}

}