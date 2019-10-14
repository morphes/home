<?php

class HelpController extends FrontController
{
	/**
	 * Главная страница раздела помощь
	 * @param int $base
	 */
	public function actionIndex($base = Help::BASE_USER)
	{
		if ( empty(Help::$baseNames[$base]) )
			throw new CHttpException(404);

		$faqs = HelpFaq::model()->findAllByAttributes(array('base_path_id'=>$base, 'status'=>HelpFaq::STATUS_OPEN));

		$this->render('index', array(
			'faqs' => $faqs,
			'baseId' => $base,
		));
	}

	/**
	 * Вывод статьи на фронт
	 * @throws CHttpException
	 */
	public function actionArticle()
	{
		$articleId = intval( Yii::app()->getRequest()->getParam('article_id') );
		$baseId = Cache::getInstance()->baseId;

		if (empty($articleId) || empty(Help::$baseNames[$baseId]))
			throw new CHttpException(404);

		/** @var $article HelpArticle */
		$article = HelpArticle::model()->findByPk($articleId, 'status=:st', array(':st'=>HelpArticle::STATUS_OPEN));
		if (is_null($article))
			throw new CHttpException(404);

		/** @var $section HelpSection */
		$section = HelpSection::model()->findByPk($article->section_id, 'status=:st AND base_path_id=:bid', array(':st'=>HelpSection::STATUS_OPEN, ':bid'=>$baseId));
		if ( is_null($section) )
			throw new CHttpException(404);

		$chapters = HelpChapter::model()->findAllByAttributes(array('article_id'=>$article->id, 'status'=>HelpChapter::STATUS_OPEN), array('order'=>'position ASC'));

		$this->render('article', array(
			'article' => $article,
			'section' => $section,
			'chapters' => $chapters,
		));
	}

}
