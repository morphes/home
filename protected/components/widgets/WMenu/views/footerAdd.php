<?php
if ( ! empty($menu))
{
	$menuHtml = '';

	$menuHtml .= CHtml::openTag('ul', array('class' => 'fnav-about'));
	
	for ($i = 0, $ci = count($menu); $i < $ci ; $i++)
	{
		$item = $menu[$i];
		
		$class = array();

		if ($item['level'] != $this->showLevel)
			continue;

		if ($item['status'] == Menu::STATUS_INPROGRESS)
			$class[] = 'secondly';

		if ($item['key'] == 'feedback_footer') {
			$class[] = 'feedback-handler';
		}

		$menuHtml .= CHtml::openTag('li', array('class' => implode(' ', $class) ));

		if ($item['status'] == Menu::STATUS_INPROGRESS) {
			$menuHtml .= CHtml::encode($item['label']);
		} else {
			$menuHtml .= CHtml::link($item['label'], $item['url']);
		}

		$menuHtml .= CHtml::closeTag('li');
	}
	
	
	$menuHtml .= CHtml:: closeTag('ul');
	
	
	echo $menuHtml;
}