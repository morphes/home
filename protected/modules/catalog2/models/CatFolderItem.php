<?php

/**
 * This is the model class for table "cat_folder_item".
 *
 * The followings are the available columns in table 'cat_folder_item':
 * @property integer $id
 * @property integer $folder_id
 * @property integer $model_id
 * @property integer $update_time
 * @property integer $create_time
 */
class CatFolderItem extends Catalog2ActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CatFolderItem the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	//Количество элементов на страницу вывода
	const PAGE_SIZE_LIMIT = 150;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cat_folder_item';
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, 'addRecount');
		$this->onAfterDelete = array($this, 'delRecount');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('folder_id, model_id', 'required'),
			array('folder_id, model_id, update_time, create_time', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, folder_id, model_id, update_time, create_time', 'safe', 'on'=>'search'),
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
			'Folder'=>array(self::BELONGS_TO, 'CatFolders', 'folder_id'),
			'Product'=>array(self::BELONGS_TO, 'Product', 'model_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'folder_id' => 'Folder',
			'model_id' => 'Model',
			'update_time' => 'Update Time',
			'create_time' => 'Create Time',
		);
	}

	public function behaviors()
	{
		return array(
			'PositionBehavior' => array(
				'class' => 'application.components.PositionBehavior',
				'whereLimitField' => 'folder_id',
			)
		);
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
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
		$criteria->compare('folder_id',$this->folder_id);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	static public function getItems($id)
	{
		$command = Yii::app()->dbcatalog2->createCommand();

		$command->select('model_id')
			->from(self::tableName())
			->where('folder_id = :id', array(':id' => $id));
		$command->order = 'create_time ASC';


		$arr = $command->queryColumn();

		return $arr;
	}

	/**
	 * Пересчет количества товаров в папке
	 */
	public function addRecount()
	{
		$qt = self::model()->countByAttributes(array('folder_id'=>$this->folder_id));

		if($this->Folder->status == CatFolders::STATUS_HIDDEN )
		{
			CatFolders::model()->updateByPk($this->folder_id, array('count'=> $qt));

		}
		else {
			CatFolders::model()->updateByPk($this->folder_id, array('count'=> $qt,'status'=>CatFolders::STATUS_NOT_EMPTY));
		}

	}


	/**
	 * Пересчет количества товаров в папке при удалении
	 */
	public function DelRecount()
	{
		$qt = self::model()->countByAttributes(array('folder_id'=>$this->folder_id));

		if($qt == 0)
		{
			$folderStatus = CatFolders::STATUS_EMPTY;
		}
		elseif($this->Folder->status == CatFolders::STATUS_HIDDEN){
			$folderStatus = CatFolders::STATUS_HIDDEN;
		}
		else {
			$folderStatus = CatFolders::STATUS_NOT_EMPTY;
		}
		CatFolders::model()->updateByPk($this->folder_id, array('count'=> $qt,'status'=>$folderStatus));
	}


	/**
	 * Возвращает первый итем
	 * из папки
	 * @param $folderId
	 *
	 * @return array|CActiveRecord|mixed|null
	 */
	public static function getFirstModel($folderId)
	{
		$model = self::model()->findByAttributes(array('folder_id'=>$folderId));
		$product = Product::model()->findByPk($model->model_id);
		return $product;

	}

	/**
	 * Возвращает максимальную позицию
	 * элемента в папке
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getMaxPosition($id)
	{
		$command = Yii::app()->dbcatalog2->createCommand();

		$command->select('max(position)')
			->from('cat_folder_item')
			->where('folder_id = :id', array(':id' => $id));

		$result = $command->queryScalar();

		return $result;

	}
}