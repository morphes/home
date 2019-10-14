<?php

/**
 * This is the model class for table "vacancies".
 *
 * The followings are the available columns in table 'vacancies':
 * @property integer $id
 * @property integer $position
 * @property string $key
 * @property string $name
 * @property string $anons
 * @property string $text
 * @property string $wage
 * @property integer $status
 * @property integer $create_time
 */
class Vacancies extends EActiveRecord
{
	const STATUS_ACTIVE = 1; // Активен
	const STATUS_INACTIVE = 2; // Неактивен

	public $actions;
	private $oldPosition = null;
	
	public static $nameStatus = array(
		self::STATUS_ACTIVE => 'Активен',
		self::STATUS_INACTIVE => 'Неактивен'
	);
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Vacancies the static model class
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
		return 'vacancy';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, key, position, text, status', 'required'),
			array('position, status, create_time', 'numerical', 'integerOnly'=>true),
			array('key, name', 'length', 'max'=>255),
			array('anons', 'length', 'max'=>500),
			array('text', 'length', 'max'=>5000),
			array('wage', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, position, key, name, anons, text, wage, status, create_time', 'safe', 'on'=>'search'),
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
			'id'		=> 'ID',
			'position'	=> '№',
			'key'		=> 'Ключ hash тега',
			'name'		=> 'Название',
			'anons'		=> 'Анонс',
			'text'		=> 'Текст',
			'wage'		=> 'Зарплата',
			'status'	=> 'Статус',
			'update_time'	=> 'Дата обновления',
			'create_time'	=> 'Дата создания',
			'actions'	=> 'Действие',
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
		$criteria->compare('position',$this->position);
		$criteria->compare('key',$this->key,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('anons',$this->anons,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('wage',$this->wage,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time);
		
		$criteria->order = 'position ASC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize' => 1000
			)
		));
	}
	
	/**
         * Execute before save
         * set update_time
         * @return boolean
         */
        public function beforeSave()
        {
                if (parent::beforeSave()) {

                        if ($this->isNewRecord)
                                $this->create_time = $this->update_time = time();
                        else {
                                $this->update_time = time();
				
				// Перед сохранением получаем старую позицию элемента
				$res = Yii::app()->db->createCommand("SELECT position FROM {$this->tableName()} WHERE id = {$this->id}")->queryRow();
				if ($res)
					$this->oldPosition = $res['position'];
                        }

                        return true;
                }
                else
                        return false;
        }
	
	public function afterSave()
	{
		if ( ! $this->isNewRecord)
			$this->setPosition($this->position);
		else
			$this->renumerateAll();
		
		return parent::afterSave();	
	}
	
	public function afterDelete() {
		parent::afterDelete();
		
		$this->renumerateAll();
	}

	/**
	 * Перенумероваывает все записи в таблице Вакансии.
	 */
	public function renumerateAll()
	{
		Yii::app()->db->createCommand("
			SET @v1 := 0;
			UPDATE
				{$this->tableName()}
			SET	
				position = (@v1:=(@v1+1))
			ORDER BY
				position ASC, update_time DESC
		")->execute();
	}
	
	/**
	 * Метод вызывается после сохранения модели.
	 * Проводит корректировку сохраненной позиции, и перенумеровывает
	 * позиции других элементов, на которые влияет текущий.
	 * 
	 * Использует в работе $this->oldPosition, который инициализируется
	 * перед сохранением.
	 * 
	 * @param integer $new_position Позиция, которую должен занимать элемент
	 */
	public function setPosition($new_position)
	{
		$new_position = (int)$new_position;
		
		$totalCount = $this->count();
		
		if ($new_position < 1)
			$new_position = 1;
		if ($new_position > $totalCount)
			$new_position = $totalCount;
		
		
		$resUpdate = null;
		
		if ($this->position != $new_position) {
			$this->position = $new_position;
			$resUpdate = Yii::app()->db->createCommand()->update($this->tableName(), array('position' => $new_position), 'id = '.$this->id);
		}
		
		// Находим элемент, на позицию которого мы хотим встать.
		$resSelect = Yii::app()->db->createCommand()
			->select('id')
			->from($this->tableName())
			->where('position = :position AND id <> :id', array(':position' => $new_position, ':id' => $this->id))
			->queryRow();
		
		if ($resSelect) {
			// Если есть элемент вместо которого мы хотим встать,
			// то обновляем позиции выше или ниже стоящих элементов
			if ($new_position < $this->oldPosition)
				Yii::app()->db->createCommand("
					UPDATE
						{$this->tableName()}
					SET	
						position = position + 1
					WHERE
						position >= {$new_position}
						  AND
						position < {$this->oldPosition}
						  AND
						id <> {$this->id}
				")->execute();
			else
				Yii::app()->db->createCommand("
					UPDATE
						{$this->tableName()}
					SET
						position = position - 1
					WHERE
						position > {$this->oldPosition}
						  AND
						position <= {$new_position}
						  AND
						id <> {$this->id}
				")->execute();
		}
	}	
	
}