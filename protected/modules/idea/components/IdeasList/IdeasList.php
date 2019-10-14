<?php

/**
 * @brief Новый вывод идей с кастомизированым пагинатором
 * @author alexsh
 */
class IdeasList extends CWidget
{
	public $dataProvider;
	public $columnsCount = 4;
	public $availablePageSizes = array(9 => 9, 15 => 15, 30 => 30, 45 => 45);
	public $emptyText = null;
	public $itemView = null;
	public $sortType = null;
	public $pageSize = null;
	public $sortList = null;
	public $search = false;
	public $htmlOptions = array('class' => 'gallery-210');
	public $showNavigate = true;
        public $hideSortOptions = false;
	// for banner
	public $viewNumber = 9; // номер, после которого выводится баннер
	public $bannerText = '';

	/** InteriorPublic SEO */
	public $oneBuild = null;
	
	private $_data = null; 
	
	public function init()
	{
		if ($this->itemView === null) {
			throw new CException(Yii::t('zii', 'The property "itemView" cannot be empty.'));
		}
		if (is_null($this->pageSize)) {
			$this->pageSize = @reset($this->availablePageSizes);
		}
		if (is_null($this->sortType)) {
			$this->sortType = Config::IDEA_SORT_DATE;
		}
		// Fix for old widget calls
		if (is_null($this->sortList)) {
			$this->sortList = Config::$ideaSortNames;
		}
		
		$this->_data = $this->dataProvider->getData();
	}
	
	public function run()
	{
		if ($this->showNavigate && count($this->_data) > 0) {
			$this->render('navigate', array(
				'dataProvider'       => $this->dataProvider,
				'availablePageSizes' => $this->availablePageSizes,
				'sortType'           => $this->sortType,
				'pageSize'           => $this->pageSize,
				'sortList'           => $this->sortList,
				'search'             => $this->search,
				'hideSortOptions'    => $this->hideSortOptions,
			));
            Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_above');
		}
		echo CHtml::openTag('div', $this->htmlOptions);
		$this->renderItems();
		echo CHtml::tag('div', array('class' => 'clear'), '');
		
		echo CHtml::closeTag('div');

		if ($this->showNavigate && count($this->_data) > 0) {
            Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under');
			$this->render('navigate', array(
				'dataProvider'       => $this->dataProvider,
				'availablePageSizes' => $this->availablePageSizes,
				'sortType'           => $this->sortType,
				'pageSize'           => $this->pageSize,
				'sortList'           => $this->sortList,
				'search'             => $this->search,
				'hideSortOptions'    => $this->hideSortOptions,
                'bottom'             => true,
			));

		}
		unset ( $this->_data );
	}

	/**
	 * Render items
	 */
	public function renderItems()
	{
		if(($n=count($this->_data))>0)
		{
			$owner=$this->getOwner();
			$render=$owner instanceof CController ? 'renderPartial' : 'render';
			foreach($this->_data as $i=>$item)
			{
				//Если показано не автору идеи и идея принадлежит специалисту
				//То хитуем ее для статистики
				if (yii::app()->user->id !== $item->author_id) {
					$author = User::model()->findByPk($item->author_id);

					if (in_array($author->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
						if (get_class($item) == 'InteriorContent') {
							StatProject::hit($item->interior_id, 'Interior', $author->id, StatProject::TYPE_SHOW_PROJECT_IN_LIST);
						} else {
							StatProject::hit($item->id, get_class($item), $author->id, StatProject::TYPE_SHOW_PROJECT_IN_LIST);
						}
					}
				}

				$this->_data=array();
				$this->_data['index']=$i;
				$this->_data['data']=$item;
				$this->_data['widget']=$this;
				$this->_data['oneBuild']=$this->oneBuild;
				$owner->$render($this->itemView,$this->_data);
				if ($i == $this->viewNumber-1) {
					echo $this->bannerText;
                }
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
		$emptyText=$this->emptyText===null ? 'У нас пока что нет идей, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="/'.Yii::app()->request->pathInfo.'">сбросить параметры фильтра</a>' : $this->emptyText;
		echo CHtml::tag('div', array('class'=>'no_result'), $emptyText);
	}
}