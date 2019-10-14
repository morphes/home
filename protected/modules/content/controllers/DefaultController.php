<?php

/**
 * @brief Редактирование статического контента сайта
 * @author Roman Kuzakov
 */
class DefaultController extends FrontController
{

        public function actionIndex($category = null, $article = null)
        {
                $category = ContentCategory::model()->findByAttributes(array(
                    'alias' => $category,
                ));

                if (!$category || $category->status != ContentCategory::STATUS_ACTIVE)
                        throw new CHttpException(404);

                $article = Content::model()->findByAttributes(array(
                    'alias' => $article,
                    'category_id' => $category->id,
                ));

                if (!$article || $article->status != Content::STATUS_ACTIVE)
                        throw new CHttpException(404);

		// Отмечаем ключ пункта меню для подсветки
		$this->menuActiveKey = $article->menu_key;
		$this->menuIsActiveLink = $article->is_active_key;
		
                return $this->render('//content/default/index', array(
                            'category' => $category,
                            'article' => $article,
                        ));
        }

}
