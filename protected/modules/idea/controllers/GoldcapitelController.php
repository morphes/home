<?php

class GoldcapitelController extends FrontController
{
	
	/**
	 * Добавляет или убирает работу из конкурса.
	 * Эти действия разрешены только автору работы.
	 * 
	 * @param integer $id ID интерьера
	 */
	public function actionAddremove($id = null)
	{
		$id = (int)$id;
		$success = false;
		
		$capitel = GoldCapitel::model()->findByAttributes(array('interior_id' => $id));
		$interior = Interior::model()->findByPk($id);
		
		// Если есть интерьер и он не добавлен в конкурс,
		// добавлем его
		if ($interior && (Yii::app()->user->id == $interior->author_id))
		{
			if ( ! $capitel )
			{	// Если интерьера в конкурсе нет, добавляем.
				$item = new GoldCapitel();
				$item->interior_id = $id;
				$item->author_id = $interior->author_id;
				$item->status = GoldCapitel::STATUS_ADDED;

				if ($item->save()) {
					$success = true;
				}
			}
			else // Если есть в конкурсе, удаляем его.
			{
				$capitel->delete();
				$success = true;
			}
		}
		
		die(
			CJSON::encode( array('success' => $success) )
		);
	}
	
	/**
	 * Выбирает указанный интерьер в конкурсе, как лучший.
	 * @param type $id 
	 */
	public function actionSelect($id = null)
	{
		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException (404);
		
		$success = false;
		
		$session = Yii::app()->session;
		
		// Если есть доступ, показываем список работ...
		if ($session['listGoldCapitel'] != 'access') {
			die(CJSON::encode(array('success' => $success)));
		}
		
		$model = GoldCapitel::model()->findByAttributes(array('interior_id' => $id));
		if ($model) {
			
			if ($model->status == GoldCapitel::STATUS_ADDED) 
				$model->status = GoldCapitel::STATUS_SELECTED;
			else
				$model->status = GoldCapitel::STATUS_ADDED;
			
			
			if ($model->save())
				$success = true;
		}
		
		die(CJSON::encode(array('success' => $success)));
	}
	
	/**
	 * Список работ, учавствующих в конкурсе.
	 */
	public function actionList()
	{
		$session = Yii::app()->session;
		
		/* Если из формы пришел секретный ключ и он верный, то
		 * делаем в сессии отметку о доступе к странице.
		 */
		if (Yii::app()->request->isPostRequest)
		{
			$key = Yii::app()->request->getParam('secret_key');
			if ($key == 'gold')
				$session['listGoldCapitel'] = 'access';
		}
		
		
		
		
		// Если есть доступ, показываем список работ...
		if ($session['listGoldCapitel'] == 'access') {
			
			// Получаем список всех интерьеров, добавленных в конкус
			$capitelModels = GoldCapitel::model()->findAll();
			$ids = $statusIdea = array();
			foreach($capitelModels as $capitel) {
				$ids[] = $capitel->interior_id;
				$statusIdea[ $capitel->interior_id ] = $capitel->status;
			}
			
			if (empty($ids))
				$ids = array(0);
				
			// Получаем данные всех интерьеров конкурса.
			$dataProvider = new CActiveDataProvider('Interior', array(
				'criteria' => array(
					'condition' => '
						    status <> '.Interior::STATUS_MAKING.'
						AND status <> '.Interior::STATUS_DELETED.'
						AND id in ('.implode(',', $ids).')',
					'order' => 'author_id ASC, create_time ASC',

				),
				'pagination' => array( 'pageSize' => 1000 )
			));
			
			
			$this->render('//idea/goldcapitel/list', array(
				'interiors' => $dataProvider->getData(),
				'statusIdea' => $statusIdea
			));
		}
		else {
			$this->hide_div_content = true;
			$this->spec_div_class = 'nulled';
			
			//...иначе показываем форму входа.
			$this->render('//idea/goldcapitel/noaccess');
		}
	}
	
	
	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/

}