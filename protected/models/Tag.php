<?php

/**
 * This is the model class for table "tag".
 *
 * The followings are the available columns in table 'tag':
 * @property integer $id
 * @property string $name
 */
class Tag extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tag the static model class
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
		return 'tag';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'name' => 'Name',
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

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Разбирает переданную строку тегов на отдельные теги и сохраняет
	 * при необходимости в базе. Возвращает массив идентификаторов тегов из
	 * таблицы tag для всех тегов из переданной строки.
	 * @static
	 * @param $tagString Строка с тегами
	 * @return array|bool Массив идентификаторов из таблицы tag или false
	 */
	public static function saveTagsFromString($tagString)
	{
		$result = array();

		try {
			// Разбираем переданную строку на слова
			$tagString = @preg_replace("/[^- a-zА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя0-9,]+/iu","",$tagString);
			$tagArray = @preg_split('~\s*,\s*~', $tagString);
			foreach ($tagArray as $key => $tag) {
				$tagArray[$key] = trim($tag);
			}

			// Оставляем только НЕ повторяющиеся теги.
			$tagArray = array_unique($tagArray);
			if (!$tagArray)
				return false;

			// Проверяем наличие каждого тега в базе данныз Тегов.
			// Если его еще нет в базе добавляем, если есть - просто берем объект.
			foreach ($tagArray as $tag) {
				$tag = trim($tag);
				if (empty($tag))
					continue;

				$tagObj = Tag::model()->findByAttributes(array('name' => $tag));
				if (is_null($tagObj)) {
					$tagObj = new Tag();
					$tagObj->name = $tag;
					$tagObj->save();
				}
				$result[] = $tagObj->id;

			}
		} catch(Exception $e ){
			return false;
		}

		return $result;
	}
}