<?php

/**
 * @author AlexSh
 */
class SeoUrlRule extends EBaseUrlRule
{
        public function createUrl($manager, $route, $params, $ampersand)
        {
		return false;
        }

        public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
        {
		$subdomain = $this->getSubdomain();
		if ($subdomain===null)
			$subdomain = '';

		// убираем дублирование страниц типа ?page=1
		if ( !$request->getIsAjaxRequest() && isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'],'GET')  ) {
			if ( isset($_GET['page']) && $_GET['page']==1 ) {
				$uri = $request->getRequestUri();
				if (count($_GET)==1) {
					$pattern = '/(\?page=1\&|\?page=1)/i';
				} else {
					$pattern = '/(page=1\&|page=1)/i';
				}
				$uri = $request->getHostInfo().preg_replace($pattern, '', $uri, 1);
				$request->redirect($uri, true, 301);
			}
		}

		$md5 = md5( $subdomain.'|'.trim( urldecode($request->getRequestUri()), ' /') );
		$model=SeoRewrite::model()->findByPk($md5, 'status=:st', array(':st'=>SeoRewrite::STATUS_ACTIVE));
		if ($model===null)
			return false;
		$params = unserialize($model->param);
		if (isset($params['cache'])) {
			Cache::applyCacheInfo($params['cache']);
			unset($params['cache']);
		}

		if ($params)
			$_GET = array_merge($_GET, $params);

		return $model->path;
        }

}