<?php

class SearchController extends FrontController
{
	/**
	 * Главная страница раздела помощь
	 * @param int $base
	 */
	public function actionIndex()
	{
		$base = intval( Cache::getInstance()->baseId );
		$query = Yii::app()->getRequest()->getParam('query', '');
		if ( empty(Help::$baseNames[$base]) )
			throw new CHttpException(404);

		$sphinxClient = Yii::app()->search;
                $sphinxQuery = $sphinxClient->EscapeString($query);

		$params = array(
			'index' => 'help',
			'modelClass'	=> 'HelpArticle',
			'filters'	=> array( 'article_status' => HelpArticle::STATUS_OPEN, 'chapter_status' => HelpChapter::STATUS_OPEN, 'bid'=>$base ),
			'query'		=> $sphinxQuery.'*',
			'group' => array('field'=>'article_id','mode'=>SPH_GROUPBY_ATTR, 'order'=>'@weight DESC'),
			'pagination' => array('pageSize' => 100),

		);
		$dataProvider = new CSphinxDataProvider($sphinxClient, $params);

		$this->render('index', array(
			'dataProvider' => $dataProvider,
			'baseId' => $base,
			'query' => $query,
		));
	}

}
