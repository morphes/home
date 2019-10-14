<?php

/**
 * @brief This widget render a few stars.
 */
class WSeoMetaTags extends CWidget
{
	// Значения по-умолчанию
	public $defaultPageTitle;
	public $defaultDescription;
	public $defaultKeywords;
	public $defaultH1;

	public $renderH1 = false;

	private $_pathInfoCrc;
	private $_requestUriCrc;
	private $_h1;

	private $_model;

	/**
	 * @var bool Флаг означающий, что UI уже был отрендерен
	 */
	private static $_uiRendered = false;


        public function init()
	{
		Yii::import('application.models.SeoMetaTag');

		// Получаем контрольную суммы от базового url (без параметров)
		$this->_pathInfoCrc = crc32(trim(Yii::app()->request->pathInfo, ' /'));
		// Получаем контрольную сумму от всего uri (вместе с параметрами)
		$this->_requestUriCrc = crc32(trim(Yii::app()->request->requestUri, ' /'));
	}

	public function run()
	{
		/** @var $this->_model SeoMetaTag */
		$this->_model = SeoMetaTag::model()->findByAttributes(array('url_crc32' => $this->_pathInfoCrc));
		if ( ! $this->_model)
			$this->_model = SeoMetaTag::model()->findByAttributes(array('url_crc32' => $this->_requestUriCrc));


		/*
		 * Если есть значения по-умолчанию, то присваиваем их.
		 * Затем, если будет найдено соответствие по url в базе, и поля будут не пустые,
		 * то эти значения перетрутся.
		 */
		if ($this->defaultPageTitle)
			$this->owner->pageTitle = $this->defaultPageTitle;

		if ($this->defaultDescription)
			$this->owner->description = $this->defaultDescription;

		if ($this->defaultKeywords)
			$this->owner->keywords = $this->defaultKeywords;

		if ($this->defaultH1)
			$this->_h1 = $this->defaultH1;

		// Рендерим Юзер Интерфейс
		$this->renderUI($this->_model);


		if ($this->renderH1 == true)
			$this->renderH1($this->_model);
		else
			$this->setMetaTags($this->_model);
	}

	/**
	 * Устанавливает значения title, description, keywords в переменных
	 * того объекта, в рамках которого вызван виджет.
	 *
	 */
	private function setMetaTags()
	{
		// Найдено совпадение в БД по URL
		if ($this->_model)
		{
			if ($this->_model->page_title)
				$this->owner->pageTitle = $this->_model->page_title;

			if ($this->_model->description)
				$this->owner->description = $this->_model->description;

			if ($this->_model->keywords)
				$this->owner->keywords = $this->_model->keywords;
		}
	}

	/**
	 * Рендерит строку для заголовка H1
	 *
	 */
	private function renderH1()
	{
		if ($this->_model) {
			if ($this->_model->h1)
				$this->_h1 = $this->_model->h1;
		}

		echo $this->_h1;
	}

	/**
	 * Рендерит пользовательский интерефейс для фронта.
	 */
	private function renderUI()
	{
		if ( ! in_array(Yii::app()->user->role, array(User::ROLE_POWERADMIN, User::ROLE_ADMIN, User::ROLE_SEO)))
			return;

		if (self::$_uiRendered)
			return;

		self::$_uiRendered = true;

		Cache::getInstance()->wSeoMetaTag = $this->render('control', array(
			'model' => $this->_model
		), true);
	}

	public function getH1()
	{
		if ($this->_model) {
			if ($this->_model->h1)
				$this->_h1 = $this->_model->h1;
		}
		return $this->_h1;
	}
}

?>