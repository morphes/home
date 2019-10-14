<?php

/**
 * This is the model class for table "ip_ban".
 *
 * The followings are the available columns in table 'ip_ban':
 * @property integer $ip
 * @property integer $expire
 * @property integer $create_time
 */
class IpBan extends EActiveRecord
{

        private $_ip = null;

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'updateData');
        }

        public function setDate()
        {
                if ($this->getIsNewRecord())
                        $this->create_time = time();
        }

        public function __get($name)
        {
                $getter = 'get' . $name;
                if (method_exists($this, $getter))
                        return $this->$getter();
                return parent::__get($name);
        }

        public function __set($name, $value)
        {
                $setter = 'set' . $name;
                if (method_exists($this, $setter))
                        return $this->$setter($value);
                return parent::__set($name, $value);
        }

        public function getIp()
        {
                if (is_null($this->_ip))
                        $this->_ip = long2ip(parent::__get('ip'));
                return $this->_ip;
        }

        public function setIp($value)
        {
                return $this->_ip = $value;
        }

        public function updateData()
        {
                parent::__set('ip', ip2long($this->ip));
                $this->expire = $this->expire * 3600 + time();
                return true;
        }

        /**
         * Returns the static model of the specified AR class.
         * @return IpBan the static model class
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
                return 'ip_ban';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('ip', 'required'),
                    array('expire, create_time', 'numerical', 'integerOnly' => true),
                    array('ip', 'length', 'max' => 15),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('ip, expire, create_time', 'safe', 'on' => 'search'),
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
                    'ip' => 'Ip',
                    'expire' => 'Забанен до',
                    'create_time' => 'Дата бана',
                    'bansize' => 'Забанить на(в часах)'
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

                $criteria->compare('ip', $this->ip);
                $criteria->compare('expire', $this->expire);
                $criteria->compare('create_time', $this->create_time);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

        /**
         * Команда установки бана
         * Если бан был установлен ранее - обновляет его значение
         * @param string $ip
         * @param integer $time сек
         * @return boolean 
         */
        static public function createBan($ip, $time = 600)
        {
                $ban = self::model()->findByPk(ip2long($ip));
                if(!$ban)
                        return Yii::app()->db->createCommand()->insert(self::model()->tableName(), array('ip' => ip2long($ip), 'expire' => $time + time(), 'create_time' => time()));

                return Yii::app()->db->createCommand()->update(self::model()->tableName(), array('expire' => $time + time(), 'create_time' => time()), 'ip=:ip', array(':ip'=>ip2long($ip)));
        }

}