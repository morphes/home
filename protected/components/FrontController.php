<?php

/**
 * @brief Конфигурирование frontend-контроллеров
 * @author Sergey Seregin <sss@medvediza.ru>
 */
class FrontController extends Controller {
	
	/**
	 * @var booleand Флаг, при выставлении которого
	 * тег <div class="content"> внутри другого тега
	 * <div class="wrapper"> скрывается из лэйаута.
	 */
	public $hide_div_content = false;
	
	/**
	 * @var string Строка с именем класса, для дополнительного
	 * обрамляющего элемента div.
	 */
	public $spec_div_class = false;
	
	public $class_wrapper = 'wrapper-inside';


	/**
	 * @var string Содержимое этой переменной выводится в лейауте непосредственно
	 * перед выводом основного контейнера.
	 */
	public $additionalContent = '';
	
	/**
	 * 
	 * @var string Имя ключа того пункта меню, который должен быть активным.
	 */
	public $menuActiveKey = '';
	/**
	 *
	 * @var boolean Флаг обозначающий необходимость сделать
	 * выделенные пункты со ссылками (в том числе родителей активного пункта)
	 */
	public $menuIsActiveLink = false;
	/**
	 *
	 * @var boolean Флаг обозначающий, что выделенными-ссылками должны быть
	 * только родители активного пункта.
	 */
	public $menuIsActiveLinkOnlyParent = false;

	/**
	 * Флаг принудительного использования текста замены для подменю.
	 * @var type 
	 */
	public $useEmptyMenu = false;

	/**
	 * @var string $bodyClass Дополнительные классы для тега <i>body</i>
	 */
	public $bodyClass = '';

	/**
	 * @var string $htmlClass Дополнительные классы для тега <i>html</i>
	 */
	public $htmlClass = '';

	/**
	 * @var array $layoutParams Дополнительные параметры для лайаута.
	 */
	public $layoutParams = array();

	/**
	 * @var string Канонический url для страницы
	 */
	public $canonicalUrl;

	/**
	 * Переопределенный метод рендеринга лейаута. Позволяет отрендерить любое представление
	 * обрамив его другим леайтом.
	 *
	 * @param string $view Имя представления для рендеринга
	 * @param null $data параметры для пердставления $view
	 * @param bool $return Флаг типа возварата результата (echo или return)
	 * @param array $wrapper Массив с настройками для обрамляющего лейаута <br>
	 	0 элемент - Имя лейаута, который нужно сделать обрамляющим
	 * 	1 элемент - Ассоциативный массив параметров для обрамляющего лейата
	 * @return string
	 */
	public function render($view,$data=null,$return=false, $wrapper = array())
	{

		if($this->beforeRender($view))
		{
			$output=$this->renderPartial($view,$data,true);

			// Если указан дополнительный лайоут для рендеринга
			if (isset($wrapper[0]) && is_string($wrapper[0]) &&  ($userLayoutFile = $this->getLayoutFile('webroot.themes.myhome.views.layouts.'.$wrapper[0])) !== false)
			{
				// Если есть параметры для встроенного лейаута передаем их в лейаут
				$params =  (isset($wrapper[1]) && is_array($wrapper[1]))
					   ? $wrapper[1]
					   : array();

				$output = $this->renderFile($userLayoutFile, array('content' => $output) + $params, true);
			}


			// Рендеринг обычного лейаута
			if(($layoutFile = $this->getLayoutFile($this->layout))!==false)
				$output = $this->renderFile($layoutFile,array('content'=>$output),true);


			$this->afterRender($view,$output);

			$output=$this->processOutput($output);

			if($return)
				return $output;
			else
				echo $output;
		}
	}
}