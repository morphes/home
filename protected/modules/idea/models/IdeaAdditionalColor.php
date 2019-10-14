<?php

/**
 * This is the model class for table "idea_additional_color".
 *
 * The followings are the available columns in table 'idea_additional_color':
 * @property integer $item_id
 * @property integer $idea_type_id
 * @property integer $color_id
 * @property integer $position
 *
 * The followings are the available model relations:
 * @property ExteriorContent $item
 */
class IdeaAdditionalColor extends EActiveRecord
{

        /**
         * Returns the static model of the specified AR class.
         * @return IdeaAdditionalColor the static model class
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
                return 'idea_additional_color';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('item_id, idea_type_id, position', 'required', 'on' => 'init'),
                    array('item_id+idea_type_id+position', 'uniqueMultiColumnValidator', 'on' => 'init'),
                    array('item_id, idea_type_id, position', 'required', 'on' => 'finished'),
                    array('item_id+idea_type_id+color_id', 'uniqueMultiColumnValidator', 'on' => 'finished', 'message' => 'Повторяющиеся дополнительные цвета'),
                    array('item_id, idea_type_id, position', 'required', 'on' => 'failed'),
                    array('item_id, idea_type_id, color_id', 'numerical', 'integerOnly' => true),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('item_id, idea_type_id, color_id', 'safe', 'on' => 'search'),
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
                    'item_id' => 'Item',
                    'idea_type_id' => 'Idea Type',
                    'color_id' => 'Дополнительный цвет',
                    'position' => 'Position',
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

                $criteria->compare('item_id', $this->item_id);
                $criteria->compare('idea_type_id', $this->idea_type_id);
                $criteria->compare('color_id', $this->color_id);
                $criteria->compare('position', $this->position);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

        /**
         * Создание новой записи для дальнейшего указания цвета (по ajax запросу)
         * @param int $sc_id
         * @param int $pos
         * @return SolutionContentColor 
         */
        static public function ajaxCreateRow($sc_id, $pos)
        {
                $idea_key = array_keys(Config::$ideaTypes, 'Interior');
                // TODO: Поправить возможность добавления тонны цветов
                $newColor = new IdeaAdditionalColor('init');
                $newColor->item_id = $sc_id;

                $newColor->idea_type_id = $idea_key[0];
                $newColor->position = (int) $pos;

                if (!$newColor->save()) {
                        Yii::app()->end();
                }

                return $newColor;
        }

        /**
         * Сохранение полученного значения цвета
         * @param int $item_id
         * @return array 
         */
        static public function saveAdditionalColor($item_id, $idea_type_id)
        {
                $errors = array();
                if (!empty($_POST['IdeaAdditionalColor'][$item_id])) {

                        foreach ($_POST['IdeaAdditionalColor'][$item_id] as $pos => $value) {

                                $color = IdeaAdditionalColor::model()->find(
					'item_id = :item_id AND idea_type_id = :idea AND position=:pos',
					array(':item_id' => $item_id, ':idea' => $idea_type_id, ':pos' => $pos));
                                if (!$color) {
                                        $color = new IdeaAdditionalColor('init');
                                        $color->item_id = (int) $item_id;
                                        $color->idea_type_id = $idea_type_id;
                                        $color->position = (int) $pos;
                                }

                                $color->attributes = $value;
                                $color->setScenario('finished');

                                if (!$color->save()) {
                                        $errors[$item_id][$pos] = $color->getErrors();
                                        $color->setScenario('failed');
                                        $color->save();
                                }
                        }
                }

                return $errors;
        }

        
        static public function formatAdditionalColors($additionalColors = array(), $colorSet = array())
        {
                if(!is_array($additionalColors))
                        $additionalColors = array($additionalColors);
                
                $html = '';
                
                foreach($additionalColors as $color){
                        if(!empty($colorSet[$color->color_id]))
                                $html.=$colorSet[$color->color_id].'<br>';
                }
                
                return $html;
        }

}