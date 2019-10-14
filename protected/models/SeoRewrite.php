<?php

/**
 * This is the model class for table "seo_rewrite".
 *
 * The followings are the available columns in table 'seo_rewrite':
 * @property string $seo_md5
 * @property string $seo_url
 * @property integer $status
 * @property string $path
 * @property string $subdomain
 * @property string $desc
 * @property integer $normal_md5
 * @property string $param
 * @property integer $create_time
 * @property integer $update_time
 * @property string $normal_url
 */
class SeoRewrite extends CActiveRecord
{
	const STATUS_NO = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DISABLED = 2;
	const STATUS_ERROR = 3;

	public static $statusNames = array(
		self::STATUS_ACTIVE => 'Активен',
		self::STATUS_DISABLED => 'Отключен',
		self::STATUS_ERROR => 'Ошибка',
		self::STATUS_NO => 'Отсутствует',
	);

	public function init()
	{
		parent::init();
		$this->attachBehavior('subdomains', new SubdomainBehavior());
		$this->onBeforeSave = array($this, 'setDate');
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
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SeoRewrite the static model class
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
		return 'seo_rewrite';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('seo_url, status', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('seo_url, subdomain', 'length', 'max'=>255),
			array('param', 'length', 'max'=>3000),
			array('desc', 'length', 'max'=>512),
			array('status', 'in', 'range'=>array(self::STATUS_ACTIVE, self::STATUS_DISABLED, self::STATUS_ERROR)),
			array('normal_url', 'safe'),
			array('seo_md5', 'unique'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('seo_url, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'seo_url' => 'Seo Url',
			'status' => 'Статус',
			'normal_url' => 'Исходный Url',
			'normal_md5' => 'md5 адреса',
			'param' => 'Параметры',
			'desc' => 'Описание rewrite',
			'subdomain' => 'Поддомен',
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

		$criteria->compare('seo_url',$this->seo_url,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>20,
			),
		));
	}

	static public function getLink($type = '', $params = array())
	{
		switch($type)
		{
			case 'create':
				$link = '/admin/seoRewrite/create?'.http_build_query($params);
				break;
			case 'update':
				$link = '/admin/seoRewrite/update?'.http_build_query($params);
				break;
			case 'delete':
				$link = '/admin/seoRewrite/delete?'.http_build_query($params);
				break;
			default:
				$link = '#';
		}

		return $link;
	}
}