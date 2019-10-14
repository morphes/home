<?php

class CatalogModule extends CWebModule
{
        private $_assetsUrl;

	public function init()
	{
                $this->defaultController = 'default';

		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'catalog.models.*',
			'catalog.components.*',
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}

        /**
         * return the URL for this module's assets, performing the publish operation
         * the first time, and caching the result for subsequent use.
         *
         * @return string
         */
        public function getAssetsUrl()
        {
                if ($this->_assetsUrl === null)
                        $this->_assetsUrl = Yii::app()->getAssetManager()->publish(
                                Yii::getPathOfAlias('idea.assets'));
                return $this->_assetsUrl;
        }

}
