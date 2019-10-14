<?php

/**
 * Виджет выводит название опции и ее значение для сокращенной карточки товара
 */
class CatFullcardOptions extends CWidget
{
        public $model;
        private $_options;

        public function init()
        {
                if(!($this->model instanceof Product))
                        throw new CHttpException(500);

                /**
                 * Получение опций карточки товара с типами и значениями опций для текущего товара ($this->model)
                 */
                $this->_options = $this->model->fullcardOptions;
        }

        public function run()
        {
                /**
                 * Флаг группы опций, выводимых в данный момент в foreach
                 * Если езменяется, то в цикле выводится заголовок новой группы и продолжается вывод опций уже
                 * для новой группы
                 */
                $currentGroupId = 0;

                /**
                 * Проход по опциям товара и вывод
                 */
                foreach($this->_options as $option_id => $option)
                {
                        /**
                         * Если у опции не указана группа - пропуск опции
                         */
                        if(is_null($option['group_id']))
                                continue;

                        /**
                         * Если группа опции отличается от той, что была на предыдущей итерации - выводит заголовок новой группы опций
                         */
                        if($this->model->category->groupsArray[$option['group_id']] != 'Общие' && $currentGroupId != $option['group_id'] && isset($this->model->category->groupsArray[$option['group_id']])) {
                                echo CHtml::tag('li', array('class'=>'parent'), $this->model->category->groupsArray[$option['group_id']]);
                                $currentGroupId = $option['group_id'];
                        }

                        if(Option::$typeParams[$option['type_id']]['multiValue']) {
                                $value = Value::serializeToArrray($option['value']);
                                if(empty($value)) continue;
                        }

                        /**
                         * Пропуск опции без названий, типов или без значения
                         */
                        if(empty($option['name']) || is_null($option['type_id']) || ($option['value'] !== '0' && empty($option['value'])))
                                continue;

                        echo CHtml::openTag('li');

                        /**
                         * Вывод названия и значения опции в зависимости от типа опции
                         */
                        switch($option['type_id']) {
                                case Option::TYPE_INPUT :
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        echo CHtml::tag('span', array('class'=>'param_value'), $option['value']);
                                        break;

                                case Option::TYPE_TEXTAREA :
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        echo CHtml::tag('span', array('class'=>'param_value'), $option['value']);
                                        break;

                                case Option::TYPE_SELECT :
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        $value = Value::model()->findByPk((int) $option['value']);
                                        if($value) echo CHtml::tag('span', array('class'=>'param_value'), $value->value);
                                        else echo CHtml::tag('span', array('class'=>'param_value'), 'Не указано');
                                        break;

                                case Option::TYPE_CHECKBOX:
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        if($option['value']) echo CHtml::tag('span', array('class'=>'param_value'), 'Да');
                                        else echo CHtml::tag('span', array('class'=>'param_value'), 'Нет');
                                        break;

                                case Option::TYPE_SELECTMULTIPLE :
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        $query = implode(',', $value);
                                        if(!$query) break;
                                        $values = Value::model()->findAll('t.id in ('.$query.')');
                                        $values_array = array();
                                        foreach($values as $val)
                                                $values_array[] = $val->value;
                                        if(!is_array($values_array)) break;
                                        echo CHtml::tag('span', array('class'=>'param_value'), implode(', ', $values_array));
                                        break;

                                case Option::TYPE_COLOR :
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        echo CHtml::openTag('span', array('class'=>'param_value'));
                                        echo CHtml::openTag('ul', array('class'=>'colors_list'));
                                        foreach($value as $v) {
                                                $color = CatColor::model()->findByPk((int) $v);
                                                if (!$color) break;
                                                echo CHtml::openTag('li', array('class'=>$color->param));
                                                echo CHtml::tag('p', array('class'=>'hide'), $color->name);
                                                echo CHtml::tag('div');
                                                echo CHtml::closeTag('li');
                                        }
                                        echo CHtml::closeTag('ul');
                                        echo CHtml::closeTag('span');
                                        break;

                                case Option::TYPE_STYLE :
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        $style = Style::model()->findByPk((int) $option['value']);
                                        if (!$style) break;
                                        echo CHtml::tag('span', array('class'=>'param_value'),  $style->name);
                                        break;

                                case Option::TYPE_IMAGE :
                                        // TODO : Если нужно выводить опции изображений на миникарте, то сделать это здесь
                                        break;
                                case Option::TYPE_SIZE:
                                        echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
                                        echo CHtml::tag('span', array('class'=>'param_value'), $option['value'] . ' ' . (isset(Option::$units[$option['param']['size_unit']]) ? Option::$units[$option['param']['size_unit']] : ''));
                                        break;
                        }

                        echo CHtml::closeTag('li');
                }
        }
}
