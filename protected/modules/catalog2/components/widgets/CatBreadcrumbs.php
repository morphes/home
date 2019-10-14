<?php
/**
 * Виджет для вывода хлебных крошек каталога товаров (с аякс-подгрузкой категорий)
 */
class CatBreadcrumbs extends CWidget
{
        public $category;
        public $homeLink;
        public $pageName;
	public $insideH1;
	public $afterH1;
	public $mallCatalogClass;
	public $folderListLink;
	//Показывать и описание каттегории
	public $showDesc = false;

	//Кастомизация для новой карточки товара
	public $productCard = false;

        public function init()
        {
                if(!($this->category instanceof Category))
                        throw new CHttpException(500);

        }

        public function run()
	{
		$htmlOut = '';

		$htmlOut .= CHtml::openTag('div', array('class' => '-grid'));
		$htmlOut .= CHtml::openTag('div', array('class' => '-col-5'));

		$htmlOut .= CHtml::openTag('ul', array('class' => '-menu-inline -breadcrumbs'));

		/**
		 * Вывод ссылки на главную страницу в "хлебных крошках"
		 */
		if ($this->homeLink === null) {
			$htmlOut .= '<li>' . CHtml::link(Yii::t('zii', 'Home'), Yii::app()->homeUrl) . '</li>';
		} else if ($this->homeLink !== false) {
			$htmlOut .= '<li>' . $this->homeLink . '</li>';
		}

		/**
		 * root категория
		 */
		$root = Category::getRoot();

		/**
		 * Кастомизация root категории
		 */
		if ($this->homeLink === null) {
			$ancestors[0] = array(
				'name' => 'Товары',
				'url'  => Yii::app()->createUrl('/products'),
				'id'   => $root->id
			);
		}

		/*
		 * Добавление текущей категории в список "хлебных крошек"
		 */
		$ancestors[] = array(
			'id'   => $this->category->id,
			'name' => $this->category->name
		);

		/* -------------------------------------------------------------
		 *  Вывод категорий от root до текущей
		 * -------------------------------------------------------------
		 */
		foreach ($ancestors as $cat) {
			/*
			 * Если выводимая категория называется root - пропускаем ее
			 */
			if ($cat['name'] == $root->name) {
				continue;
			}

			if (!isset($cat['url'])) {
				$cat['url'] = Category::getLink($cat['id']);
			}

			$htmlOut .= '<li class="parent">'
				. CHtml::link($cat['name'], $cat['url'])
				. '<i data-id="' . $cat['id'] . '" class="-icon-toggle-down -icon-only -gutter-left-qr"></i>'
				. '</li>';
		}

		//Небольшой костыль для вывода ссылки на список папок
		//В списке товаров папки
		//В будуще зарефакторить
		if (isset($this->folderListLink)) {
			$htmlOut .= '<li class="parent">'
				. $this->folderListLink
				. '</li>';
		}



		$htmlOut .= CHtml::closeTag('ul');
		$htmlOut .= CHtml::closeTag('div'); // EOF <div class="-col-5">

		$htmlOut .= $this->afterH1;

		$htmlOut .= CHtml::closeTag('div'); // EOF <div class="-grid">


		/* -------------------------------------------------------------
		 *  Здесь выводится элемент выбора города
		 * -------------------------------------------------------------
		 */



		/* -------------------------------------------------------------
		 *  Заголовок страницы
		 * -------------------------------------------------------------
		 */
		$htmlOut .= CHtml::openTag('div', array('class' => '-grid'));

		if(isset($this->mallCatalogClass))
		{
			$htmlOut .= CHtml::openTag('div', array('class' => '-col-9'));
		}
		elseif($this->productCard) {
			$htmlOut .= CHtml::openTag('div', array('class' => '-col-10 -pass-2'));
		}
		else {
			$htmlOut .= CHtml::openTag('div', array('class' => '-col-9 meta-tags -gutter-bottom-dbl'));
		}


		$htmlOut .= CHtml::tag('h1', array('class' => '-inline'), $this->pageName);
		$htmlOut .= $this->insideH1;

		if($this->showDesc && $this->category->seo_top_desc)
		{
			$htmlOut .= CHtml::tag('p', array(), $this->category->seo_top_desc);
		}

		$htmlOut .= CHtml::closeTag('div');
		$htmlOut .= CHtml::closeTag('div');

		$htmlOut .= '<script>
			catalog.initBreadCrumbs();
		</script>';

		echo $htmlOut;
	}
}
