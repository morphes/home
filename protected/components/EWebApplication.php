<?php
/**
 * Расширение CWebApplication с добавлением событий
 */
class EWebApplication extends CWebApplication
{
	public function processRequest()
	{
		if(is_array($this->catchAllRequest) && isset($this->catchAllRequest[0]))
		{
			$route=$this->catchAllRequest[0];
			foreach(array_splice($this->catchAllRequest,1) as $name=>$value)
				$_GET[$name]=$value;
		}
		else {
            $route=$this->getUrlManager()->parseUrl($this->getRequest());
        }

		if($this->hasEventHandler('onBeforeController'))
			$this->onBeforeController(new CEvent($this));

		$this->runController($route);
	}

	public function onBeforeController($event)
	{
		$this->raiseEvent('onBeforeController',$event);
	}


}
