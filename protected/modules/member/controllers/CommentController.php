<?php

/**
 * @brief Обработка добавления комментариев
 * @author Sergey Seregin <sss@medvediza.ru>
 */
class CommentController extends Controller
{

        private $_view = '//widget/comment/_oneComment';

        public function filters()
        {
                return array('accessControl');
        }

        /**
         * @brief Разрешает доступ
         * @return array
         */
        public function accessRules()
        {

                return array(
                        array('allow',
                                'actions' => array('create'),
                                'users' => array('*')
                        ),
                        array('deny',
                                'users' => array('*'),
                        ),
                );
        }

        /**
         *
         * @brief Добавление нового комментария
         * @param integer $id
         * @param type $type not use
         */
        public function actionCreate($id, $type)
        {
		$success = false;
                $html_comment = '';
                $errors = '';
                $average_rating = $count_comment = 0;
		$showRating = (bool)Yii::app()->getRequest()->getParam('rating');

                $comment = new Comment();
                $voting = new Voting();


                if (isset($_POST['ajax']) && $_POST['ajax'] === 'comment-form') {
                        echo CActiveForm::validate($comment);
                        Yii::app()->end();
                }

                if (isset($_POST['Comment']) && Yii::app()->request->isAjaxRequest) {
                        $comment->attributes = $_POST['Comment'];
                        $comment->message = CHtml::encode($comment->message);
                        $comment->parent_id = 0;

                        if (isset($_POST['Voting'])) {
                                $voting->attributes = $_POST['Voting'];
                        }

                        $comment->model	= $voting->model = Yii::app()->request->getParam('type');
                        $comment->model_id = $voting->model_id = (int)Yii::app()->request->getParam('id');

			$ip = Yii::app()->request->getUserHostAddress();

			if($ip)
			{
				$comment -> author_ip = ip2long($ip);
			}

			if(Yii::app()->user->isGuest)
			{
				$comment ->author_id = 0;
				$comment -> status = $comment::ON_MODERATE;

				if(Yii::app()->cookieStorage->getCookieId())
				{
					$comment ->guest_id = Yii::app()->cookieStorage->getCookieId();
				}
				else
				{
					$comment -> guest_id = 1;
				}
			}
			else
			{
				$comment->author_id = $voting->author_id = Yii::app()->user->id;
				$comment->status = $comment::ACTIVE;
			}

                        if ( empty(Config::$commentType[$comment->model]) )
                                throw new CException(__CLASS__.':'.__METHOD__.': Invalid comment type');

                        // Импортим модель Интерьера
                        Yii::import('application.modules.idea.models.Interior');
                        Yii::import('application.modules.idea.models.Interiorpublic');
                        Yii::import('application.modules.idea.models.Architecture');
                        Yii::import('application.modules.idea.models.Portfolio');
                        Yii::import('application.modules.catalog.models.Product');
                        Yii::import('application.modules.content.models.News');
                        Yii::import('application.modules.media.models.*');
                        Yii::import('application.modules.catalog.models.StoreNews');

			$model = $comment->getLinkedModel();

                        if (!$model->getIsOwner()) {
                                $voting->save();
                        }

                        switch($comment->model) {
                                case 'Product' : {
                                        $this->_view = '//widget/comment/product/_oneComment'; break;
					$showRating = true;
				}
				case 'StoreNews':
					$this->_view = '//widget/comment/storeNews/_oneComment';
					break;
                        }

                        // --- СОХРАНЯЕМ ---
                        if ($comment->save()) {
				$success = true;
                                list($average_rating, $count_comment) = $model->afterComment($comment);

				/*if($comment->status!=$comment::ON_MODERATE)
				{*/
				
					$html_comment = $this->renderPartial($this->_view, array(
						'comment' => $comment,
						'modelId' => $comment->model_id,
						'showRating' => $showRating,
						'model' => $model,
					), true);
				/*}*/

			} else {
				
				$success = false;
				$errors = $comment->getError('message');
			}
		}

		echo CJSON::encode(array(
			'success'	=> $success,
			'errors'	=> $errors,
			'html_comment'	=> $html_comment,
			'average_rating'=> $average_rating,
			'count_comment'	=> $count_comment,
		));
	}

}