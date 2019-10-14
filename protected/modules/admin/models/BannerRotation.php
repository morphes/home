<?php

/**
 * Модель таблицы "banner_rotation".
 *
 * The followings are the available columns in table 'banner_rotation':
 * @property integer $id
 * @property integer $section_id
 * @property integer $geo_id
 * @property integer $city_id
 * @property integer $region_id
 * @property integer $type_id
 * @property integer $country_id
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $item_id
 * @property integer $status
 * @property integer $item_section_id
 * @property integer $tariff_id
 * @property integer $file_id
 * @property integer $swf_file_id
 *
 * @author Roman Kuzakov
 * @version $Id$
 * @since 3.1
 */
class BannerRotation extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return BannerRotation the static model class
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
		return 'banner_rotation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('section_id, status, item_section_id, geo_id, type_id, start_time, end_time, item_id, tariff_id', 'required'),
			array('section_id, status, item_section_id, city_id, region_id, geo_id, country_id, type_id, start_time, end_time, item_id, tariff_id, file_id, swf_file_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, section_id, status, item_section_id, city_id, region_id, geo_id, country_id, type_id, start_time, end_time, item_id, tariff_id, file_id, swf_file_id', 'safe', 'on'=>'search'),
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
			'itemSection'=>array(self::BELONGS_TO, 'BannerItemSection', array('item_id'=>'item_id', 'section_id'=>'section_id')),
			'swf' => array(self::BELONGS_TO, 'UploadedFile', 'swf_file_id'),
			'image' => array(self::BELONGS_TO, 'UploadedFile', 'file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'section_id' => 'Section',
			'city_id' => 'City',
			'region_id' => 'Region',
			'type_id' => 'Type id',
			'country_id' => 'Country',
			'geo_id' => 'Geo Id',
			'start_time' => 'Start Time',
			'end_time' => 'End Time',
			'item_id' => 'Item',
			'tariff_id' => 'Tariff',
			'file_id' => 'Image File',
			'swf_file_id' => 'Swf File',
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
		$criteria->compare('section_id',$this->section_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('region_id',$this->region_id);
		$criteria->compare('country_id',$this->country_id);
		$criteria->compare('start_time',$this->start_time);
		$criteria->compare('end_time',$this->end_time);
		$criteria->compare('item_id',$this->item_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}