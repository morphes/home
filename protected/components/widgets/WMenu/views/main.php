<?php
/**
 * Вьюха выводит главное меню. Ту часть, которая выводится на красной плашке.
 * Выводится только первый уровень.
 */
?>

<?php
if ( ! empty($menu))
{
	$menuHtml = '';

	$menuHtml .= CHtml::openTag('ul', array('id' => 'nav'));


	
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

		if ($i == $ci - 1)
			$class[] = 'last';

		$menuHtml .= CHtml::openTag('li', array('class' => implode(' ', $class) ));

		if ($item['selected'] == true) {
			// ** Выбранный пункт меню **

			if ($item['active_link'] == true) {
				$menuHtml .= CHtml::tag('strong', array(), CHtml::link($item['label'], $item['url']), true);
			} else {
				$menuHtml .= CHtml::tag('strong', array(), $item['label'], true);
			}

		} else {
			// ** Не выбранный пункт меню **

			if ($item['status'] == Menu::STATUS_INPROGRESS) {
				$menuHtml .= CHtml::encode($item['label']);

				$menuHtml .= CHtml::openTag('div', array('class' => 'menu-hint'));
				$menuHtml .= CHtml::tag('i', array(), '', true);
				$menuHtml .= CHtml::tag('p', array('class' => 'title'), 'Раздел в разработке', true);
				$menuHtml .= CHtml::tag('p', array(), $item['no_active_text'], true);
				$menuHtml .= CHtml::closeTag('div');

			} else {
				$menuHtml .= CHtml::link($item['label'], $item['url']);
			}
		}

		$menuHtml .= CHtml::closeTag('li');
	}
	
	
	$menuHtml .= CHtml:: closeTag('ul');
	
	
	echo $menuHtml;
}
?>
