<?php

class PropertyController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'prop', 'create', 'createmain', 'update', 'up', 'down'),
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

        /**
         * Входной скрипт
         * 
         * @author Sergey Seregin
         */
        public function actionIndex()
        {
                // ПРоверяем на входе тип идеи
                $idea_type_id = (int) Yii::app()->request->getParam('idea_type_id');
                if (!$idea_type_id)
                        $idea_type_id = Config::INTERIOR;


                if (!array_key_exists($idea_type_id, Config::$ideaTypes))
                        throw new CHttpException(404);

		switch($idea_type_id)
		{
			case Config::INTERIOR: $this->_indexInterior(Config::INTERIOR); break;
			case Config::INTERIOR_PUBLIC: $this->_indexInterior(Config::INTERIOR_PUBLIC); break;
			case Config::ARCHITECTURE: $this->_indexArchitecture(); break;

			default: throw new CHttpException(404); break;
		}
        }

	/**
	 * Первая страница свойств для Интерьеров
	 */
	private function _indexInterior($typeId)
	{
		$criteria = new CDbCriteria;
		$criteria->select = '
			t.id,
			t.option_key,
			t.option_value,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'building_type\') as building_type_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'style\') as style_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'room\') as room_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'color\') as color_cnt
		';
		$criteria->compare('idea_type_id', $typeId);
		$criteria->compare('parent_id', 0);
		$criteria->order = 'position ASC';

		$dataProvider = new CActiveDataProvider('IdeaHeap', array(
			'criteria' => $criteria
		));

		$this->render('indexInterior', array(
			'dataProvider' => $dataProvider,
			'idea_type_id' => $typeId,
		));
	}

	/**
	 * Первая страница свойст для Архитекутры
	 */
	private function _indexArchitecture()
	{
		$criteria = new CDbCriteria;
		$criteria->select = '
			t.id,
			t.option_key,
			t.option_value,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'building_type\') as building_type_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'style\') as style_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'material\') as material_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'floor\') as floor_cnt,
			(SELECT COUNT(*) FROM idea_heap t2 WHERE t2.parent_id = t.id and t2.option_key = \'color\') as color_cnt
		';
		$criteria->compare('idea_type_id', Config::ARCHITECTURE);
		$criteria->compare('parent_id', 0);
		$criteria->order = 'position ASC';

		$dataProvider = new CActiveDataProvider('IdeaHeap', array(
			'criteria' => $criteria
		));

		$this->render('indexArchitecture', array(
			'dataProvider' => $dataProvider,
			'idea_type_id' => Config::ARCHITECTURE
		));
	}

        /**
         * Выдает список свойств с ключом $key, принадлежащих
         * родительскому свойству $id
         * 
         * @author Sergey Seregin
         * 
         * @param integer $id Идентификатор родительского свойства
         * @param string $key Название ключа
         */
        public function actionProp($id, $key)
        {
                // Получаем родительское свойство, потомков которого надо посмотреть
                $parent_model = IdeaHeap::model()->findByPk($id);
                if (!$parent_model)
                        throw new CHttpException(404);

                $dataProvider = new CActiveDataProvider('IdeaHeap', array(
			'criteria' => array(
				'condition' => 'parent_id = :id and option_key = :option_key',
				'params' => array(':id' => $id, ':option_key' => $key),
				'order' => 'position ASC',
			),
			'pagination' => array(
				'pageSize' => 40,
			),
		));

                $this->render('property', array(
                    'dataProvider' => $dataProvider,
                    'parent_model' => $parent_model,
                    'key' => $key
                ));
        }

        /**
         * Редактирование свойства $id
         * 
         * @author Sergey Seregin
         * 
         * @param integer $id Идентификатор свойства
         */
        public function actionUpdate($id)
        {
                // Получаем данные по свойству
                $model = IdeaHeap::model()->findByPk($id);
                if (!$model)
                        throw new CHttpException(404);

                // Получаем данные по родителю свойства
                $parent_model = IdeaHeap::model()->findByAttributes(array('id' => $model->parent_id));

                if (isset($_POST['IdeaHeap'])) {
                        $model->attributes = $_POST['IdeaHeap'];
                        if ($model->save()) {
                                if (!is_null($parent_model))
                                        $this->redirect(array('prop', 'id' => $parent_model->id, 'key' => $model->option_key));
                                else
                                        $this->redirect(array('index', 'idea_type_id' => $model->idea_type_id));
                        }
                }

                $this->render('edit', array(
                    	'model' => $model,
                    	'parent_model' => $parent_model
                ));
        }

        /**
         * Создание нового свойства
         * 
         * @author Sergey Seregin
         * 
         * @param type $id 
         */
        public function actionCreate($id, $key)
        {
                // Получаем родительское свойство
                $parent_model = IdeaHeap::model()->findByPk($id);
                if (!$parent_model)
                        throw new CHttpException(404);

                // Создаем новое свойство
                $model = new IdeaHeap();

                if (in_array($key, array('room', 'color', 'style', 'object', 'building_type')))
                        $model->option_key = $key;
                else
                        throw new CHttpException(404);

                // задаем id родителя и тип идеи
                $model->parent_id = $parent_model->id;
                $model->idea_type_id = $parent_model->idea_type_id;

                if (isset($_POST['IdeaHeap'])) {
                        $model->attributes = $_POST['IdeaHeap'];
                        if ($model->save()) {
				$model->position = $model->id;
				$model->save(false);
                                $this->redirect(array('prop', 'id' => $id, 'key' => $model->option_key));
                        }
                }

                $this->render('edit', array(
                    'model' => $model,
                    'parent_model' => $parent_model
                ));
        }

        /**
         * Создание нового главного свойства
         * 
         * @author Sergey Seregin
         * 
         * @param type $id 
         */
        public function actionCreateMain($idea_type_id)
        {
                $idea_type_id = (int) $idea_type_id;

                if (!array_key_exists($idea_type_id, Config::$ideaTypes))
                        throw new CHttpException(404);

                // Получаем данные по свойству
                $model = new IdeaHeap();

                $model->option_key = 'object';
                $model->parent_id = 0;
                $model->idea_type_id = (int) $idea_type_id;

                if (isset($_POST['IdeaHeap'])) {
                        $model->attributes = $_POST['IdeaHeap'];
                        if ($model->save()) {
				$model->position = $model->id;
				$model->save(false);
                                $this->redirect(array('index', 'idea_type_id' => $model->idea_type_id));
                        }
                }

                $this->render('edit', array(
                    'model' => $model,
                    'parent_model' => null,
                ));
        }
	
	/**
	 * Move up property item position 
	 * @return boolean
	 * @author Alexey Shvedov
	 */
	public function actionUp()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
		$this->layout = false;
		$id = Yii::app()->request->getParam('id');
		$current = IdeaHeap::model()->findByPk($id);
		if (is_null($current))
			return $this->renderText (false);
		
		$criteria = new CDbCriteria();
		$criteria->condition = 'idea_type_id=:idea_type_id AND option_key=:option_key AND parent_id=:parent_id AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(':position' => $current->position, 
					':idea_type_id' => $current->idea_type_id, 
					':parent_id' => $current->parent_id,
					':option_key' => $current->option_key,
				);
		
		$next = IdeaHeap::model()->find($criteria);
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
	 * Move down property item position 
	 * @return boolean
	 * @author Alexey Shvedov
	 */
	public function actionDown()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
		$this->layout = false;
		$id = Yii::app()->request->getParam('id');
		$current = IdeaHeap::model()->findByPk($id);
		if (is_null($current))
			return $this->renderText (false);
		
		$criteria = new CDbCriteria();
		$criteria->condition = 'idea_type_id=:idea_type_id AND option_key=:option_key AND parent_id=:parent_id AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(':position' => $current->position, 
					':idea_type_id' => $current->idea_type_id, 
					':parent_id' => $current->parent_id,
					':option_key' => $current->option_key,
				);
		
		$next = IdeaHeap::model()->find($criteria);
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
         * Returns the data model based on the primary key given in the GET variable.
         * If the data model is not found, an HTTP exception will be raised.
         * @param integer the ID of the model to be loaded
         */
        public function loadModel($id)
        {
                $model = IdeaHeap::model()->findByPk($id);

                if ($model === null)
                        throw new CHttpException(404);
                return $model;
        }

}
