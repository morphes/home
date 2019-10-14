<?php

/**
 * @brief This is the model class for table "system_message".
 *
 * @details The followings are the available columns in table 'system_message':
 * @param integer $id
 * @param string $model
 * @param integer $model_id
 * @param integer $author_id
 * @param string $message
 * @param integer $create_time
 *
 * The followings are the available model relations:
 * @param User $author
 */
class SystemMessage extends EActiveRecord
{

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
        }

        /**
         * Update create_time and update_time in object
         */
        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = time();
        }

        /**
         * Returns the static model of the specified AR class.
         * @return SystemMessage the static model class
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
                return 'system_message';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('model, model_id, author_id, message', 'required'),
                    array('model_id, author_id, create_time', 'numerical', 'integerOnly' => true),
                    array('model', 'length', 'max' => 255),
                    array('message', 'length', 'max' => 2000),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, model, model_id, author_id, message, create_time', 'safe', 'on' => 'search'),
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
                    'author' => array(self::BELONGS_TO, 'User', 'author_id'),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
                    'id' => 'ID',
                    'model' => 'Model',
                    'model_id' => 'Model',
                    'author_id' => 'Author',
                    'message' => 'Внутренний комментарий',
                    'create_time' => 'Create Time',
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
                $criteria->compare('model', $this->model, true);
                $criteria->compare('model_id', $this->model_id);
                $criteria->compare('author_id', $this->author_id);
                $criteria->compare('message', $this->message, true);
                $criteria->compare('create_time', $this->create_time);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

        /**
         * Создает новое системное сообщение к объекту модели.
         * (Внутренний комментарий к объекту системы)
         * @param object $model - комментируемый объект модели
         * @param string $message - комментарий
         * @return boolean - результат операции
         */
        public static function setMessage($model, $message)
        {
                if (is_object($model) && isset($model->id)) {
                        $sys_msg = new self();
                        $sys_msg->model = get_class($model);
                        $sys_msg->model_id = $model->id;
                        $sys_msg->author_id = Yii::app()->user->id;
                        $sys_msg->message = $message;
                        if ($sys_msg->save()) {
                                return true;
                        }
                }
                return false;
        }

        /**
         * Возвращает массив оъектов типа SystemMessage, относящихся к указанной модели.
         * (Возвращает список внутренних комментариев объекта системы)
         * @param object $model - объект, для которого запрашиваются все комментарии
         * @return array 
         */
        public static function getMessages($model)
        {
                if (is_object($model) && isset($model->id)) {

			return new CActiveDataProvider('SystemMessage', array(
                            'criteria'=>array(
                                'condition'=>'model = :model AND model_id = :model_id',
                                'params'=>array(':model'=>get_class($model), ':model_id' => $model->id),
                                'order'=>'create_time DESC',
                            ),
                            'pagination'=>array(
                                'pageSize'=>10,
                                
                            ),
                        ));
                }
                return false;
        }

}