<?php

/**
 * @brief This widget render a few stars.
 */
class WMenu extends CWidget
{
	/**
	 *
	 * @var integer ID типа меню, зашитый в виде констант в модели Menu.
	 */
	public $typeMenu = null;
	
	/**
	 *
	 * @var string Ключ пункта меню, который должен быть активным.
	 */
	public $activeKey = null;
	
	/**
	 * Флаг обозначающий, что активный пункт будет выглядеть,
	 * как выделенный, но иметь ссылку.
	 * @var boolean 
	 */
	public $activeLink = false;
	
	/**
	 * Флаг обозначающий, что активными ссылками (смотри $activeLink)
	 * нужно отметить только родителей.
	 * @var boolean
	 */
	public $activeLinkOnlyParent = false;
	
	/**
	 *
	 * @var integer Номер уровня меню, который должен быть отображен
	 */
	public $showLevel = 1;
	
	/**
	 *
	 * @var string Имя представления для рендеринга меню
	 */
	public $viewName = null;
	
	/**
	 *
	 * @var string Текст, показывающийся вместо меню, если нечего отображать.
	 */
	public $forEmptyMenu = 'Интернет-помощник по благоустройству дома. Представить. Выбрать. Воплотить';
       
	/**
	 * Принудительное использование текста заменителя
	 * @var type 
	 */
	public $useEmptyMenu = false;

	/**
	 *
	 * @var array Массив ключей всех родительских для пункта $activeKey
	 */
	private $_parentPath = array();
	
	public function init()
	{
		if (is_null($this->typeMenu))
			throw new CException(__CLASS__ . ': Не указан тип меню');
		
		if (is_null($this->viewName))
			throw new CException(__CLASS__ . ': Не указано имя представления');
	}

	public function run()
	{
		// Формируем ключ для кэша, в котором лежат все наши данные.
		$memcacheKey =	get_class($this)
				.'_'.$this->typeMenu
				.'_'.$this->viewName
				.'_'.$this->showLevel
				.'_'.$this->activeKey
				.'_'.$this->activeLink
				.'_'.$this->activeLinkOnlyParent
				.'_'.$this->useEmptyMenu;
		
		$data = Yii::app()->cache->get($memcacheKey);
		
		$updateTime = Yii::app()->redis->get(Menu::MENU_FRONTEND_REDIS_KEY);

		if (! $data || $data['dependValue'] < $updateTime) {

			// Строим массив ID родителей $activeKey пункта
			$model = Menu::model()->findByAttributes(array(
				'type_id' => $this->typeMenu,
				'key' => $this->activeKey
			));
			$this->_parentPath = $this->_buildParentPath(array(), $model);

			// Получаем пунктоы меню
			$menu = $this->_getMenuMain();


			$data['data'] = $this->render(
				$this->viewName,
				array('menu' => $menu, 'useEmptyMenu'=>  $this->useEmptyMenu),
				true
			);
			$data['dependValue'] = time();

			Yii::app()->cache->set($memcacheKey, $data, Cache::DURATION_MENU);
		}
		
		echo $data['data'];
	}
	
	/**
	 * Рекурсивная функция.
	 * Получает список ID всех родителей пункта $activeKey
	 * 
	 * @param array $path Массив ID родителей
	 * @param array $model
	 * @return array  
	 */
	protected function _buildParentPath($path = array(), $model = null)
	{
		if ($model && $model->parent_id > 0) {
			$path[] = $model->parent_id;
			$path = $this->_buildParentPath($path, Menu::model()->findByPk($model->parent_id));
		}
		
		return $path;
	}

	/**
	 * Рекурсивная функция.
	 * Получает пункты меню тех уровней, которые есть в списке родителей $activeKey
	 * и потомков самого $activeKey
	 * 
	 * @param type $result
	 * @param type $parent_id
	 * @param type $level
	 * @return type 
	 */
	protected function _getMenuMain(&$result = array(), $parent_id = 0, $level = 1)
	{
		// TODO: Переписать на построитель запросов
		$mainMenu = Menu::model()->findAllByAttributes(array(
			'type_id'	=> $this->typeMenu,
			'parent_id'	=> $parent_id,
			'status'	=> array(Menu::STATUS_ACTIVE, Menu::STATUS_INPROGRESS)
		), array('order' => 'position ASC'));
	
		if ($mainMenu) {
			foreach($mainMenu as $item) {
				
				$isEquals = (($this->activeKey == $item->key) || (in_array($item->id, $this->_parentPath)));

				$isActiveLink = $this->activeLink;
				
				if ($this->activeLink && $this->activeLinkOnlyParent) {
					if ($item->key == $this->activeKey)
						$isActiveLink = false;
					else
						$isActiveLink = true;
				}
				
				$result[] = array(
					'id'		=> $item->id,
					'key'		=> $item->key,
					'label'		=> ($item->label_hidden)
							   ? $item->label.'<span class="-text-block"> '.$item->label_hidden.'</span>'
							   : $item->label,
					'level'		=> $level,
					'url'		=> $item->url,
					'status'	=> $item->status,
					'selected'	=> $isEquals,
					'active_link'	=> $isActiveLink,
					'no_active_text'=> $item->no_active_text
				);
				
				if ($isEquals)
					$result = $this->_getMenuMain ($result, $item->id, $level+1);
			}
		}

		
		return $result;
	}

}

?>
