<?php

/**
 * This is the model class for table "interior_content_tag".
 *
 * The followings are the available columns in table 'interior_content_tag':
 * @property integer $tag_id
 * @property integer $interior_content_id
 */
class InteriorContentTag extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return InteriorContentTag the static model class
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
		return 'interior_content_tag';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('tag_id, interior_content_id', 'required'),
			array('tag_id, interior_content_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('tag_id, interior_content_id', 'safe', 'on'=>'search'),
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
			'tag_id' => 'Tag',
			'interior_content_id' => 'Interior Content',
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

		$criteria->compare('tag_id',$this->tag_id);
		$criteria->compare('interior_content_id',$this->interior_content_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Обновление тэгов из строки для указанного помещения
	 * @param integer $interiorContentId
	 * @param string $tagString 
	 */
	public static function updateTags($interiorContentId, $tagString)
	{
		try {
			$tagString = @preg_replace("/[^- a-zА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя0-9,]+/iu","",$tagString);
			$tagArray = @preg_split('~\s*,\s*~', $tagString);
			foreach ($tagArray as $key => $tag) {
				$tagArray[$key] = trim($tag);
			}
			$tagArray = array_unique($tagArray);
			if (!$tagArray)
				return false;

			$sql = 'insert into interior_content_tag (`tag_id`, `interior_content_id`) values ';
			$values = array();
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
				$values[] = '('.$tagObj->id.','.$interiorContentId.')';

			}

			Yii::app()->db->createCommand()->delete('interior_content_tag', 'interior_content_id = :icid', array(':icid' => $interiorContentId));
			if (!empty($values))
				Yii::app()->db->createCommand($sql . implode(',', $values))->execute();
		} catch(Exception $e ){
			return false;
		}
		return true;
	}
}