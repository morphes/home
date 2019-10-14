<?php
/**
 * Вьюха выводит главное подменю. То которое под красной плашкой.
 */
?>

<?php
if ( ! empty($menu))
{
	$menuHtml = '';

	$menuHtml .= CHtml::openTag('ul', array('class' => '-menu-inline -submenu'));

	// Флаг отмечающий, что вывели какие-то данные
	$showedSomething = false;

	foreach ($menu as $key=> $item) {
		if ($item['level'] != $this->showLevel)
			unset ($menu[$key]);
	}

	foreach ($menu as $key=>$item)
	{
		$showedSomething = true;

		$menuHtml .= CHtml::openTag('li');

		if ($item['selected'] == true) {
			// ** Выбранный пункт меню **

			if ($item['active_link'] == true) {
				$menuHtml .= CHtml::link(
					$item['label'],
					$item['url'],
					array('class' => '-submenu-active')
				);
			} else {
				$menuHtml .= CHtml::link(
					$item['label'],
					$item['url'],
					array('class' => '-submenu-active')
				);
			}

		} else {
			// ** Не выбранный пункт меню **
			$menuHtml .= CHtml::link($item['label'], $item['url']);
		}

		$menuHtml .= CHtml::closeTag('li');
	}

	$menuHtml .= CHtml:: closeTag('ul');
}

/*
 * Если есть какие-то пункты меню для отображения, показываем их.
 */
if ($showedSomething && !$useEmptyMenu) {
	echo $menuHtml;
}
