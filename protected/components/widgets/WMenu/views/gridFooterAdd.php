<?php
if ( ! empty($menu))
{
	$menuHtml = '';

	$menuHtml .= CHtml::openTag('ul', array('class' => '-menu-block -gray'));
	
	for ($i = 0, $ci = count($menu); $i < $ci ; $i++)
	{
		$item = $menu[$i];
		
		$class = array();

		if ($item['level'] != $this->showLevel)
			continue;

		if ($item['status'] == Menu::STATUS_INPROGRESS)
			$class[] = 'secondly';

		if ($item['key'] == 'help_footer')
		{
			$class[] = '-icon-lifebuoy';
			$class[] = '-red';
		}
		elseif ($item['key'] == 'shop_adv')
		{
			$class[] = '-icon-cart';
			$class[] = '-red';
		}
		elseif ($item['key'] == 'feedback_footer')
		{
			$class[] = '-icon-question';
			$class[] = '-red';
			$class[] = '-feedback';
		}
		elseif($item['key'] == 'konkurs')
		{
			$class[] = ''; //'-new-label';
		}

		$menuHtml .= CHtml::openTag('li');

		if ($item['status'] == Menu::STATUS_INPROGRESS) {
			$menuHtml .= CHtml::encode($item['label']);
		} else {
			$menuHtml .= CHtml::link($item['label'], $item['url'], array('class' => implode(' ', $class)));
		}

		$menuHtml .= CHtml::closeTag('li');
	}
	
	
	$menuHtml .= CHtml:: closeTag('ul');
	
	
	echo $menuHtml;
}