<?php

/**
 * ShareBox
 * Create a list of social networks that a user may share the page with.
 *
 * CSS base and 48px icons from Beautiful Social Bookmarking Widget by Harish.
 * http://www.way2blogging.org/2011/03/add-beautiful-social-bookmarking-widget.html
 *
 * 16, 24 and 32 px icons from IconDock
 * http://icondock.com/free/vector-social-media-icons
 *
 * @copyright © Digitick <www.digitick.net> 2011
 * @license Public Domain
 * @author Ianaré Sévi
 *
 * Note: the company logos in the icons are copyright of their respective owners.
 */

/**
 * Main widget class.
 */
class EShareBox extends CWidget
{
	public $view = 'main';
	/**
	 * @var string URL to share.
	 */
	public $url;
	/**
	 * @var string Title for the page to share.
	 */
	public $title;
	/**
	 * @var string Message for the page to share.
	 */
	public $message;

	/**
	 * Ссылка на картинку которую
	 * надо расшарить
	 * @var string
	 */
	public $imgUrl;

	/**
	 * @var array Services to include.
	 * Note that the exclude filter is still applied.
	 */
	public $include = array();
	/**
	 * @var array Services to exclude.
	 */
	public $exclude = array();
	/**
	 * @var array Html options for UL element
	 */
	public $htmlOptions = array();
	/**
	 * @var array Definitions for sharing services .
	 */
	protected $shareDefinitions = array(
		'livejournal' => array(
			'url' => 'http://www.livejournal.com/update.bml?subject={title}&event={message}<br>{url}',
			'title' => 'Разместить в LiveJournal',
			'name' => 'LiveJournal',
		),
		'vkontakte' => array(
			'url' => 'http://vkontakte.ru/share.php?url={url}&title={title}&description={message}',
			'title' => 'Разместить во ВКонтакт',
			'name' => 'ВКонтакте',
		),
		'twitter' => array(
			'url' => 'http://twitter.com/intent/tweet?text={title}+—++{url}',
			'title' => 'Разместить в Twitter',
			'name' => 'Twitter',
		),
		'facebook' => array(
			'url' => 'https://www.facebook.com/share.php?u={url}&t={title}',
			'title' => 'Разместить в Facebook',
			'name' => 'Facebook',
		),
		'google+' => array(
		    'url' => ' https://plus.google.com/share?url={url}',
		    'title' => 'Разместить в Google+',
		    'name' => 'Google+',
		),
		'odkl' => array(
			'url'  => 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st.comments={title}&st._surl={url}',
			'title' => 'Разместить в Одноклассниках',
			'name' => 'Одноклассники',
		),
		'pinterest' => array(
			'url' => 'http://www.pinterest.com/pin/create/button/?url=www.myhome.ru&media={imgUrl}&description={title}',
			'title' => 'Разместить в Pinterest',
			'name' => 'Pinterest',
		),
	);
	
	public $classDefinitions = array(
		'livejournal' => 'share-lj',
		'vkontakte' => 'share-vk',
		'twitter' => 'share-tw',
		'facebook' => 'share-fb',
		'google+' => 'share-gplus',
		'odkl' => 'share-odkl',
		'pinterest' => 'share-pi',
	);
	/**
	 * @var array Default html options. Will be merged with $htmlOptions provided by user.
	 */
	protected $defaultHtmlOptions = array(
		"class" => "share",
	);

	public function init()
	{
		/*if (!$this->url || !$this->title) {
			throw new CException('Could not initialize ShareBox : "title" and "url" parameters are required.');
		}*/
		
		if (!empty($this->include)) {
			foreach ($this->shareDefinitions as $share => $info) {
				if (!in_array($share, $this->include)) {
					unset($this->shareDefinitions[$share]);
				}
			}
		}
		foreach ((array) $this->exclude as $share) {
			unset($this->shareDefinitions[$share]);
		}
		$this->htmlOptions = array_merge($this->defaultHtmlOptions, $this->htmlOptions);
	}

	public function run()
	{
		$this->render($this->view, array(
		    'shareDefinitions' => $this->shareDefinitions,
		    'htmlOptions' => $this->htmlOptions,
		    'title' => $this->title,
		    'message' => $this->message,
		    'url' => $this->url,
		    'imgUrl' => $this->imgUrl,
		    'classDefinition' => $this->classDefinitions,
		));
	}

}
