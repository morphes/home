<?php

/**
 * @brief Вывод тендеров в фильтре
 * @author alexsh
 */
class TenderList extends CWidget
{
	public $dataProvider;
	public $availablePageSizes = array(9 => 9, 15 => 15, 30 => 30, 45 => 45);
	
	public $emptyText = null;
	public $sortType = null;
	public $pageSize = null;
	
	private $_data = null;
	
	public function init()
	{
		if (is_null($this->pageSize))
			$this->pageSize = @reset($this->availablePageSizes);
		if (is_null($this->sortType))
			$this->sortType = Tender::SORT_DATE;
		
		$this->_data = $this->dataProvider->getData();
	}

    public function run()
    {
        echo CHtml::openTag('div', array('id' => 'right_side'));
        if (count($this->_data) > 0) {
            $this->render('navigate', array(
                'pagination' => $this->dataProvider->getPagination(),
                'availablePageSizes' => $this->availablePageSizes,
                'sortType' => $this->sortType,
                'pageSize' => $this->pageSize,
                'bottom' => false,
            ));
        }

        $this->render('header');
        echo CHtml::openTag('div', array('class' => 'tender_list tenders_page'));
        $this->renderItems();
        echo CHtml::closeTag('div');
        Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under');

        if (count($this->_data) > 0) {
            $this->render('navigate', array(
                'pagination' => $this->dataProvider->getPagination(),
                'availablePageSizes' => $this->availablePageSizes,
                'sortType' => $this->sortType,
                'pageSize' => $this->pageSize,
                'bottom' => true,
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
			$time = time();
			$isGuest = Yii::app()->user->isGuest;
			$isSpecialist = in_array(Yii::app()->getUser()->getRole(), array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN));
            Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_above');

            foreach ($this->_data as $i => $item) {
                $this->_data = array();
                $this->_data['index'] = $i;
                $this->_data['data'] = $item;
                $this->_data['time'] = $time;
                $this->_data['isGuest'] = $isGuest;
                $this->_data['isSpecialist'] = $isSpecialist;
                $this->render('_tenderItem', $this->_data);
            }
        } else
            $this->renderEmptyText();
    }
	
	/**
	 * Renders the empty message when there is no data.
	 */
	public function renderEmptyText()
	{
                $emptyText=$this->emptyText===null ? 'У нас пока что нет заказов, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="/'.Yii::app()->request->pathInfo.'">сбросить параметры фильтра</a>' : $this->emptyText;
                echo CHtml::tag('div', array('class'=>'no_result'), $emptyText);
	}
}