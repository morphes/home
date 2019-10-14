<?php

/**
 * @brief Виджет просмотра изображений в виде плеера
 */
class WGalleryPlayer extends CWidget
{
	/**
	 * @var object Array of models which we want to show in gallery slider.
	 */
	public $arrModels = null;
	public $model; // заплатка для архитектур
	
	/**
	 * Режим, включающий дополнительные ссылки для проверки авторства изображений.
	 * @var type 
	 */
	public $authorMode = false;
	
	
        public function init()
        {
		Yii::app()->clientScript->registerScriptFile('/js/fancybox.js');
		Yii::app()->clientScript->registerCssFile('/css/fancybox.css');
        }

        public function run()
        {
		if (empty($this->arrModels) || !is_array($this->arrModels))
			return true;
		
		Yii::app()->controller->renderPartial('//widget/galleryPlayer', array(
			'arrModels' => $this->arrModels,
			'authorMode' => $this->authorMode,
			'model' => $this->model,
		));
        }

}
