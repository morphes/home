<?php

/**
 * This is the model class for table "index_spec_block".
 *
 * The followings are the available columns in table 'index_spec_block':
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property integer $position
 * @property integer $create_time
 * @property integer $update_time
 */
class IndexSpecBlock extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return IndexSpecBlock the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

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
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'index_spec_block';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('position, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name, url', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, url, position, create_time, update_time', 'safe', 'on'=>'search'),
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
			'url'         => 'Ссылка',
			'position'    => 'Позиция',
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
		return (int)self::count() + 1;
	}


	/**
	 * Возвращает список ссылок (блоков) для специалистов на главной
	 * странице и связанные с ними фотографии.
	 *
	 * @param integer $clearCache Флаг очищающий кеш
	 */
	static public function getSpecBlocks($clearCache = false)
	{

		$result = Yii::app()->cache->get(IndexSpecPhoto::getCacheKeySpec());

		if ($clearCache == true) {
			$result = false;
		}

		if (!$result) {

			// Шаг 1. Получаем список ссылок-блоков.
			$blocks = IndexSpecBlock::model()->findAll(array(
				'order' => 'position ASC',
				'limit' => 3
			));

			foreach ($blocks as $block) {
				$tmp = array(
					'block_name' => $block->name,
					'block_url'  => $block->url,
					'photos'     => array()
				);

				// Шаг 2. Для каждого блока получаем активные фоточки.

				$sql = 'SELECT photo_id '
					. ' FROM index_spec_photo_block'
					. ' INNER JOIN index_spec_photo'
					. ' 	ON index_spec_photo.id = index_spec_photo_block.photo_id'
					. ' WHERE block_id = :bid AND index_spec_photo.status = :st';

				$photoIds = Yii::app()->db
					->createCommand($sql)
					->bindValues(array(
						':bid' => $block->id,
						':st'  => IndexSpecPhoto::STATUS_ACTIVE
					))
					->queryColumn();

				if ($photoIds) {

					shuffle($photoIds);

					/* Шаг 3. Для каждой фоточки собираем данные о
						картинке и ссылке.
					*/
					$index = 1;
					foreach($photoIds as $id) {

						$photo = IndexSpecPhoto::model()->findByPk($id);

						if (!$photo) {
							continue;
						}

						$tmp['photos'][] = array(
							'user_url' => $photo->user->getLinkProfile(),
							'user_img' => $photo->getImageFullPath()
						);

						if ($index++ >= 4) {
							break;
						}
					}
				}

				$result[] = $tmp;
			}

			Yii::app()->cache->set(IndexSpecPhoto::getCacheKeySpec(), $result, 600);
		}

		return $result;
	}
}