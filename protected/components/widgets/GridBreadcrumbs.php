<?php
/**
 * Custom breadcrumbs
 * @see CBreadcrumbs
 */
class GridBreadcrumbs extends CWidget
{
	public $tagName='ul';
	public $htmlOptions=array('class'=>'-menu-inline -breadcrumbs');
	public $encodeLabel=true;
	public $homeLink;
	public $links=array();
	public $separator=' ';

	public function run()
	{
		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
		$links=array();
		if($this->homeLink===null)
			$links[]=CHtml::link(Yii::t('zii','Home'),Yii::app()->homeUrl);
		else if($this->homeLink!==false)
			$links[]=$this->homeLink;

		foreach($this->links as $label=>$url)
		{
			if(is_string($label) || is_array($url))
				$links[]='<li>'.CHtml::link($this->encodeLabel ? CHtml::encode($label) : $label, $url).'</li>';
			else
				$links[]='<li>'.($this->encodeLabel ? CHtml::encode($url) : $url).'</li>';
		}
		echo implode($this->separator,$links);
		echo CHtml::closeTag($this->tagName);
	}
}