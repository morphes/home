<?php

/**
 * Новостные ленты сайта
 * @author Roman Kuzakov
 */
class FeedController extends FrontController
{
	/**
	 * Лента журнала для раздела «Знания» в формате RSS-2.0
	 */
	public function actionRss2JournalKnowledge($yandex = 'false', $rambler = 'false')
	{
		/**
		 * Подключение feed extension
		 */
		Yii::import('ext.feed.*');
		Yii::import('application.modules.media.models.MediaKnowledge');


		/**
		 * Получение кешированной ленты
		 */
		$cacheName = 'feed_media_knowledge_20';
		if ($yandex == 'true')
			$cacheName = $cacheName.'_yandex';
		if ($rambler == 'true')
			$cacheName = $cacheName.'_rambler';

		$feed = Yii::app()->cache->get($cacheName);

		/**
		 * Генерация закешированной ленты в случае ее наличия в memcache
		 */
		if ($feed) {
			$feed->generateFeed();
			Yii::app()->end();
		}

		/*
		 * Создание ленты, настройка общих параметров
		 */
		$feed = new EFeed();

		// Корневой атрибут для ЯНДЕКСА
		if ($yandex == 'true')
			$feed->setRssAttributes(array(
				'xmlns:yandex' => "http://news.yandex.ru",
				'xmlns:media'  => "http://search.yahoo.com/mrss/",
				'version'      => "2.0"
			));

		// Корневой атрибут для РАМБЛЕРА
		if ($rambler == 'true')
			$feed->setRssAttributes(array(
				'xmlns:rambler' => "http://news.rambler.ru",
				'version'       => "2.0"
			));

		$feed->title = 'MyHome.ru — Журнал — Знания';
		$feed->description = 'Список знаний из раздела «Журнал»';


		$feed->setImage('Журнал — Знания', Yii::app()->homeUrl, 'http://www.myhome.ru/img/oauth-logo.png');


		$feed->addChannelTag('language', 'ru');
		$feed->addChannelTag('pubDate', date(DATE_RSS, time()));
		$feed->addChannelTag('link', Yii::app()->homeUrl);



		/**
		 * Вставка выбранных новостей в ленту
		 */
		if ($yandex == 'true') {
			$knowledges = MediaKnowledge::model()->published()->only_rss()->findAll(array('limit' => 20));
		} else {
			$knowledges = MediaKnowledge::model()->published()->findAll(array('limit' => 100));
		}

		foreach ($knowledges as $model)
		{
			$item = $feed->createNewItem();
			$item->title = $model->title;
			$item->link = $this->createAbsoluteUrl($model->getElementLink());
			$item->date = $model->public_time;
			$item->description = Amputate::getLimb(strip_tags($model->content), 400);
			$item->addTag('author', $model->author->name);

			if ($yandex == 'true')
				$item->addTag('yandex:full-text', strip_tags($model->content));

			if ($rambler == 'true')
				$item->addTag('rambler:fulltext', $model->content);

			$src = $model->preview->getPreviewName(MediaKnowledge::$preview['width_700']);
			$item->setEncloser(Yii::app()->homeUrl.'/'.$src, filesize($src), 'image/jpeg');

			$feed->addItem($item);
		}


		/**
		 * Кеширование сгенерированной ленты на 10 минут
		 */
		Yii::app()->cache->set($cacheName, $feed, 10 * 60);

		if ($knowledges)
			$feed->generateFeed();

		Yii::app()->end();
	}


