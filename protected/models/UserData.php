<?php

/**
 * This is the model class for table "user_data".
 *
 * The followings are the available columns in table 'user_data':
 * @property integer $user_id
 * @property string $notice_private_message
 * @property string $birthday
 * @property string $length_of_service
 * @property string $gender
 * @property string $link_to_portfolio
 * @property string $hide_phone
 * @property string $skype
 * @property string $icq
 * @property string $site
 * @property string $twitter
 * @property string $vkontakte
 * @property string $odnoklassniki
 * @property string $facebook
 * @property string $ban_comment
 * @property string $contact_face
 * @property string $about
 * @property integer $draft_qt
 * @property integer $average_rating
 */
class UserData extends EActiveRecord
{
	// Список полей, которые должны быть за encode'ны при присваивании значения
	protected $encodedFields = array('skype', 'icq');

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'initSettings');
	}

	public function initSettings()
	{
		if ($this->getIsNewRecord()) {
			$this->notice_private_message = 1;
			$this->tender_response_notify = 1;
			$this->tender_notify = 1;
		}
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return UserData the static model class
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
		return 'user_data';
	}

	public function behaviors(){
                return array(
                        'CSafeContentBehavor' => array( 
                                'class' => 'application.components.CSafeContentBehavior',
                                'attributes' => $this->encodedFields,
                        ),
                );
        }
	
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, draft_qt', 'numerical', 'integerOnly'=>true),
			array('portal_notice, tender_notify, tender_response_notify, idea_comment_notify', 'numerical', 'integerOnly'=>true, 'min'=>0, 'max'=>1),
			array('notice_private_message, birthday, length_of_service, gender, link_to_portfolio, skype, icq, site, twitter, vkontakte, odnoklassniki, facebook, ban_comment, contact_face', 'length', 'max'=>45),
			array('mail_sign', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, notice_private_message, birthday, length_of_service, gender, link_to_portfolio, hide_phone, skype, icq, site, twitter, vkontakte, odnoklassniki, facebook, ban_comment, contact_face, about', 'safe', 'on'=>'search'),
			array('hide_phone', 'boolean'),
			array('contact_face', 'required',
				'on' => 'reg-'.User::ROLE_SPEC_JUR),
			array('expert_desc', 'length', 'max'=>255),
			
			array('twitter, facebook, vkontakte', 'url', 'allowEmpty' => true),
			array('site', 'url', 'allowEmpty' => true,
				'message' => 'Неправильный URL сайта',
				'pattern'=>'/^(http(s?)\:\/\/)?(([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)(\.[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)+(\/[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)*(\/?(\?([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1}(&[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1})*){0,1})?))$/i',
			),

			array('gender, ban_comment, notice_private_message', 
				'length',
				'max' => 1
			),
			array('birthday', 
				'match',
				'pattern' => '#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#'
			),
			array('about', 'length', 'max' => 2000),

			array('price_list', 'file', 'types' => 'xls, xlsx, doc, docx, zip, pdf, rtf', 'maxSize' => '3145728', 'tooLarge' => 'Файл может быть не более 3Мб', 'wrongType' => 'Файлы могут быть только xls, xlsx, doc, docx, pdf, rtf, zip',
				'on' => 'savePriceList'),
			array('price_list',  'numerical', 'integerOnly' => true,
				'on' => 'saveIdPrice'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id'		=> 'ID пользователя',
			'notice_private_message'=> 'Уведомление о новых личных сообщениях',
			'tender_notify'		=> 'Уведомление о подходящих заказах',
			'tender_response_notify'=> 'Уведомление об откликах на заказ',
			'birthday'		=> 'День рождения',
			'length_of_service'	=> 'Стаж работы',
			'gender'		=> 'Пол',
			'link_to_portfolio'	=> 'Ссылка на портфолио',
			'hide_phone'		=> 'Скрыть телефон',
			'skype'			=> 'Skype',
			'icq'			=> 'ICQ',
			'site'			=> 'Сайт',
			'twitter'		=> 'Twitter',
			'vkontakte'		=> 'ВКонтакте',
			'odnoklassniki'		=> 'Одноклассники',
			'facebook'		=> 'Facebook',
			'ban_comment'		=> 'Бан на комментирование',
			'contact_face'		=> 'Контактное лицо',
			'idea_comment_notify'	=> 'Уведомление о новых комментариях в проектах',
			'mail_sign'		=> 'Подпись к письму инвайта',
                        'draft_qt'              => 'Кол-во проектов в черновиках',
			'hide_phone'		=> 'Скрыть телефон',
			'portal_notice'		=> 'Уведомление о новых возможностях портала',
			'expert_desc'		=> 'Краткое описание',
			'about' => array('default' => 'О себе', User::ROLE_SPEC_JUR => 'О компании'),
		);
	}

	/**
	 * Расширяет метод получения имени свойства модели.
	 * Появилась возможность в качестве имени свойства указать массив key-value
	 * array( 'default' => 'Имя по-умолчанию', <имя роли> = "Название поля для конкретной роли")
	 *
	 * @param string $attribute Название атрибута, для которого надо взять имя
	 * @return string Название поля
	 */
	public function getAttributeLabel($attribute)
	{
		$labels = $this->attributeLabels();

		if (isset($labels[$attribute]) && is_array($labels[$attribute])) {
			$role = Yii::app()->user->role;
			if (array_key_exists($role, $labels[$attribute])) {
				return $labels[$attribute][$role];
			} else {
				return $labels[$attribute]['default'];
			}
		}
		else
			return parent::getAttributeLabel($attribute);
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('notice_private_message',$this->notice_private_message,true);
		$criteria->compare('birthday',$this->birthday,true);
		$criteria->compare('length_of_service',$this->length_of_service,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('link_to_portfolio',$this->link_to_portfolio,true);
		$criteria->compare('hide_phone',$this->hide_phone);
		$criteria->compare('skype',$this->skype,true);
		$criteria->compare('icq',$this->icq,true);
		$criteria->compare('site',$this->site,true);
		$criteria->compare('twitter',$this->twitter,true);
		$criteria->compare('vkontakte',$this->vkontakte,true);
		$criteria->compare('odnoklassniki',$this->odnoklassniki,true);
		$criteria->compare('facebook',$this->facebook,true);
		$criteria->compare('ban_comment',$this->ban_comment,true);
		$criteria->compare('contact_face',$this->contact_face,true);
                $criteria->compare('draft_qt',$this->draft_qt,true);
		$criteria->compare('about', $this->about, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * По переданному ID из uploadedFile возвращает путь для скачивания
	 * прайс листов услуг пользователя
	 * @static
	 * @param $uploaded_file_id
	 * @return string
	 */
	public static function getUrlDownloadPrice($uploaded_file_id)
	{
		return '/download/pricelist/'.$uploaded_file_id;
	}
}