<?php

/**
 * This is the model class for table "tender_file".
 *
 * The followings are the available columns in table 'tender_file':
 * @property integer $tender_id
 * @property integer $file_id
 */
class TenderFile extends EActiveRecord
{
	protected $encodedFields = array('desc');
	
	public function behaviors()
        {
                return array(
                    'CSafeContentBehavor' => array(
                        'class' => 'application.components.CSafeContentBehavior',
                        'attributes' => $this->encodedFields,
                    ),
                );
        }
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TenderFile the static model class
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
		return 'tender_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('tender_id, file_id', 'required'),
			array('tender_id, file_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('tender_id, file_id', 'safe', 'on'=>'search'),
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
			'tender_id' => 'Tender',
			'file_id' => 'Uploaded File',
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

		$criteria->compare('tender_id',$this->tender_id);
		$criteria->compare('file_id',$this->file_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Загрузка файлов тендера
	 * @static
	 * @param $tender Tender or tenderId
	 * @param $filename
	 * @param string $desc
	 * @param string $saveType
	 * @return array(TenderFile, UploadedFile)
	 */
	public static function saveFile($tender, $filename, $desc='', $saveType = 'fileApi', $isAdmin = false)
        {
		if ( !$tender instanceof Tender)
                	$tender = Tender::model()->findByPk((int)$tender);
                
                if(!$tender || empty($filename) || !($tender->author_id == Yii::app()->user->id || $isAdmin) )
                        return false;
                
                $file = new UploadedFile('document'); 
		
                $file->file = CUploadedFile::getInstance($file,$filename);

                if(!$file->file)
                        return false;

                /**
                 * Для fileApi
                 */
                if(empty($desc) && isset($_POST['UploadedFile']['desc']) && $saveType == 'fileApi')
                        $desc = $_POST['UploadedFile']['desc'];
		
		$authorId = $tender->author_id;

                $file->author_id = $authorId;
		$file->path = 'tender/'.intval($tender->id / UploadedFile::PATH_SIZE + 1).'/'.$tender->id;
		$file->ext = $file->file->extensionName;
		$file->size = $file->file->size;
                $file->type = UploadedFile::DOCUMENT_TYPE;
		
		$filename = 'tender_'.$tender->id.'_'.mt_rand(100, 999);
		$path = UploadedFile::UPLOAD_PATH .'/'.$file->path;
		
		$cnt = 0;
		while (file_exists($path . '/' . $filename . '.' . $file->ext) && $cnt < 50) {
			$filename .= mt_rand(100, 999);
			$cnt++;
		}
		if ($cnt==50)
			return false;
		
                $file->name = $filename;
                $folder = UploadedFile::UPLOAD_PATH . '/' . $file->path;
		
		if (!file_exists($folder)) 
			mkdir($folder, 0700, true);
                
                if($file->save()){
                        $file->file->saveAs($folder . '/' . $file->name . '.' . $file->ext);
                        
                        $rel = new self();
                        $rel->tender_id = $tender->id;
                        $rel->file_id = $file->id;
			$rel->desc = CHtml::encode($desc);
                        $rel->save();

			return array($rel, $file);
                }
                return false;
        }
}