	/**
	 * Лента «Новости» в формате RSS-2.0
	 */
	public function actionRss2JournalNews($yandex = 'false', $rambler = 'false')
	{
		/**
		 * Подключение feed extension
		 */
		Yii::import('ext.feed.*');
		Yii::import('application.modules.media.models.MediaNew');


		/**
		 * Получение кешированной ленты
		 */
		$cacheName = 'feed_media_news_20';
		if ($yandex == 'true')
			$cacheName = $cacheName.'_yandex';
		if ($rambler == 'true')
			$cacheName = $cacheName.'_rambler';


		$feed = Yii::app()->cache->get($cacheName);

		/**
		 * Генерация закешированной ленты в случае ее наличия в memcache
		 */
		if ($feed) {
			$feed->generateFeed();
			Yii::app()->end();
		}

		/*
		 * Создание ленты, настройка общих параметров
		 */
		$feed = new EFeed();

		// Корневой атрибут для ЯНДЕКСА
		if ($yandex == 'true')
			$feed->setRssAttributes(array(
				'xmlns:yandex' => "http://news.yandex.ru",
				'xmlns:media'  => "http://search.yahoo.com/mrss/",
				'version'      => "2.0"
			));
		// Корневой атрибут для РАМБЛЕРА
		if ($rambler == 'true')
			$feed->setRssAttributes(array(
				'xmlns:rambler' => "http://news.rambler.ru",
				'version'       => "2.0"
			));

		$feed->title = 'MyHome.ru — Журнал — Новости';
		$feed->description = 'Список новостей из раздела «Журнал»';


		$feed->setImage('Журнал — Новости', Yii::app()->homeUrl, 'http://www.myhome.ru/img/oauth-logo.png');


		$feed->addChannelTag('language', 'ru');
		$feed->addChannelTag('pubDate', date(DATE_RSS, time()));
		$feed->addChannelTag('link', Yii::app()->homeUrl);

		/*
		 * получение элементов для вывода в ленту. Создание элементов.
		 */

		$criteria = new CDbCriteria();
		$criteria->condition = 'status=:status AND public_time<=:time';
		$criteria->order = 'public_time DESC';
		$criteria->params = array(':status' => MediaNew::STATUS_PUBLIC, ':time' => time());
		$criteria->limit = 100;

		/**
		 * Вставка выбранных новостей в ленту
		 */

		if ($yandex == 'true') {
			$criteria->limit = 20;
			$news = MediaNew::model()->only_rss()->findAll($criteria);
		} else {
			$news = MediaNew::model()->findAll($criteria);
		}

		/** @var $model MediaNew */
		foreach ($news as $model)
		{
			/** @var $item EFeedItemRSS2 */
			$item = $feed->createNewItem();
			$item->title = $model->title;
			$item->link = $this->createAbsoluteUrl($model->getElementLink());
			$item->date = $model->public_time;
			$item->description = Amputate::getLimb(strip_tags($model->content), 400);

			if ($yandex == 'true')
				$item->addTag('yandex:full-text', strip_tags($model->content));

			if ($rambler == 'true')
				$item->addTag('rambler:fulltext', strip_tags($model->content));

			$src = $model->preview->getPreviewName(MediaNew::$preview['width_700']);
			$item->setEncloser(Yii::app()->homeUrl.'/'.$src, filesize($src), 'image/jpeg');

			$feed->addItem($item);
		}

		/**
		 * Кеширование сгенерированной ленты на 10 минут
		 */
		Yii::app()->cache->set($cacheName, $feed, 10 * 60);

		if ($news)
			$feed->generateFeed();

		Yii::app()->end();
	}



