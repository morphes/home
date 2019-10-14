<?php
/**
 * CLinkPager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
/**
 * CLinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CLinkPager.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.web.widgets.pagers
 * @since 1.0
 */
class CustomPager2 extends CBasePager
{
	const CSS_PREVIOUS_PAGE='b';
	const CSS_INACTIVE='inactive';
	const CSS_NEXT_PAGE='b';
	const CSS_INTERNAL_PAGE='';
	const CSS_HIDDEN_PAGE='';
	const CSS_SELECTED_PAGE='';

	/**
	 * @var integer maximum number of page buttons that can be displayed. Defaults to 10.
	 */
	public $maxButtonCount=10;
	/**
	 * @var string the text label for the next page button. Defaults to 'Next &gt;'.
	 */
	public $nextPageLabel;
	/**
	 * @var string the text label for the previous page button. Defaults to '&lt; Previous'.
	 */
	public $prevPageLabel;
	/**
	 * @var string the text shown before page buttons. Defaults to 'Go to page: '.
	 */
	public $header='';
	/**
	 * @var array HTML attributes for the pager container tag.
	 */
	public $htmlOptions=array('class' => 'paginator');

	/**
	 * @var int Определяет сколько ссылок подряд выводит в пагинации.
	 */
	private $stepLinks = 3;

    public $newStyle = false;

	/**
	 * Initializes the pager by setting some default property values.
	 */
	public function init()
	{
		if($this->nextPageLabel===null)
			$this->nextPageLabel='<span class="arr">&rarr;</span>';

		if($this->prevPageLabel===null)
			$this->prevPageLabel='<span class="arr">&larr;</span>';

		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$this->getId();

		if(!isset($this->htmlOptions['class']))
			$this->htmlOptions['class']='yiiPager';
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		$buttons=$this->createPageButtons();

		if(empty($buttons))
			return;

        $htmlOptionsMini = $this->htmlOptions;
        $htmlOptionsMini['style'] = 'display:inline';
        $htmlOptionsFull = $this->htmlOptions;
        $htmlOptionsFull['style'] = 'display:none';

		echo CHtml::tag('p', $htmlOptionsMini, implode("\n",$buttons));

        if ($this->newStyle && $this->getCurrentPage() == 0) {
            echo CHtml::tag('p', $htmlOptionsFull, implode("\n", $this->getAllButtons()));
            echo CHtml::tag('span', ['class'=>'-acronym -small allpages', 'style'=>'margin-left:10px; color:#808080;'], 'Все страницы');
            Yii::app()->clientScript->registerScript('pager', '
            $(".allpages").click(function(){
                var hidden = $(this).parents("div.pagination").find("p:hidden");
                var current = $(this).parents("div.pagination").find("p:not(:hidden)");
                current.css("display", "none");
                hidden.css("display", "inline");
                if ($(this).text() == "Все страницы") {
                    $(this).text("Свернуть список страниц");
                } else {
                    $(this).text("Все страницы");
                }
            });
        ', CClientScript::POS_READY);
        }
	}

    protected function getAllButtons()
    {
        $buttons = array();
        for($i = 0; $i < $this->getPageCount(); $i++) {
            $buttons[] = $this->createPageButton($i + 1, $i, self::CSS_INTERNAL_PAGE, false, $i == $this->getCurrentPage(false));
        }
        return $buttons;
    }

	/**
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageButtons()
	{
		if(($pageCount=$this->getPageCount())<=1)
			return array();

		// Номер максимальной страницы
		$maxPage = $pageCount - 1;
		// Раземр шага в одну сторону.
		$halfStep = intval($this->stepLinks/2);

		$currentPage=$this->getCurrentPage(false); // currentPage is calculated in getPageRange()

		$buttons=array();


		// Стрелка "предыдущая страница"
		$buttons[] = $this->createPageButton($this->prevPageLabel, (($page=$currentPage-1) < 0) ? 0 : $page, self::CSS_PREVIOUS_PAGE, $currentPage <= 0, false);

		// Первая страница
		$buttons[] = $this->createPageButton(1, 0, self::CSS_INTERNAL_PAGE, false, 0 == $currentPage);

		// Внутренние страницы
		$index = 1;
		foreach($this->getPageRange() as $i)
		{
			// Если выводим первую ссылку в середине постранички, и она не является второй ссылкой
			// то ставим точечки.
			if ($index == 1 && $i > 1)
				$buttons[] = '...';

			$buttons[] = $this->createPageButton($i+1, $i, self::CSS_INTERNAL_PAGE, false, $i==$currentPage);

			// Если выводим последнюю ссылку в середине постранички, и она не является предпоследней,
			// то ставим точенчки.
			if ($index == count($this->getPageRange()) && $i < $maxPage-1)
				$buttons[] = '...';

			$index++;
		}

		// Последняя страница
		$buttons[] = $this->createPageButton($maxPage+1, $maxPage, self::CSS_INTERNAL_PAGE, false, $maxPage == $currentPage);

		// Стрелка "следующая страница"
		$buttons[] = $this->createPageButton($this->nextPageLabel, (($page=$currentPage+1) >= $maxPage) ? $maxPage : $page, self::CSS_NEXT_PAGE, $currentPage >= $maxPage, false);


		return $buttons;
	}

	/**
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 * @param string $label the text label for the button
	 * @param integer $page the page number
	 * @param string $class the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
	 * @param boolean $hidden whether this page button is visible
	 * @param boolean $selected whether this page button is selected
	 * @return string the generated button
	 */
	protected function createPageButton($label,$page,$class,$hidden,$selected)
	{
		if ($selected)
			return CHtml::tag ('strong', array(), $label);
		else {
			$url = $this->createPageUrl($page);
			$options = array('class' => $class);
			return CHtml::link($label,$url, $options);
		}
	}

	/**
	 * @return array Массив содержащий номера ссылок, которые нужно показывать
	 * между первой и послденей страницей
	 */
	protected function getPageRange()
	{
		$currentPage = $this->getCurrentPage();
		$maxPage = $this->getPageCount()-1;

		$arr = array();

		$min = max(1, $currentPage - intval($this->stepLinks/2));
		$max = min($maxPage-1, $currentPage + intval($this->stepLinks/2));


		if ($this->stepLinks <= $maxPage-1) {
			if ($min == 1)
				$max = $min + $this->stepLinks - 1;
			elseif ($max == $maxPage-1)
				$min = $maxPage - $this->stepLinks;
		}


		for ($i = $min, $ci = $max; $i <= $ci; $i++) {
			$arr[] = $i;
		}

		return $arr;
	}

}
