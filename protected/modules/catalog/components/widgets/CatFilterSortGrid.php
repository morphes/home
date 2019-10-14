<?php

/**
 * Виджет выводит элементы, по которым возможно произвести сортировку
 */
class CatFilterSortGrid extends CWidget
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
		$htmlOut = '';

                $htmlOut .= CHtml::openTag('div', array('class'=>'-col-wrap -gutter-right sorting'));
		$htmlOut .= CHtml::tag('span', array('class' => '-small -gray'), $this->label);



		if ($this->renderDefault) {
			$class = (isset($this->value[0]) && $this->value[0] == 'default')
				? '-sort-active'
				: '';
			$htmlOut .= ' ';
			$htmlOut .= CHtml::openTag(
				'span',
				array(
					'class'      => '-sort ' . $class,
					'data-order' => 'asc'
				)
			);
			$htmlOut .= CHtml::link($this->textDefault, '#', array('class' => '-small -acronym', 'data-sort' => 'default'));
			$htmlOut .= CHtml::closeTag('span');

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
                                $class = '-sort-active' . ' -sort-' . $order;

				switch($order) {
					case 'asc' :
						$order = 'desc'; break;
					case 'desc' :
						$order = 'asc'; break;
					default :
						$order = $this->defaultOrder;
				}
                        } else {
				// Если выводим не активный пункт сортировки, то направление
				// сортировки всегда принимает значение по-умолчанию
                                $order = isset($item['order']) ? $item['order'] : $this->defaultOrder;
                        }


                        $htmlOut .= CHtml::openTag('span', array('class'=>'-sort ' . $class));
			$htmlOut .= CHtml::link(
				$item['text'],
				'#',
				array(
					'data-sort'  => $item['name'] . '_' . $order,
					'data-order' => $order,
					'class'      => '-acronym -small'
				)
			);
			$htmlOut .= CHtml::closeTag('span');

                }

		$htmlOut .= CHtml::closeTag('div');


		echo '<noindex>'.$htmlOut.'</noindex>';


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
