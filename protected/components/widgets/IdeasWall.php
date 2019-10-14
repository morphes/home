<?php

//Yii::import('zii.widgets.CBaseListView');
/**
 * @brief Вывод идей с кастомизированым пагинатором
 */
class IdeasWall extends CWidget
{

        public $columnsCount = 4;
        public $availablePageSizes = array(9 => 9, 15 => 15, 30 => 30, 45 => 45);
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
        public $enablePagination = true;

        public $pager=array('class'=>'application.components.widgets.CustomPager2');
        public $htmlOptions=array();

        public $emptyText = null;

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

                // ==>> Экстра div закрылся
                if ($this->extraDivClass != '')
                        echo CHtml::closeTag('div');

                // Главный div закрылся
                echo CHtml::closeTag('div');

                // Рендерим постраничку
                $this->renderPager();

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
                        }
                        // Перенес рендер постранички в метод run()
                        // $this->renderPager();
                }
                else
                        $this->renderEmptyText();
        }

        /**
         * Render pagesize dropdown list
         */
        public function renderPageSize()
        {
                if (is_null($this->pageSize))
                        $this->pageSize = reset($this->availablePageSizes);

                $pagination = $this->dataProvider->getPagination();

                // create url
                $params=$pagination->params===null ? $_GET : $pagination->params;

                unset($params[$pagination->pageVar]);

                $content = '
			<div class="elements_on_page drop_down">
			На странице <span class="exp_current">'.$this->availablePageSizes[$this->pageSize].'<i></i></span>
			<ul>';

                foreach ($this->availablePageSizes as $key => $value) {
                        $params[$this->pageSizeVar] = $key;
                        $content .= CHtml::tag('li', array('data-value' => $key, 'onclick' => '
				document.location = "'.$this->getController()->createUrl($pagination->route, $params).'"
			'), $value);
                }

                $content .= '
			</ul>
		</div>';

                return $content;
        }

        /**
         * Renders the pager.
         */
        public function renderPager()
        {
                if(!$this->enablePagination)
                        return;

                $pager=array();
                $class='CustomPager2';
                if(is_string($this->pager))
                        $class=$this->pager;
                else if(is_array($this->pager))
                {
                        $pager=$this->pager;
                        if(isset($pager['class']))
                        {
                                $class=$pager['class'];
                                unset($pager['class']);
                        }
                }
                if ($this->dataProvider->getTotalItemCount() == 0)
                        return;
                $pager['pages']=$this->dataProvider->getPagination();
                $pager['prevPageLabel']='<span class="arr">&larr;</span>';
                $pager['nextPageLabel']='<span class="arr">&rarr;</span>';

                if ($this->hidePageSize == false)
                        echo $this->renderPageSize();

                echo CHtml::openTag('div', array('class'=>'pages'));

                $this->widget($class,$pager);
                echo CHtml::closeTag('div');

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