	/**
	 * Лента «Журнал / Новости» разбитая по отдельным тематикам в формате RSS-2.0
	 */
	public function actionRss2JournalNewsGroup($theme_id = 0, $yandex = 'false')
	{
		/**
		 * Подключение feed extension
		 */
		Yii::import('ext.feed.*');
		Yii::import('application.modules.media.models.MediaNew');
		Yii::import('application.modules.media.models.MediaTheme');


		$theme = MediaTheme::model()->findByPk((int)$theme_id);

		if ( ! $theme)
			throw new CHttpException(404);


		/**
		 * Получение кешированной ленты
		 */
		$cacheName = 'feed_media_news_group'.$theme->id.'_20';
		if ($yandex == 'true')
			$cacheName = $cacheName.'_yandex';


		$feed = Yii::app()->cache->get($cacheName);

		/**
		 * Генерация закешированной ленты в случае ее наличия в memcache
		 */
		if ($feed) {
			$feed->generateFeed();
			Yii::app()->end();
		}


		/*
		 * Создание ленты, настройка общих параметров
		 */
		$feed = new EFeed();

		// Корневой атрибут для ЯНДЕКСА
		if ($yandex == 'true')
			$feed->setRssAttributes(array(
				'xmlns:yandex' => "http://news.yandex.ru",
				'xmlns:media'  => "http://search.yahoo.com/mrss/",
				'version'      => "2.0"
			));

		$feed->title = 'MyHome.ru — Журнал — Новости по теме '.$theme->name;
		$feed->description = 'Список новостей из раздела «Журнал»';


		$feed->setImage('Журнал — Новости по теме '.$theme->name, Yii::app()->homeUrl, 'http://www.myhome.ru/img/oauth-logo.png');


		$feed->addChannelTag('language', 'ru');
		$feed->addChannelTag('pubDate', date(DATE_RSS, time()));
		$feed->addChannelTag('link', Yii::app()->homeUrl);

		/*
		 * получение элементов для вывода в ленту. Создание элементов.
		 */

		$criteria = new CDbCriteria();
		$criteria->condition = 'status=:status AND public_time<=:time';


		// Фильтрация по ТЕМАТИКАМ

		$criteria->join = 'LEFT JOIN media_theme_select mts ON mts.model_id = id';

		$criteria->condition = $criteria->condition . ' AND mts.theme_id in (:themeId) AND mts.model = :model';

		$criteria->order = 'public_time DESC';
		$criteria->params = array(
			':status'  => MediaNew::STATUS_PUBLIC,
			':time'    => time(),
			':themeId' => $theme->id,
			':model'   => 'MediaNew'
		);
		$criteria->limit = 100;

		/**
		 * Вставка выбранных новостей в ленту
		 */
		if ($yandex == 'true') {
			$criteria->limit = 20;
			$news = MediaNew::model()->only_rss()->findAll($criteria);
		} else {
			$news = MediaNew::model()->findAll($criteria);
		}

		foreach ($news as $model)
		{
			$item = $feed->createNewItem();
			$item->title = $model->title;
			$item->link = $this->createAbsoluteUrl($model->getElementLink());
			$item->date = $model->public_time;
			$item->description = Amputate::getLimb(strip_tags($model->content), 400);

			if ($yandex == 'true')
				$item->addTag('yandex:full-text', strip_tags($model->content));

			$feed->addItem($item);
		}


		/**
		 * Кеширование сгенерированной ленты на 10 минут
		 */
		Yii::app()->cache->set($cacheName, $feed, 10 * 60);

		if ($news)
			$feed->generateFeed();

		Yii::app()->end();
	}


	/**
	 * Лента «Журнал / Знания» разбитая по отдельным тематикам в формате RSS-2.0
	 */
	public function actionRss2JournalKnowledgeGroup($theme_id = 0, $yandex = 'false')
	{
		/**
		 * Подключение feed extension
		 */
		Yii::import('ext.feed.*');
		Yii::import('application.modules.media.models.MediaKnowledge');
		Yii::import('application.modules.media.models.MediaTheme');


		$theme = MediaTheme::model()->findByPk((int)$theme_id);

		if ( ! $theme)
			throw new CHttpException(404);


		/**
		 * Получение кешированной ленты
		 */
		$cacheName = 'feed_media_knowledge_group'.$theme->id.'_20';
		if ($yandex == 'true')
			$cacheName = $cacheName.'_yandex';


		$feed = Yii::app()->cache->get($cacheName);

		/**
		 * Генерация закешированной ленты в случае ее наличия в memcache
		 */
		if ($feed) {
			$feed->generateFeed();
			Yii::app()->end();
		}


		/*
		 * Создание ленты, настройка общих параметров
		 */
		$feed = new EFeed();

		// Корневой атрибут для ЯНДЕКСА
		if ($yandex == 'true')
			$feed->setRssAttributes(array(
				'xmlns:yandex' => "http://news.yandex.ru",
				'xmlns:media'  => "http://search.yahoo.com/mrss/",
				'version'      => "2.0"
			));

		$feed->title = 'MyHome.ru — Журнал — Знания по теме '.$theme->name;
		$feed->description = 'Список знаний из раздела «Журнал»';


		$feed->setImage('Журнал — Знания по теме '.$theme->name, Yii::app()->homeUrl, 'http://www.myhome.ru/img/oauth-logo.png');


		$feed->addChannelTag('language', 'ru');
		$feed->addChannelTag('pubDate', date(DATE_RSS, time()));
		$feed->addChannelTag('link', Yii::app()->homeUrl);

		/*
		 * получение элементов для вывода в ленту. Создание элементов.
		 */

		$criteria = new CDbCriteria();

		// Фильтрация по ТЕМАТИКАМ

		$criteria->join = 'LEFT JOIN media_theme_select mts ON mts.model_id = id';
		$criteria->condition = 'mts.theme_id in (:themeId) AND mts.model = :model';
		$criteria->params = array(':themeId' => $theme->id, ':model'   => 'MediaKnowledge');
		$criteria->limit = 100;

		/**
		 * Вставка выбранных новостей в ленту
		 */
		if ($yandex == 'true') {
			$criteria->limit = 20;
			$knowledges = MediaKnowledge::model()->published()->only_rss()->findAll($criteria);
		} else {
			$knowledges = MediaKnowledge::model()->published()->findAll($criteria);
		}

		foreach ($knowledges as $model)
		{
			$item = $feed->createNewItem();
			$item->title = $model->title;
			$item->link = $this->createAbsoluteUrl($model->getElementLink());
			$item->date = $model->public_time;
			$item->description = Amputate::getLimb(strip_tags($model->content), 400);

			if ($yandex == 'true')
				$item->addTag('yandex:full-text', strip_tags($model->content));

			$feed->addItem($item);
		}


		/**
		 * Кеширование сгенерированной ленты на 10 минут
		 */
		Yii::app()->cache->set($cacheName, $feed, 10 * 60);

		if ($knowledges)
			$feed->generateFeed();

		Yii::app()->end();
	}



