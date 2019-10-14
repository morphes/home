<?php

/**
 * @brief Виджет комментариев
 */
class WComment extends CWidget
{
	/**
	 * @var object A model which we want to add comment
	 */
	public $model = null;
	
	/**
	 * @var string The comments block title
	 */
	public $title = 'Комментарии';
	
	/**
	 *
	 * @var string Url to page which contains all comments
	 */
	public $urlToAllComments = null;
	
	/**
	 *
	 * @var integer Specifies the number of comments on page
	 */
	public $showCnt = 5;
	/**
	 * Flag for comments hide
	 * @var boolean
	 */
	public $hideComments = false;
	
	public $showRating = true;

    /**
     * Показывать рекламу яндекс директ
     * @var bool
     */
    public $showDirect = true;

	/**
	 * Возможно ли оставление комментариев незарегестрированным пользователям.
	 * @var bool
	 */
	public $guestComment = false;

        public $view = '//widget/comment/main';

	// Флаг предназначеный для сброса кеша
	public $refreshCache = false;
	
        public function init()
        {
		if (is_null($this->model))
			throw new CException('The model is null');
		
		if ( !is_integer($this->showCnt))
			throw new CException('showCnt is not integer');
        }

        public function run()
        {
		if ($this->hideComments)
			return;

		$key = 'WComment:' . Yii::app()->user->isGuest . ':' . get_class($this->model) . ':' . $this->model->id;

		$html = Yii::app()->cache->get($key);

		if ($this->refreshCache == true) {
			$html = false;
		}

		if (!$html) {

			$criteria = new CDbCriteria();

			$criteria->order = 'create_time DESC';
			if ($this->showCnt > 0) {
				$criteria->limit = $this->showCnt;
				$criteria->offset = 0;
			}

			// Get the list of comments for current model
			$comments = Comment::model()->findAllByAttributes(
				array(
					'model' => get_class($this->model),
					'model_id' => $this->model->id,
					'status' => Comment::ACTIVE,
				), $criteria
			);

			// Calculates total quantity comments
			$cntTotal = Comment::model()->count(
				'model = :model AND model_id = :model_id AND status = :status',
				array(':model' => get_class($this->model), ':model_id' => $this->model->id, ':status' => Comment::ACTIVE )
			);

			// Проверка на владение моделью
			$owner = $this->model->getIsOwner();

			$newComment = new Comment();
			$voting = new Voting();

			$html = Yii::app()->controller->renderPartial($this->view, array(
				'title'            => $this->title,
				'urlToAllComments' => $this->urlToAllComments,
				'cntTotal'         => $cntTotal,
				'model'            => $this->model,
				'newComment'       => $newComment,
				'voting'           => $voting,
				'comments'         => $comments,
				'showRating'       => $this->showRating,
				'owner'            => $owner,
				'guestComment'     => $this->guestComment,
				'isGuest'          => Yii::app()->user->isGuest,
                'showDirect'       => $this->showDirect
			), true);

			/*
			 * SQL зависимость, которая проверяет дату создания
			 * последнего комента, относящегося к текущей модели
			 * с текущим id.
			 */

			$sql = 'SELECT create_time '
				. ' FROM ' . Comment::model()->tableName()
				. ' WHERE model = "' . get_class($this->model) .'"'
				. ' 	AND model_id = ' . $this->model->id
				. '	AND status = ' . Comment::ACTIVE
				. ' ORDER BY create_time DESC'
				. ' LIMIT 1';

			Yii::app()->cache->set($key, $html, Cache::DURATION_DAY, new CDbCacheDependency($sql));
		}

		echo $html;
        }

}
