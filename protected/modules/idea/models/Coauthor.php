<?php

/**
 * This is the model class for table "coauthor".
 *
 * The followings are the available columns in table 'coauthor':
 * @property integer $id
 * @property integer $user_id
 * @property integer $idea_id
 * @property integer $idea_type_id
 * @property string $name
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Coauthor extends EActiveRecord
{
	
	// Список полей, которые должны быть за encode'ны при присваивании значения
	protected $encodedFields = array('name', 'specialization', 'url');
	protected static $purifier = null;
	
        public function behaviors(){
                return array(
                        'CSafeContentBehavor' => array( 
                                'class' => 'application.components.CSafeContentBehavior',
                                'attributes' => $this->encodedFields,
                        ),
                );
        }

        /**
         * Returns the static model of the specified AR class.
         * @return Coauthor the static model class
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
                return 'coauthor';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('idea_id, idea_type_id', 'required'),
                    array('user_id, idea_id, idea_type_id', 'numerical', 'integerOnly' => true),
                    array('name, url', 'length', 'max' => 45),
		    array('specialization', 'length', 'max' => 45, 'tooLong' => 'Специализация слишком длинная(максимум 45 символов)'),
		    array('url', 'url', 'message' => 'Некорректный адрес', 'allowEmpty' => true, 
			'pattern'=>'/^(http:\/\/|https:\/\/){0,1}(([A-ZА-Яабвгдеёжзиклмнопрстуфхцчшщъыьэюя0-9][-A-ZА-Яабвгдеёжзиклмнопрстуфхцчшщъыьэюя0-9_]*)(\.[A-ZА-Яабвгдеёжзиклмнопрстуфхцчшщъыьэюя0-9][-A-ZА-Яабвгдеёжзиклмнопрстуфхцчшщъыьэюя0-9_]*)+)/i',
			'on' => 'final'),
                    array('user_id+idea_id+idea_type_id', 'uniqueMultiColumnValidator', 'on' => 'final', 'message' => 'Соавтор не может повторяться.'),
                    array('name', 'required', 'on' => 'final', 'message' => 'Имя не может быть пустым'),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, user_id, idea_id, idea_type_id, name, specialization, url', 'safe', 'on' => 'search'),
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
                    'user' => array(self::BELONGS_TO, 'User', 'user_id'),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
                    'id' => 'ID',
                    'user_id' => 'Соавтор',
                    'idea_id' => 'Idea',
                    'idea_type_id' => 'Idea Type',
                    'name' => 'Name',
                    'specialization' => 'Специализация',
                    'url' => 'Url',
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

                $criteria = new CDbCriteria;

                $criteria->compare('id', $this->id);
                $criteria->compare('user_id', $this->user_id);
                $criteria->compare('idea_id', $this->idea_id);
                $criteria->compare('idea_type_id', $this->idea_type_id);
                $criteria->compare('name', $this->name, true);
                $criteria->compare('specialization', $this->specialization, true);
                $criteria->compare('url', $this->url, true);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

        /**
         * Create new row for set coauthor info later (by ajax request)
         * @param int $ideaId
         * @return Coauthor 
         * @author Alexey Shvedov
         */
        static public function createRow($ideaId, $ideaTypeId)
        {
		if (!array_key_exists($ideaTypeId, Config::$ideaTypes))
			Yii::app()->end();

                $coauthor = new Coauthor();
                $coauthor->idea_type_id = $ideaTypeId;
                $coauthor->idea_id = $ideaId;

                if (!$coauthor->save()) {
                        Yii::app()->end();
                }

                return $coauthor;
        }

        /**
         * Save coauthors info, obtained by POST
         * @return boolean
         * @author Alexey Shvedov
         */
        static public function SaveCoauthors($ideaId, $ideaTypeId)
        {
                $errors = array();

                if (!empty($_POST['Coauthor'])) {
                        $coauthors = self::model()->findAllByAttributes(array('idea_id' => $ideaId, 'idea_type_id' => $ideaTypeId));

                        foreach ($coauthors as $coauthor) {
                                if (isset($_POST['Coauthor'][$coauthor->id]))
                                        $coauthor->attributes = $_POST['Coauthor'][$coauthor->id];

                                $coauthor->setScenario('final');

                                if (!$coauthor->save()) {
                                        $errors[$coauthor->id] = $coauthor->getErrors();
                                        $coauthor->setScenario('update');
                                        $coauthor->save();
                                }
                        }
                }

                if (!empty($errors)) {
                        return $errors;
                }
                return false;
        }

        /**
         * Получает ссылку на профиль прикрученного соавтора.
         * 
         * @return string URL
         */
        public function getLinkProfile()
        {
                $model = User::model()->findByPk($this->user_id);
                if ($model) {
                        $url = $model->getLinkProfile();
                } else {
                        $url = '#';
                }

                return $url;
        }

        static public function coauthorFormatter($coauthors = array())
        {
                $result = '';

                foreach ($coauthors as $coauthor) {

                        if (!empty($coauthor->user_id))
                                $result.= CHtml::link($coauthor->user->login, Yii::app()->createUrl('/member/profile/user/', array('id' => $coauthor->user_id))) . "<br />";
                        else
                                $result.= CHtml::link($coauthor->name, $coauthor->url) . "<br />";
                }
                return $result;
        }
}