	/**
	 * Лента «Журнал / Знания» разбитая по отдельным Жанрам в формате RSS-2.0
	 */
	public function actionRss2JournalKnowledgeGenre($genre_id = 0, $yandex = 'false')
	{
		/**
		 * Подключение feed extension
		 */
		Yii::import('ext.feed.*');
		Yii::import('application.modules.media.models.MediaKnowledge');
		Yii::import('application.modules.media.models.MediaTheme');


		$genre_id = (int)$genre_id;

		if ( ! array_key_exists($genre_id, MediaKnowledge::$genreNames))
			throw new CHttpException(404);

		/**
		 * Получение кешированной ленты
		 */
		$cacheName = 'feed_media_knowledge_genre'.$genre_id.'_20';
		if ($yandex == 'true')
			$cacheName = $cacheName.'_yandex';


		$feed = Yii::app()->cache->get($cacheName);

		/**
		 * Генерация закешированной ленты в случае ее наличия в memcache
		 */
		if ($feed) {
			$feed->generateFeed();
			Yii::app()->end();
		}


		/*
		 * Создание ленты, настройка общих параметров
		 */
		$feed = new EFeed();

		// Корневой атрибут для ЯНДЕКСА
		if ($yandex == 'true')
			$feed->setRssAttributes(array(
				'xmlns:yandex' => "http://news.yandex.ru",
				'xmlns:media'  => "http://search.yahoo.com/mrss/",
				'version'      => "2.0"
			));

		$feed->title = 'MyHome.ru — Журнал — Знания по жанрам '.MediaKnowledge::$genreNames[$genre_id];
		$feed->description = 'Список знаний из раздела «Журнал»';


		$feed->setImage('Журнал — Знания по жанрам '.MediaKnowledge::$genreNames[$genre_id], Yii::app()->homeUrl, 'http://www.myhome.ru/img/oauth-logo.png');


		$feed->addChannelTag('language', 'ru');
		$feed->addChannelTag('pubDate', date(DATE_RSS, time()));
		$feed->addChannelTag('link', Yii::app()->homeUrl);

		/*
		 * получение элементов для вывода в ленту. Создание элементов.
		 */

		$criteria = new CDbCriteria();
		$criteria->condition = 'genre = :genreId';
		$criteria->params = array(':genreId' => $genre_id);
		$criteria->limit = 100;

		/**
		 * Вставка выбранных новостей в ленту
		 */
		if ($yandex == 'true') {
			$criteria->limit = 20;
			$knowledges = MediaKnowledge::model()->published()->only_rss()->findAll($criteria);
		} else {
			$knowledges = MediaKnowledge::model()->published()->findAll($criteria);
		}

		foreach ($knowledges as $model)
		{
			$item = $feed->createNewItem();
			$item->title = $model->title;
			$item->link = $this->createAbsoluteUrl($model->getElementLink());
			$item->date = $model->public_time;
			$item->description = Amputate::getLimb(strip_tags($model->content), 400);

			if ($yandex == 'true')
				$item->addTag('yandex:full-text', strip_tags($model->content));

			$feed->addItem($item);
		}


		/**
		 * Кеширование сгенерированной ленты на 10 минут
		 */
		Yii::app()->cache->set($genre_id, $feed, 10 * 60);

		if ($knowledges)
			$feed->generateFeed();

		Yii::app()->end();
	}



