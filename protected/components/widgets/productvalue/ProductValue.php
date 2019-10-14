<?php
/**
 * Генерирует форму для редактирования значения опции в зависимости от ее типа
 * User: desher
 * Date: 30.05.12
 * Time: 16:59
 */
class ProductValue extends CWidget
{
	/** @var Value */
        public $value;
        public $ARForm = true; // Генерирует ActiveForms

        public function init()
        {

        }

        public function run()
        {
                if(!$this->value || !$this->value->option)
                        throw new CHttpException(500);

                if($this->ARForm) {
                        switch($this->value->option->type_id) {
                                case Option::TYPE_INPUT :
                                        echo CHtml::activeTextField($this->value, "[{$this->value->id}]value");
                                        break;
                                case Option::TYPE_TEXTAREA :
                                        echo CHtml::activeTextArea($this->value, "[{$this->value->id}]value");
                                        break;
                                case Option::TYPE_CHECKBOX :
                                        echo CHtml::activeCheckBox($this->value, "[{$this->value->id}]value");
                                        break;
                                case Option::TYPE_SELECT :
                                        echo CHtml::activeDropDownList($this->value, "[{$this->value->id}]value", CHtml::listData(array(''=>'') + $this->value->option->getAvailableValues(), 'id', 'value'));
                                        break;
                                case Option::TYPE_SELECTMULTIPLE :
                                        echo CHtml::activeDropDownList($this->value, "[{$this->value->id}]value", CHtml::listData($this->value->option->getAvailableValues(), 'id', 'value'), array('multiple'=>'multiple'));
                                        break;
                                case Option::TYPE_STYLE :
                                        $styles = Style::model()->findAll();
                                        echo CHtml::activeDropDownList($this->value, "[{$this->value->id}]value", array(''=>'Не выбран') + CHtml::listData($styles, 'id', 'name'));
                                        break;
                                case Option::TYPE_COLOR :
                                        echo $this->render('_color', array('value'=>$this->value), true);
                                        break;
                                case Option::TYPE_IMAGE :
                                        echo $this->render('_image', array('value'=>$this->value), true);
                                        break;
                                case Option::TYPE_SIZE :
                                        echo CHtml::activeTextField($this->value, "[{$this->value->id}]value", array('style'=>'width:80px;')) . ' ';
                                        $option_params = $this->value->option->getParamsArray();
                                        echo isset($option_params['size_unit']) ? Option::$units[$option_params['size_unit']] : '';
                                        break;
                        }
                }

        }
}
