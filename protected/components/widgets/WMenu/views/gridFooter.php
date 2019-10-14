<?php
if ( ! empty($menu))
{
	$menuHtml = '';

	$menuHtml .= CHtml::openTag('ul', array('class' => "-menu-block -gray"));

	for ($i = 0, $ci = count($menu); $i < $ci; $i++ )
	{
		$item = $menu[$i];
		
		if ($item['level'] != $this->showLevel)
			continue;

		$menuHtml .= CHtml::openTag('li');
		$menuHtml .= CHtml::link($item['label'], $item['url']);
		$menuHtml .= CHtml::closeTag('li');
	}
	
	$menuHtml .= CHtml:: closeTag('ul');
	
	
	echo $menuHtml;
}
?>