        /**
         * Вывод ленты новостей в формате RSS 2.0
         */
        public function actionRss2()
        {
                /**
                 * Подключение feed extension
                 */
                Yii::import('ext.feed.*');

                /**
                 * Получение кешированной ленты
                 */
                $feed = Yii::app()->cache->get('feed_rss_20');
		
                /**
                 * Генерация закешированной ленты в случае ее наличия в memcache
                 */
                if ($feed) {
                        $feed->generateFeed();
                        Yii::app()->end();
                }

                /**
                 * Генерация ленты в случае отстуствия ее в memcache
                 */
                $feed = new EFeed();

                /**
                 * Базовая настрояка ленты
                 */
                $feed->title = 'MyHome.ru — новости проекта';
                $feed->addChannelTag('language', 'ru');
                $feed->addChannelTag('pubDate', date(DATE_RSS, time()));
                $feed->addChannelTag('link', 'http://www.myhome.ru/');


                /**
                 * Составление условия выборки актуальных новостей
                 */
                $criteria = new CDbCriteria();
                $criteria->condition = 'status=:status AND public_time<=:time';
                $criteria->order = 'create_time DESC';
                $criteria->params = array(':status' => News::STATUS_ACTIVE, ':time' => time());
                $criteria->limit = 100;

                /**
                 * Вставка выбранных новостей в ленту
                 */
                $news = News::model()->findAll($criteria);
                foreach ($news as $model) {
                        $item = $feed->createNewItem();
                        $item->title = $model->title;
                        $item->link = $this->createAbsoluteUrl('/content/news/view/', array('id' => $model->id));
                        $item->date = $model->public_time;
                        $item->description = Amputate::getLimb(strip_tags($model->content), 400);
                        $feed->addItem($item);
                }

                /**
                 * Кеширование сгенерированной ленты на 10 минут
                 */
                Yii::app()->cache->set('feed_rss_20', $feed, 10 * 60);
		if ($news)
			$feed->generateFeed();
                Yii::app()->end();
        }

	/**
	 * Общая лента «Журнал / Новости» в формате RSS-2.0
	 */
	public function actionRss2JournalEvents($yandex = 'false')
	{
		/**
		 * Подключение feed extension
		 */
		Yii::import('ext.feed.*');
		Yii::import('application.modules.media.models.MediaEvent');


		/**
		 * Получение кешированной ленты
		 */
		$cacheName = 'feed_media_events';
		if ($yandex == 'true')
			$cacheName = $cacheName.'_yandex';


		$feed = Yii::app()->cache->get($cacheName);

		/**
		 * Генерация закешированной ленты в случае ее наличия в memcache
		 */
		if ($feed) {
			$feed->generateFeed();
			Yii::app()->end();
		}


		/*
		 * Создание ленты, настройка общих параметров
		 */
		$feed = new EFeed();

		// Корневой атрибут для ЯНДЕКСА
		if ($yandex == 'true')
			$feed->setRssAttributes(array(
				'xmlns:yandex' => "http://news.yandex.ru",
				'xmlns:media'  => "http://search.yahoo.com/mrss/",
				'version'      => "2.0"
			));

		$feed->title = 'MyHome.ru — Журнал — Календарь событий';
		$feed->description = 'Список событий из раздела «Журнал»';


		$feed->setImage('Журнал — Календарь событий', Yii::app()->homeUrl, 'http://www.myhome.ru/img/oauth-logo.png');


		$feed->addChannelTag('language', 'ru');
		$feed->addChannelTag('pubDate', date(DATE_RSS, time()));
		$feed->addChannelTag('link', Yii::app()->homeUrl);

		/*
		 * получение элементов для вывода в ленту. Создание элементов.
		 */

		$criteria = new CDbCriteria();
		$criteria->condition = 'status=:status AND public_time<=:time';
		$criteria->order = 'public_time DESC';
		$criteria->params = array(':status' => MediaEvent::STATUS_PUBLIC, ':time' => time());
		$criteria->limit = 100;

		/**
		 * Вставка выбранных новостей в ленту
		 */
		$events = MediaEvent::model()->findAll($criteria);
		/** @var $model MediaEvent */
		foreach ($events as $model)
		{
			$item = $feed->createNewItem();
			$item->title = $model->name;
			$item->link = $this->createAbsoluteUrl($model->getElementLink());
			$item->date = $model->public_time;
			$item->description = Amputate::getLimb(strip_tags($model->content), 400);

			if ($yandex == 'true')
				$item->addTag('yandex:full-text', strip_tags($model->content));

			$feed->addItem($item);
		}


		/**
		 * Кеширование сгенерированной ленты на 10 минут
		 */
		Yii::app()->cache->set($cacheName, $feed, 10 * 60);

		if ($events)
			$feed->generateFeed();

		Yii::app()->end();
	}

