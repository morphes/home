<?php

//Yii::import('zii.widgets.CBaseListView');
/**
 * @brief Вывод идей с кастомизированым пагинатором
 */
class MediaItemsList extends CWidget
{
        public $pageSize = null; // current pagesize
        public $pageSizeVar = 'pagesize';
        public $pageSizeText = 'Показывать на странице: ';
        // Если не установлен true и в Refferer что-нибудь есть, то
        // при $options['url'] будет записан не текущий Uri, а Refferer
        public $saveUri = false;
        // Ключ для Clip'a, который нужно вывести перед виджетом.
        public $namePreClip = '';
        // Строка с именем класса, дополнительно обрамляющий содержимое.
        // В этот див не попадает постраничка.
        public $extraDivClass = '';

        public $dataProvider = null;
        public $itemView = '';

        public $pager=array('class'=>'application.components.widgets.CustomPager2');
        public $htmlOptions=array();

        public $emptyText = null;
	// for banner
	public $viewNumber = 2; // номер, после которого выводится баннер
	public $bannerText = '';

        // Скрытие блока выбора количества элементов на странице
        public $hidePageSize = false;

        public function init()
        {
                if($this->itemView===null)
                        throw new CException(Yii::t('zii','The property "itemView" cannot be empty.'));

                if($this->dataProvider===null)
                        throw new CException(Yii::t('zii','The "dataProvider" property cannot be empty.'));

                $this->htmlOptions['id']=$this->getId();

                if(!isset($this->htmlOptions['class']))
                        $this->htmlOptions['class']='list-view';

        }

        public function run()
        {
                //$this->registerClientScript();

                // Главный div открылся
                echo CHtml::openTag('div',$this->htmlOptions)."\n";


                // ==>> Экстра div Открылся
                if ($this->extraDivClass != '')
                        echo CHtml::openTag('div', array('class' => $this->extraDivClass));

                // Рендерим предварающий клип
                echo Yii::app()->controller->clips[$this->namePreClip];

                // Рендерим список элементов
                $this->renderItems();

		echo CHtml::tag('div', array('class' => 'clear'), '', true);

                // ==>> Экстра div закрылся
                if ($this->extraDivClass != '')
                        echo CHtml::closeTag('div');

                // Главный div закрылся
                echo CHtml::closeTag('div');
        }

        /**
         * Render items
         */
        public function renderItems()
        {
                $data=$this->dataProvider->getData();
                if(($n=count($data))>0)
                {
                        $owner=$this->getOwner();
                        $render=$owner instanceof CController ? 'renderPartial' : 'render';
                        foreach($data as $i=>$item)
                        {
                                $data=array();
                                $data['index']=$i;
                                $data['data']=$item;
                                $data['widget']=$this;
                                $owner->$render($this->itemView,$data);

				if ($i == $this->viewNumber-1)
					echo $this->bannerText;
                        }
                        // Перенес рендер постранички в метод run()
                        // $this->renderPager();
                }
                else
                        $this->renderEmptyText();
        }



        /**
         * Renders the empty message when there is no data.
         */
        public function renderEmptyText()
        {
                $emptyText=$this->emptyText===null ? 'Результатов нет.' : $this->emptyText;
                echo CHtml::tag('span', array('class'=>'empty'), $emptyText);
        }
}