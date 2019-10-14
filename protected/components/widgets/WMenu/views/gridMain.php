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

	$menuHtml .= CHtml::openTag('ul', array('id' => 'nav', 'class' => '-menu-inline -menu-main'));


	
	for ($i = 0, $ci = count($menu); $i < $ci; $i++ )
	{
		$item = $menu[$i];
		
		$class = array();

		if ($item['level'] != $this->showLevel)
			continue;

		if ($item['status'] == Menu::STATUS_INPROGRESS)
			$class[] = 'secondly';


		// Рендерим подменю
		$subMenu = '';

		if ($item['key'] == 'journal') {
			$subMenu .= $this->widget('application.components.widgets.WMenu.WMenu', array(
				'typeMenu'  => Menu::TYPE_MAIN,
				'viewName'  => 'gridMainSub',
				'showLevel' => 2,
				'activeKey' => ($item['selected'] == true)
			                       ? $this->activeKey
			                       : $item['key'],
				'activeLink'=> true,
				'activeLinkOnlyParent'=> true,
			), true);
		}

		// Если есть подменю, тег <li> метим для применения стилей
		if ($subMenu != '') {
			$class[] = '-toggle-submenu';
		}

		$menuHtml .= CHtml::openTag('li', array('class' => implode(' ', $class) ));

		// Классы для ссылки пункта меню
		$linkClass = array();

		// Если есть подменю, метим ссылка пункта.
		if ($subMenu != '') {
			$linkClass[] = '-icon-arrow-down';
			$linkClass[] = '-icon-pull-right';
		}

		if ($item['selected'] == true) {
			// ** Выбранный пункт меню **

			$linkClass[] = 'current';

			if ($item['active_link'] == true) {
				$menuHtml .= CHtml::link(
					$item['label'],
					$item['url'],
					array('class' => implode(' ', $linkClass))
				);
			} else {
				$menuHtml .= CHtml::link(
					$item['label'],
					$item['url'],
					array('class' => implode(' ', $linkClass))
				);
			}

		} else {
			// ** Не выбранный пункт меню **
			$menuHtml .= CHtml::link(
				$item['label'],
				$item['url'],
				array('class' => implode(' ', $linkClass))
			);
		}



		/* Если подменю не пустое, добавляем его вложенным в тег <li>
		основного пункта */
		$menuHtml .= $subMenu;



		$menuHtml .= CHtml::closeTag('li');
	}
	
	
	$menuHtml .= CHtml:: closeTag('ul');
	
	
	echo $menuHtml;
}
?>