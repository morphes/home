<?php

/**
 * @brief Управление всеми меню сайта
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class MenuController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';
	public $rightbar = null;
	
	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'submenu', 'update', 'up', 'down', 'create', 'createMain', 'delete'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
	
	public function actionIndex()
	{
		// ПРоверяем на входе тип идеи
                $type_id = Yii::app()->request->getParam('type_id');
                if (is_null($type_id))
                        $type_id = Menu::TYPE_MAIN;
		
                if (!isset(Menu::$menuNames[$type_id]))
                        throw new CHttpException(404);

                $criteria = new CDbCriteria;
                $criteria->compare('type_id', $type_id);
                $criteria->compare('parent_id', 0);
		$criteria->order = 'position ASC';

                $dataProvider = new CActiveDataProvider('Menu', array(
                            'criteria' => $criteria
                        ));

                $this->render('index', array(
                    'dataProvider' => $dataProvider,
                    'type_id' => $type_id
                ));
	}
	
	
        /**
         * Выдает список свойств с ключом $key, принадлежащих
         * родительскому свойству $id
         * 
         * @param integer $parent_id Идентификатор родительского свойства
	 * @author Alexey Shvedov
         */
        public function actionSubmenu($parent_id)
        {
                // Получаем родительское свойство, потомков которого надо посмотреть
                $parentModel= Menu::model()->findByPk($parent_id);
                if (is_null($parentModel))
                        throw new CHttpException(404);

                $dataProvider = new CActiveDataProvider('Menu', array(
                            'criteria' => array(
					'condition' => 'parent_id = :id',
					'params' => array(':id' => $parent_id),
					'order' => 'position ASC',
				)
                        ));

                $this->render('submenu', array(
                    'dataProvider' => $dataProvider,
                    'parentModel' => $parentModel,
                ));
        }
	
	/**
         * Редактирование свойства $id
         * 
         * @param integer $id Идентификатор свойства
	 * @author Alexey Shvedov
         */
        public function actionUpdate($id)
        {
                // Получаем данные по свойству
                $model = Menu::model()->findByPk($id);
                if (is_null($model))
                        throw new CHttpException(404);

                // Получаем данные по родителю свойства
                $parentModel = Menu::model()->findByAttributes(array('id' => $model->parent_id));

                if (isset($_POST['Menu'])) {
                        $model->attributes = $_POST['Menu'];
                        if ($model->save()) {
                                if (!is_null($parentModel))
                                        $this->redirect(array('submenu', 'parent_id' => $parentModel->id));
                                else
                                        $this->redirect(array('index', 'type_id' => $model->type_id));
                        }
                }

                $this->render('edit', array(
                    'model' => $model,
                    'parentModel' => $parentModel
                ));
        }
	
	/**
	 * Remove menu item and 1 level submenu
	 * @param integer $id 
	 * @author Alexey Shvedov
	 */
	public function actionDelete($id)
	{
		$model = Menu::model()->findByPk($id);
		if (is_null($model))
			throw new CHttpException(404);
		
		$model->delete();
		Menu::model()->deleteAllByAttributes(array('parent_id' => $model->id));
		
		if (!empty($model->parent_id))
			$this->redirect(array('submenu', 'parent_id' => $model->parent_id));
		else
			$this->redirect(array('index', 'type_id' => $model->type_id));
	}

	/**
         * Создание нового свойства
         * 
         * @param type $parent_id 
	 * @author Alexey Shvedov
         */
        public function actionCreate($parent_id)
        {
                // Получаем родительское свойство
                $parentModel = Menu::model()->findByPk($parent_id);
                if (is_null($parentModel))
                        throw new CHttpException(404);

                // Создаем новое свойство
                $model = new Menu();

                // задаем id родителя и тип идеи
                $model->parent_id = $parentModel->id;
                $model->type_id = $parentModel->type_id;

                if (isset($_POST['Menu'])) {
                        $model->attributes = $_POST['Menu'];
                        if ($model->save()) {
				$model->position = $model->id;
				$model->save(false);
				$this->redirect($this->createUrl('submenu', array('parent_id' => $parent_id)));
                        }
                }

                $this->render('edit', array(
                    'model' => $model,
                    'parentModel' => $parentModel
                ));
        }

        /**
         * Создание нового главного свойства
         * 
         * @param type $id 
	 * @author Alexey Shvedov
         */
        public function actionCreateMain($type_id)
        {
                $type_id = (int) $type_id;

		if (!isset(Menu::$menuNames[$type_id]))
                        throw new CHttpException(404);

                // Получаем данные по свойству
                $model = new Menu();

                $model->parent_id = 0;
                $model->type_id = $type_id;

                if (isset($_POST['Menu'])) {
                        $model->attributes = $_POST['Menu'];
                        if ($model->save()) {
				$model->position = $model->id;
				$model->save(false);
                                $this->redirect(array('index', 'type_id' => $type_id));
                        }
                }

                $this->render('edit', array(
                    'model' => $model,
                    'parentModel' => null,
                    'type_id' => $type_id
                ));
        }
	
	/**
	 * Move up menu item position 
	 * @return boolean
	 * @author Alexey Shvedov
	 */
	public function actionUp()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
		$this->layout = false;
		$id = Yii::app()->request->getParam('id');
		$current = Menu::model()->findByPk($id);
		if (is_null($current))
			return $this->renderText (false);
		
		$criteria = new CDbCriteria();
		$criteria->condition = 'type_id=:type_id AND parent_id=:parent_id AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(':position' => $current->position, ':type_id' => $current->type_id, ':parent_id' => $current->parent_id);
		
		$next = Menu::model()->find($criteria);
		if (is_null($next))
			return $this->renderText (false);
		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		return $this->renderText (true);
	}
	
	/**
	 * Move down menu item position 
	 * @return boolean
	 * @author Alexey Shvedov
	 */
	public function actionDown()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
		$this->layout = false;
		$id = Yii::app()->request->getParam('id');
		$current = Menu::model()->findByPk($id);
		if (is_null($current))
			return $this->renderText (false);
		
		$criteria = new CDbCriteria();
		$criteria->condition = 'type_id=:type_id AND parent_id=:parent_id AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(':position' => $current->position, ':type_id' => $current->type_id, ':parent_id' => $current->parent_id);
		
		$next = Menu::model()->find($criteria);
		if (is_null($next))
			return $this->renderText (false);
		
		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		return $this->renderText (true);
	}

}