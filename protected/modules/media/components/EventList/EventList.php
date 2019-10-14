<?php

/**
 * @brief Вывод тендеров в фильтре
 * @author alexsh
 */
class EventList extends CWidget
{
	public $dataProvider;

	public $emptyText = null;
	public $sortType = null;
	public $pageSize = null;

	public $cityId = 0;
	public $viewType = 0;
	public $sortDirect = null;
	// for banner
	public $viewNumber = 2; // номер, после которого выводится баннер
	public $bannerText = '';

	private $_data = null;
	
	public function init()
	{
		if (is_null($this->pageSize))
			throw new CException('Invalid page size');
		if (is_null($this->sortDirect))
			throw new CException('Invalid sort direct');

			$this->_data = $this->dataProvider->getData();
	}
	
	public function run()
	{
		echo CHtml::openTag('div', array('id'=>'right_side', 'class'=>'new_template'));
			if(count($this->_data)>0) {
				$this->render('navigate', array(
					'pagination' => $this->dataProvider->getPagination(),
					'sortType' => $this->sortType,
					'pageSize' => $this->pageSize,
					'isBottom' => false,
					'sortDirect' => $this->sortDirect,
					'viewType' => $this->viewType,
				));
			}

			$class = $this->viewType ? 'knowledge_items elements calendar' : 'knowledge_items list calendar';
			echo CHtml::openTag('div', array('class'=>$class));
			$this->renderItems();
			echo CHtml::tag('div', array('class'=>'clear'), '');
			echo CHtml::closeTag('div');

			if(count($this->_data)>0) {
				$this->render('navigate', array(
					'pagination' => $this->dataProvider->getPagination(),
					'sortType' => $this->sortType,
					'pageSize' => $this->pageSize,
					'isBottom' => true,
					'sortDirect' => $this->sortDirect,
					'viewType' => $this->viewType,
				));
			}
		echo CHtml::closeTag('div');
		unset ( $this->_data );
	}

	/**
	 * Render items
	 */
	public function renderItems()
	{
		if(($n=count($this->_data))>0)
		{
			$view = $this->viewType ? '_eventItem2Col' : '_eventItem';
			foreach($this->_data as $i=>$item)
			{
				$this->_data=array();
				$this->_data['index']=$i;
				$this->_data['data']=$item;
				$this->_data['cityId']=$this->cityId;
				$this->render($view, $this->_data);

				if ($i == $this->viewNumber-1)
					echo $this->bannerText;
			}
		}
		else
			$this->renderEmptyText();
	}
	
	/**
	 * Renders the empty message when there is no data.
	 */
	public function renderEmptyText()
	{
                $emptyText=$this->emptyText===null ? 'У нас пока что нет событий, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="/'.Yii::app()->request->pathInfo.'">сбросить параметры фильтра</a>' : $this->emptyText;
                echo CHtml::tag('div', array('class'=>'no_result'), $emptyText);
	}
}