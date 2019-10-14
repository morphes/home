<?php

/**
 * Виджет выводит элементы, по которым возможно произвести сортировку
 */
class CatFilterSort extends CWidget
{
        /**
         * @var string Текст, выводимый до элементов сортировки
         */
        public $label = 'Сортировать по';

        public $cookieName = 'product_filter_sort';

        /**
         * @var array Элементы, по которым можно сортировать
         * array(name=>"название столбца сортировки", data=>"дополнительные данные столбца", order=>"asc/desc")
         */
        public $items = array();

	public $defaultSort = null;
        public $defaultOrder = 'asc';
	// грязный хак для значения по умолчанию
	public $renderDefault = false;
	public $textDefault = 'умолчанию';

        public $formSelector = '#filter_form';

        private $value = array();

        public function init()
        {
                if(!$this->cookieName)
                        throw new CHttpException(500);

                if(Yii::app()->request->cookies[$this->cookieName])
		{
                        $this->value = explode('_',  Yii::app()->request->cookies[$this->cookieName]->value);
		}
		elseif ( ! is_null($this->defaultSort)) {
			$cookieValue = ($this->renderDefault) ? $this->defaultSort : ($this->defaultSort.'_'.$this->defaultOrder);
			Yii::app()->request->cookies[$this->cookieName] = new CHttpCookie($this->cookieName, $cookieValue);
			$this->value = array($this->defaultSort, $this->defaultOrder);
 		}

        }

        public function run()
        {
                echo CHtml::openTag('div', array('class'=>'sort_elements'));
                echo CHtml::encode($this->label);

		if ($this->renderDefault) {
			$class = (isset($this->value[0]) && $this->value[0]=='default') ? 'current' : '';
			echo CHtml::openTag('div', array('class'=>'sorting ' . $class));
			echo CHtml::tag('span', array('data'=>'default'), $this->textDefault);
			echo CHtml::closeTag('div');
		}

                foreach($this->items as $item) {

                        $class = '';

                        if (isset($this->value[0]) && $this->value[0] == $item['name'])
			{
				/*
				 * Если выводим текущий эелемент, то отмечаем его
				 * и инвертируем направление соритровки, чтобы она сменилась
				 * при следующем клике на этот же пункт
				 */
                                $order = isset($this->value[1]) ? $this->value[1] : $this->defaultOrder;
                                $class = 'current' . ' ' . $order;

				switch($order) {
					case 'asc' :
						$order = 'desc'; break;
					case 'desc' :
						$order = 'asc'; break;
					default :
						$order = $this->defaultOrder;
				}
                        }
                        else
			{
				// Если выводим не активный пункт сортировки, то направление
				// сортировки всегда принимает значение по-умолчанию
                                $order = isset($item['order']) ? $item['order'] : $this->defaultOrder;
                        }


                        echo CHtml::openTag('div', array('class'=>'sorting ' . $class));
                        echo CHtml::tag('span', array('data'=>$item['name'].'_'.$order), $item['text']) . '<i></i>';
                        echo CHtml::closeTag('div');
                }

                echo CHtml::closeTag('div');

//		$domain = Config::getCookieDomain();
//		$domain = (empty($domain)) ? 'window.location.hostname' : '\''.$domain.'\'';
		$jsParams = array(
			'expires'=>1800,
			'path'=>'/',
			//'domain'=>$domain,
		);
                Yii::app()->clientScript->registerScript('sorting', '
                        $(".sorting span").click(function(){
                                CCommon.setCookie("'.$this->cookieName.'", $(this).attr("data"),'.json_encode($jsParams, JSON_NUMERIC_CHECK).');
                                $("'.$this->formSelector.'").submit();
                        });
                ', CClientScript::POS_READY);
        }
}
