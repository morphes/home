<?php

/**
 * This is the model class for table "seo_meta_tag".
 *
 * The followings are the available columns in table 'seo_meta_tag':
 * @property integer $id
 * @property string $url
 * @property integer $url_crc32
 * @property string $page_title
 * @property string $description
 * @property string $keywords
 * @property string $h1
 * @property integer $create_time
 * @property integer $update_time
 */
class SeoMetaTag extends EActiveRecord
{

	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
		$this->onBeforeSave = array($this, 'saveCrcUrl');
	}

	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Вычисляет контролную сумму от указанного url, без учета обрамляющих
	 * пробелов и слешей.
	 */
	public function saveCrcUrl()
	{
		$this->url_crc32 = crc32(trim($this->url,' /'));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SeoMetaTag the static model class
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
		return 'seo_meta_tag';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('url', 'required'),
			array('url', 'unique'),
			array('url_crc32, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('url, page_title, h1', 'length', 'max'=>255),
			array('description, keywords', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, url, url_crc32, page_title, description, keywords, h1, create_time, update_time', 'safe', 'on'=>'search'),
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
			'url'         => 'URL страницы',
			'url_crc32'   => 'Контрольная сумма URL страницы',
			'page_title'  => 'Title',
			'description' => 'Description',
			'keywords'    => 'Keywords',
			'h1'          => 'H1',
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
		$criteria->compare('url',$this->url,true);
		$criteria->compare('url_crc32',$this->url_crc32);
		$criteria->compare('page_title',$this->page_title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('keywords',$this->keywords,true);
		$criteria->compare('h1',$this->h1,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	static public function getLink($type = '', $params = array())
	{
		switch($type)
		{
			case 'create':
				$link = Yii::app()->createUrl('/admin/seoMetaTag/create', $params);
				break;
			case 'update':
				$link = Yii::app()->createUrl('/admin/seoMetaTag/update/', $params);
				break;
			case 'delete':
				$link = Yii::app()->createUrl('/admin/seoMetaTag/delete/', $params);
				break;
			default:
				$link = '#';
		}

		return $link;
	}
}