	/**
	 * XML выгрузка статей для mail.ru
	 */
	public function actionXmlJournalKnowledge()
	{

		$cache = Yii::app()->cache->get('xml_knowledge');

		if ( $cache ) {
			Header('Content-type: text/xml');
			echo $cache;
			Yii::app()->end();
		}

		/**
		 * Подключение feed extension
		 */
		Yii::import('application.modules.media.models.MediaKnowledge');
		Yii::import('application.modules.media.models.MediaThemeSelect');

		$knowledges = MediaKnowledge::model()->published()->only_rss()->findAll(array('limit' => 20));

		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml.= '<articles>' . "\n";

		/**
		 * @var $model MediaKnowledge
		 */
		foreach($knowledges as $model) {
			$xml.= "\t<article id='".$model->id."'>\n";
			$xml.= "\t\t<title>" . $this->encodeStrForXml($model->title) . "</title>\n";
			$xml.= "\t\t<announce>" . $this->encodeStrForXml(strip_tags($model->lead)) . "</announce>\n";

			// замена всех img тегов в контенте статьи на
			// теги image с уникальными id, а сами атрибуты тега img
			// сохраняются для вывода в блок images (ниже)
			// где описан каждый image тег в соответствии с его id
			// сам не понял, чего написал. см. файл https://docs.google.com/viewer?a=v&pid=gmail&attid=0.1&thid=13f759ca8971242d&mt=application/vnd.openxmlformats-officedocument.wordprocessingml.document&url=https://mail.google.com/mail/u/0/?ui%3D2%26ik%3Dbf137baa78%26view%3Datt%26th%3D13f759ca8971242d%26attid%3D0.1%26disp%3Dsafe%26zw&sig=AHIEtbS9keNytqlJhvDSZlB29PQasnoG4A
			$dom = new DOMDocument('1.0', 'utf-8');
			libxml_use_internal_errors(true);
			$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $model->content);
			$dom->encoding = 'utf-8';
			$i = 1;
			$images = array();
			foreach ($dom->getElementsByTagName('img') as $embed) {
				$src= $embed->getAttribute('src');
				$alt= $embed->getAttribute('alt');
				$img= $dom->createElement('image');
				$img->setAttribute('id', $i);
				$embed->parentNode->replaceChild($img, $embed);
				$images[$i] = array('src'=>$src, 'alt'=>$alt);
				$i++;
			}
			$content = $dom->saveHTML();
			libxml_clear_errors();

			// вывод контента
			$xml.= "\t\t<content>" . $this->encodeStrForXml(str_replace('&nbsp;', '&#xA0;', strip_tags($content, '<image>'))) . "</content>\n";

			// добавление в массив изображений главной картинки статьи
			if ($model->preview) {
				$main_image_url = $this->encodeStrForXml(Yii::app()->createAbsoluteUrl('/' . $model->preview->getPreviewName(MediaKnowledge::$preview['width_700'])));
				$images[] = array('src'=>$main_image_url, 'alt'=>'', 'is_main'=>true);
			}

			// начало формирования массива изображений
			$xml.= "\t\t<images>\n";
			foreach ($images as $i=>$image) {
				$alt = $image['alt'];
				$src = $this->encodeStrForXml($image['src']);

				if (isset($image['is_main']))
					$xml.= "\t\t\t<image id='{$i}' description='{$alt}' url='{$src}' is_main='true' />\n";
				else
					$xml.= "\t\t\t<image id='{$i}' description='{$alt}' url='{$src}' />\n";
			}
			$xml.= "\t\t</images>\n";
			// конец формирования массива изображений

			// начало формирования блока рубрик (соответствия наших тем рубрикам на mail.ru)
			$xml.= "\t\t<rubrics>\n";
			$num_buffer = array();
			foreach ($model->getThemes() as $theme_id) {
				switch ($theme_id) {
					case 6 : $num = 4; break;
					case 3 : $num = 4; break;
					case 4 : $num = 5; break;
					case 9 : $num = 3; break;
					case 10 : $num = 3; break;
					case 8 : $num = 4; break;
					case 2 : $num = 5; break;
					case 7 : $num = 5; break;
					case 1 : $num = 8; break;
					case 5 : $num = 8; break;
					default : continue;
				}
				// пропуск уже указанных рубрик
				if (isset($num_buffer[$num]))
					continue;

				$num_buffer[$num] = $num;
				$xml.= "\t\t\t<rubric id = '$num' />\n";
			}
			$xml.= "\t\t</rubrics>\n";
			// конец формирования рубрик

			$xml.= "\t</article>\n";
		}

