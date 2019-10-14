<?php

/**
 * Виджет выводит название опции и ее значение для сокращенной карточки товара
 */
class CatMinicardOptions extends CWidget
{
        public $model;
        private $_options;

        public function init()
        {
                if(!($this->model instanceof Product))
                        throw new CHttpException(500);

                /**
                 * Получение опций сокращенной карточки товара с типами и значениями опций для текущего товара ($this->model)
                 */
                $this->_options = $this->model->minicardOptions;
        }

        public function run()
        {
                /**
                 * Проход по опциям сокращенной карточки товара и вывод
                 */
                foreach($this->_options as $option)
                {
                        /**
                         * Пропуск опции без названий, типов или без значения
                         */
                        if(empty($option['name']) || empty($option['type_id']) || empty($option['value']))
                                continue;

                        if(Option::$typeParams[$option['type_id']]['multiValue']) {
                                $val = Value::serializeToArrray($option['value']);
                                if(empty($val))
                                        continue;
                        }

                        echo CHtml::openTag('li');

                        /**
                         * Вывод названия и значения опции в зависимости от типа опции
                         */
                        switch($option['type_id']) {
                                case Option::TYPE_INPUT :
                                        echo CHtml::tag('span', array(), $option['name']).': ';
                                        echo $option['value'];
                                        break;

                                case Option::TYPE_TEXTAREA :
                                        echo CHtml::tag('span', array(), $option['name']).'<br>';
                                        echo $option['value'];
                                        break;

                                case Option::TYPE_SELECT :
                                        echo CHtml::tag('span', array(), $option['name']).': ';
                                        $value = Value::model()->findByPk((int) $option['value']);
                                        if($value) echo $value->value;
                                        else echo 'Не указано';
                                        break;

                                case Option::TYPE_CHECKBOX:
                                        echo CHtml::tag('span', array(), $option['name']).': ';
                                        if($option['value']) echo 'Да';
                                        else echo 'Нет';
                                        break;

                                case Option::TYPE_SELECTMULTIPLE :
                                        echo CHtml::tag('span', array(), $option['name']).': ';
                                        $query = implode(',', $val);
                                        if(!$query) break;
                                        $values = Value::model()->findAll('t.id in ('.$query.')');
                                        $values_array = array();
                                        foreach($values as $val)
                                                $values_array[] = $val->value;
                                        echo implode(', ', $values_array);
                                        break;

                                case Option::TYPE_COLOR :
                                        echo CHtml::tag('span', array(), $option['name']);
                                        echo CHtml::openTag('ul', array('class'=>'colors_list'));
                                        foreach($val as $v) {
                                                $color = CatColor::model()->findByPk((int) $v);
                                                if (!$color) break;
                                                echo CHtml::openTag('li', array('class'=>$color->param, 'title' => $color->name));
                                                echo CHtml::tag('p', array('class'=>'hide'), $color->name);
                                                echo CHtml::tag('div');
                                                echo CHtml::closeTag('li');
                                        }
                                        echo CHtml::closeTag('ul');
                                        break;

                                case Option::TYPE_STYLE :
                                        echo CHtml::tag('span', array(), $option['name']).': ';
                                        $style = Style::model()->findByPk((int) $option['value']);
                                        if (!$style) break;
                                        echo $style->name;
                                        break;

                                case Option::TYPE_IMAGE :
                                        // TODO : Если нужно выводить опции изображений на миникарте, то сделать это здесь
                                        break;

                                case Option::TYPE_SIZE :
                                        echo CHtml::tag('span', array(), $option['name']).': ';
                                        echo $option['value'] . ' ';
                                        echo ' ' . isset(Option::$units[$option['param']['size_unit']]) ? Option::$units[$option['param']['size_unit']] : '';
                                        break;
                        }

                        echo CHtml::closeTag('li');
                }
        }
}
