<?php
/**
 * Генерирует форму для редактирования значения опции в зависимости от ее типа
 * User: desher
 * Date: 30.05.12
 * Time: 16:59
 */
class FrontProductValue extends CWidget
{
        public $value;

        public function init()
        {

        }

        public function run()
        {
                if(!$this->value || !$this->value->option)
                        throw new CHttpException(500);

                $required = false;
                if($this->value->option->required)
                        $required = true;

                $miniform = false;
                if($this->value->option->miniform)
                        $miniform = true;

                echo '<div class="options_row' . ((!$required && !$miniform) ? ' hide' : ' ') . '">';
                        echo '<div class="option_label">' . ($this->value->option->name . ($required ? ' <span class="required">*</span>' : '')) . '</div>';
                        echo '<div class="option_value">';

                switch($this->value->option->type_id) {
                        case Option::TYPE_INPUT :
                                echo CHtml::activeTextField($this->value, "[{$this->value->id}]value", array('class'=>'textInput'));
                                break;
                        case Option::TYPE_TEXTAREA :
                                echo CHtml::activeTextArea($this->value, "[{$this->value->id}]value", array('class'=>'textInput'));
                                break;
                        case Option::TYPE_CHECKBOX :
                                echo CHtml::activeCheckBox($this->value, "[{$this->value->id}]value");
                                break;
                        case Option::TYPE_SELECT :
                                echo CHtml::activeDropDownList($this->value, "[{$this->value->id}]value", array(''=>'Не выбрано') + CHtml::listData($this->value->option->getAvailableValues(), 'id', 'value'), array('class'=>'textInput'));
                                break;
                        case Option::TYPE_SELECTMULTIPLE :
                                echo CHtml::openTag('ul',array('class'=> 'checkbox_list'));
                                echo CHtml::activeCheckBoxList($this->value, "[{$this->value->id}]value", CHtml::listData($this->value->option->getAvailableValues(), 'id', 'value'), array('template'=>'<li><label>{input} {label}</label></li>', 'separator'=>''));
                                echo CHtml::closeTag('ul');
                                break;
                        case Option::TYPE_STYLE :
                                $values = Style::getProductStylesForDropdown();
                                $values[0] = 'Не выбрано';
                                echo CHtml::activeDropDownList($this->value, "[{$this->value->id}]value", $values, array('class'=>'textInput'));
                                break;
                        case Option::TYPE_COLOR :
                                $errorClass = '';
                                if($this->value->getErrors()) $errorClass = 'error';
                                echo $this->render('_color', array('value'=>$this->value, 'errorClass'=>$errorClass), true);
                                break;
                        case Option::TYPE_SIZE :
                                echo CHtml::activeTextField($this->value, "[{$this->value->id}]value", array('class'=>'textInput size')) . ' ';
                                $option_params = $this->value->option->getParamsArray();
                                echo isset($option_params['size_unit']) ? CHtml::tag('span', array('class'=>'unit'), Option::$units[$option_params['size_unit']]) : '';
                                break;
                }

                        echo '</div><div class="clear"></div>';
                echo '</div>';
        }
}
