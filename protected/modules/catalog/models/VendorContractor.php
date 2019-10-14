<?php

/**
 * This is the model class for table "cat_vendor_contractor".
 *
 * The followings are the available columns in table 'cat_vendor_contractor':
 * @property integer $vendor_id
 * @property integer $contractor_id
 */
class VendorContractor extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return VendorContractor the static model class
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
		return 'cat_vendor_contractor';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('vendor_id, contractor_id', 'required'),
			array('vendor_id, contractor_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('vendor_id, contractor_id', 'safe', 'on'=>'search'),
		);
	}

}