		$xml.= '</articles>';

		// кеширование на 3 часа
		Yii::app()->cache->set('xml_knowledge', $xml, 3 * 3600);

		Header('Content-type: text/xml');
		echo $xml;

		Yii::app()->end();
	}

	/**
	 * XML выгрузка идей для mail.ru
	 */
	public function actionXmlIdea()
	{
		$cache = Yii::app()->cache->get('xml_idea');

		if ( $cache ) {
			Header('Content-type: text/xml');
			echo $cache;
			Yii::app()->end();
		}

        $style_array = array(
            106 => 1,
            194 => 1,
            74 => 2,
            138 => 2,
            195 => 2,
            108 => 3,
            129 => 3,
            196 => 3,
            105 => 4,
            124 => 4,
            197 => 4,
            104 => 5,
            130 => 5,
            201 => 5,
            233 => 6,
            107 => 7,
            127 => 7,
            202 => 7,
            109 => 8,
            141 => 8,
            204 => 8,
            75 => 9,
            139 => 9,
            205 => 9,
            115 => 11,
            193 => 11,
            111 => 12,
            136 => 12,
            199 => 12,
            113 => 13,
            133 => 13,
            200 => 13,
            103 => 14,
            140 => 14,
            203 => 14,
            112 => 15,
            132 => 15,
            206 => 15,
            110 => 16,
            135 => 16,
            198 => 16,
            114 => 17,
            144 => 17,
            208 => 17,
            73 => 18,
            209 => 18,
            116 => 19,
            191 => 19,
            207 => 19,
            120 => 2,
        );

		/**
		 * Подключение feed extension
		 */
		Yii::import('application.modules.idea.models.*');

		$ideas = Idea::getLatestIdeas();

		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml.= '<portfolios>' . "\n";

		/**
		 * @var $model MediaKnowledge
		 */
		foreach($ideas as $model) {

            $styles = InteriorContent::getStyleById($model->id);

			$rubrics = array();
            if (get_class($model) == 'Interior') {
                $ics = InteriorContent::model()->findAllByAttributes(array('interior_id'=>$model->id));
                foreach($ics as $ic) {
					if ($ic->room_id == 79)
						$rubrics[6] = 6;
					if ($ic->room_id == 76)
						$rubrics[7] = 7;
					if ($ic->room_id == 78)
						$rubrics[8] = 8;
					if ($ic->room_id == 77)
						$rubrics[9] = 9;
					if ($ic->room_id == 96)
						$rubrics[10] = 10;
					if ($ic->room_id == 98)
						$rubrics[11] = 11;
				}
			}
			if (get_class($model) == 'Interiorpublic') {
				if ($model->building_type_id == 232)
					$rubrics[14] = 14;
			}
			if (get_class($model) == 'Architecture') {
				if ($model->object_id == 118)
					$rubrics[16] = 16;
			}

			if (empty($rubrics))
				continue;

			$xml.= "\t<portfolio id='".$model->id."'>\n";
			$xml.= "\t\t<title>" . $model->name . "</title>\n";
			$xml.= "\t\t<announce>" . $this->encodeStrForXml(strip_tags($model->desc)) . "</announce>\n";



			// начало формирования массива изображений
			$xml.= "\t\t<images>\n";
			$i = 1;

			if ($model instanceof Architecture) { // Заплатка для перевода на новые картинки

				/** @var $imgComp ImageComponent */
				$imgComp = Yii::app()->img;
				$photoList = $model->getPhotoList();

				$main_image_url = $this->encodeStrForXml(Yii::app()->createAbsoluteUrl('/' . $model->getPreview('resize_710x475')));
				$xml.= "\t\t\t<image id='{$i}' url='{$main_image_url}' is_main='true' />\n";
				$i++;

				foreach ($photoList as $imageId) {
					$src = $this->encodeStrForXml(Yii::app()->createAbsoluteUrl('/' . $imgComp->getPreview($imageId, 'resize_710x475')));
					$xml.= "\t\t\t<image id='{$i}' url='{$src}' />\n";
					$i++;
				}

			} else {
				$images = $model->getPhotos();

				$main_image_url = $this->encodeStrForXml(Yii::app()->createAbsoluteUrl('/' . $model->getPreview(Interior::$preview['resize_710x475'])));
				$xml.= "\t\t\t<image id='{$i}' url='{$main_image_url}' is_main='true' />\n";
				$i++;

				foreach ($images as $image) {
					$src = $this->encodeStrForXml(Yii::app()->createAbsoluteUrl('/' . $image->getPreviewName(Interior::$preview['resize_710x475'])));
					$xml.= "\t\t\t<image id='{$i}' url='{$src}' />\n";
					$i++;
				}
			}

			$xml.= "\t\t</images>\n";
			// конец формирования массива изображений

			// начало формирования блока рубрик (соответствия наших тем рубрикам на mail.ru)
			$xml.= "\t\t<rubrics>\n";
			foreach($rubrics as $rb) {
				$xml.= "\t\t\t<rubric id = '{$rb}' />\n";
			}
			$xml.= "\t\t</rubrics>\n";
			// конец формирования рубрик

            // начало формирования блока стилей (соответствия наших стилей рубрикам на mail.ru)
            $xml.= "\t\t<styles>\n";
            foreach($styles as $style_id) {
                if(isset($style_array[$style_id])) {
                    $xml.= "\t\t\t<style id = '{$style_array[$style_id]}' />\n";
                }
                else {
                    $xml.= "\t\t\t<style id = '{1}' />\n";
                }
            }
            $xml.= "\t\t</styles>\n";

			$xml.= "\t</portfolio>\n";
		}

		$xml.= '</portfolios>';

		// кеширование на 3 часа
		Yii::app()->cache->set('xml_idea', $xml, 3 * 3600);

		Header('Content-type: text/xml');
		echo $xml;

		Yii::app()->end();
	}

	public function actionJsonJournalNews()
	{
		Yii::import('application.modules.media.models.MediaNew');

		$resultArray = array(
			'logo'=>Yii::app()->createAbsoluteUrl('/img/myhome_ru_logo_curv.jpg'),
			'news'=>array(),
		);

		$criteria = new CDbCriteria();
		$criteria->condition = 'status=:status AND public_time<=:time';
		$criteria->order = 'public_time DESC';
		$criteria->params = array(':status' => MediaNew::STATUS_PUBLIC, ':time' => time());
		$criteria->limit = 3;

		$news = MediaNew::model()->findAll($criteria);

		foreach($news as $model) {
			$resultArray['news'][] = array(
				'title'=>$model->title,
				'img'=>Yii::app()->createAbsoluteUrl('/' . $model->preview->getPreviewName(MediaNew::$preview['crop_210'])),
				'datetime'=>date('d.m.Y H:i', $model->public_time),
				'url'=>$this->createAbsoluteUrl($model->getElementLink()),
			);
		}

		$encoded = json_encode($resultArray);
		header('Content-type: application/json');
		exit($encoded);
	}

	/**
	 * Конвертирует строку HTML в строку, пригодную для вывода в XML
	 * @param $str
	 *
	 * @return mixed
	 */
	private function encodeStrForXml($str)
	{
		$str = html_entity_decode($str);
		$str = str_ireplace(array('&'),array('&amp;'),$str);

		return $str;
	}

}
