<?php

/**
 * Виджет выводит название опции и ее значение для сокращенной карточки товара
 */
class CatFilter extends CWidget
{
        public $category;
        public $selected;
        private $_options;
	public $city;
	public $viewType=1;
	public $enableStyleColorFilter = false;

	public $viewName = 'filter';

        public function init()
        {
		/**
		 * Получение опций сокращенной карточки товара с типами и значениями опций для текущего товара ($this->model)
		 */

                if ($this->category instanceof Category) {

			$this->_options = Yii::app()->dbcatalog2->createCommand()->select('id, type_id, group_id, key, name, param')
				->from('cat_option')->order('position')
				->where('category_id=:cid and forminimized=1', array(':cid'=>$this->category->id))->queryAll();
		}
        }

        public function run()
        {
                $this->render($this->viewName, array(
			'category' => $this->category,
			'options'  => $this->_options,
			'selected' => $this->selected,
			'city'     => $this->city,
			'viewType' => $this->viewType,
			'enableStyleColorFilter' => $this->enableStyleColorFilter,
                ));
        }

}