<?php

/**
 * This is the model class for table "tender_response".
 *
 * The followings are the available columns in table 'tender_response':
 * @property integer $id
 * @property integer $tender_id
 * @property integer $author_id
 * @property integer $status
 * @property integer $create_time
 * @property double $cost
 * @property string $content
 */
class TenderResponse extends EActiveRecord
{
	const STATUS_ACTIVE = 1;
	private $isNew = false; // Флаг того, что запись была новая
	private $_tender = null;
	
	protected $encodedFields = array('cost', 'content');

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, 'recountResponse');
		$this->onAfterDelete = array($this, 'recountResponse');
		$this->onAfterSave = array($this, 'sendNotify');
	}
	
	public function setDate()
	{
		if ($this->isNewRecord) {
                        $this->create_time = time();
			$this->isNew = true;
		}
	}
	
	public function recountResponse()
	{
		$count = self::model()->countByAttributes(array('tender_id'=>$this->tender_id));
		$count = Yii::app()->db->createCommand()->update('tender', array('response_count'=>$count), 'id=:tid', array(':tid'=>$this->tender_id));
		if ($count)
			Yii::app()->gearman->appendJob('sphinx:tender', $this->tender_id);
	}
	
	/**
	 * Отправка уведомления автору тендера по почте, при добавлении отклика
	 * @return boolean 
	 */
	public function sendNotify()
	{
		if ($this->isNew) {

			$tender = $this->getTender();
			$respAuthor = $this->getUser();
			if (empty($tender->author_id)) { // GUEST
				Yii::app()->mail->create('tenderResposeForGuest')
					->to( $tender->getAuthorEmail() )
					->priority(EmailComponent::PRT_LOW)
					->params(array(
					'user_name' => $tender->getAuthorName(),
					'tender_name' => CHtml::link( $tender->name, Yii::app()->homeUrl.$tender->getLink() ),
					'view_link' => CHtml::link('Просмотреть отклик', Yii::app()->homeUrl.$tender->getAccessLink($this->getHash())),
					'spec_name' => $respAuthor->name,
					'sign_A'	=> Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
				))
				->send();

			} else {
				$user = $tender->getUser();
				if ( !$user->data->tender_response_notify )
					return false;

				Yii::app()->mail->create('tenderResposeForAuthor')
					->to( $tender->getAuthorEmail() )
					->priority(EmailComponent::PRT_LOW)
					->params(array(
					'user_name' => $tender->getAuthorName(),
					'tender_name' => CHtml::link( $tender->name, Yii::app()->homeUrl.$tender->getLink() ),
					'view_link' => CHtml::link('Просмотреть отклик', Yii::app()->homeUrl.$tender->getAccessLink($this->getHash())),
					'spec_name' => $respAuthor->name,
					'close_link' => CHtml::link('закрыть заказ', Yii::app()->homeUrl.$tender->getCloseLink()),
					'sign_A'	=> Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
				))
				->send();

			}
		}
	}
	
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
	 * @return TenderResponse the static model class
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
		return 'tender_response';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('tender_id, author_id, content', 'required'),
			array('tender_id, author_id, status', 'numerical', 'integerOnly'=>true),
			array('cost', 'length', 'max'=>15),
			array('content', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, tender_id, author_id, status, create_time, cost, content', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'tender_id' => 'ID Заказа',
			'author_id' => 'Author',
			'status' => 'Status',
			'create_time' => 'Create Time',
			'content' => 'Описание',
		);
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return User::model()->findByPk($this->author_id);
	}
	
	/**
	 * Тендер, к которому привязан отклик
	 * @return Tender 
	 */
	public function getTender()
	{
		if (is_null($this->_tender)) {
			$this->_tender = Tender::model()->findByPk($this->tender_id);
		}
		return $this->_tender;
	}

	/**
	 * Ссылка на тендер с якорем на отклик
	 */
	public function getLink()
	{
		return '/tenders/'.$this->tender_id.'#r_'.$this->id;
	}

	public function getHash()
	{
		return 'r_'.$this->id;
	}
	
}