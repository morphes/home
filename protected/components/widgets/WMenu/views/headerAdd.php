<?php
if ( ! empty($menu))
{
	$menuHtml = '';

	$menuHtml .= CHtml::openTag('ul', array('id' => 'nav-support'));

	for ($i = 0, $ci = count($menu); $i < $ci; $i++ )
	{
		$item = $menu[$i];
		
		$class = array();		

		if ($item['level'] != $this->showLevel)
			continue;

		if ($i == 0)
			$class[] = 'first';

		if ($item['status'] == Menu::STATUS_INPROGRESS)
			$class[] = 'secondly';

		$menuHtml .= CHtml::openTag('li', array('class' => implode(' ', $class) ));

		if ($item['selected'] == true) {
			// ** Выбранный пункт меню **
			$menuHtml .= CHtml::encode($item['label']);

		} else {
			// ** Не выбранный пункт меню **
			if ($item['status'] == Menu::STATUS_INPROGRESS)
				$menuHtml .= CHtml::encode ($item['label']);
			else
				$menuHtml .= CHtml::link('<i></i>'.$item['label'], $item['url']);
		}

		$menuHtml .= CHtml::closeTag('li');
	}
	
	
	$menuHtml .= CHtml:: closeTag('ul');
	
	
	echo $menuHtml;
}
?>