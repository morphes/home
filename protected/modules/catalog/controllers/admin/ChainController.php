<?php

class ChainController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';

        public function filters() {
                return array('accessControl');
        }

        public function accessRules() {

                return array(
                        array('allow',
                                'roles'=>array(
                                        User::ROLE_ADMIN,
                                        User::ROLE_POWERADMIN,
                                        User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_STORE,
                                ),
                        ),
                        array('deny',
                                'users'=>array('*'),
                        ),
                );
        }

        public function init()
        {
                // отключение твиттер-панели
                $this->rightbar = null;
        }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Chain;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Chain']))
		{
			$model->attributes=$_POST['Chain'];
                        $model->geocode = CHtml::encode(YandexMap::getGeocode('г.'.(isset($model->city->name) ? $model->city->name : '').', '.$model->address));
                        $model->user_id = Yii::app()->user->id;

                        if($model->save()) {

                                /**
                                 * Сохранение логотипа
                                 */
                                $model->setImageType('logo');
                                $file = UploadedFile::loadImage($model, 'logo', '', true);
                                if($file) {
                                        $model->image_id = $file->id;
                                        $model->save(false, array('image_id'));
                                }

                                $this->redirect(array('update', 'id'=>$model->id));
                        }

		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Chain']))
		{
			$model->attributes=$_POST['Chain'];
                        $model->geocode = CHtml::encode(YandexMap::getGeocode('г.'.(isset($model->city->name) ? $model->city->name : '') .', '.$model->address));

                        if(!$model->user_id)
                                $model->user_id = Yii::app()->user->id;

                        if($model->save()) {

                                /**
                                 * Сохранение логотипа
                                 */
                                $model->setImageType('logo');
                                $file = UploadedFile::loadImage($model, 'logo', '', true);
                                if($file) {
                                        $model->image_id = $file->id;
                                        $model->save(false, array('image_id'));
                                }


                                $this->redirect(array('index'));
                        }
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
                $model = new Chain('search');
                $model->unsetAttributes();

                $date_from = Yii::app()->request->getParam('date_from');
                $date_to = Yii::app()->request->getParam('date_to');

                $criteria = new CDbCriteria();
                $criteria->order = 'create_time DESC';

                if(isset($_POST['Chain'])) {
                        $model->attributes = $_POST['Chain'];
                        if($model->id)
                                $criteria->compare('id', $model->id);
                        if($model->name)
                                $criteria->compare('name', $model->name, true);
                        if($model->user_id)
                                $criteria->compare('user_id', $model->user_id);
                        if ($date_from)
                                $criteria->compare('create_time', '>='.(strtotime($date_from)));
                        if ($date_to)
                                $criteria->compare('create_time', '<='.(strtotime($date_to)+86400));
                        if($model->vendor_id) {
                                $chains = Yii::app()->db->createCommand()
                                        ->selectDistinct('cs.chain_id')->from('cat_store_vendor sv')
                                        ->join('cat_chain_store cs', 'cs.store_id=sv.store_id')
                                        ->where('sv.vendor_id=:vid', array(':vid'=>$model->vendor_id))->queryAll();

                                $chain_ids = array();
                                foreach($chains as $ch)
                                        $chain_ids[] = $ch['chain_id'];
                                $chain_ids = implode(', ', $chain_ids);
                                $criteria->addCondition('id in ('.$chain_ids.')');
                        }
                }

                $dataProvider=new CActiveDataProvider('Chain', array(
                        'criteria' => $criteria,
                        'pagination'=>array(
                                'pageSize'=>20,
                        ),
                ));

                $this->render('index',array(
                        'dataProvider'=>$dataProvider,
                        'model'=>$model,
                        'date_from'=>$date_from,
                        'date_to'=>$date_to,
                ));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Chain::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='chain-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

        /**
         * Удаление привязки магазина к производителю
         * @param $chain_id
         * @param $vendor_id
         * @throws CHttpException
         */
        public function actionDeleteVendor($chain_id, $vendor_id)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                if(!Vendor::model()->exists('id=:id', array(':id'=>(int) $vendor_id)))
                        throw new CHttpException(404);

                $chain = $this->loadModel($chain_id);

                $stores_ids = $chain->getStores(false, true);

                Yii::app()->db->createCommand()
                        ->delete('cat_store_vendor', 'vendor_id=:vid and store_id in ('.$stores_ids.')', array(':vid'=>(int)$vendor_id));

                die(json_encode(array('success'=>true)));
        }

        /**
         * Создание связки сеть магазинов - магазин
         * @throws CHttpException
         */
        public function actionAddStore()
        {
                $this->layout = false;

                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $store_id = (int) Yii::app()->request->getParam('store_id');
                $chain_id = (int) Yii::app()->request->getParam('chain_id');

                if(!Store::model()->exists('id=:id', array(':id'=>$store_id)) || !Chain::model()->exists('id=:id', array(':id'=>$chain_id)))
                        die(CJSON::encode(array('success'=>false, 'message'=>'Некорректное значение магазина или сети магазинов')));

                $exists = Yii::app()->db->createCommand()->from('cat_chain_store')
                        ->where('chain_id=:cid AND store_id=:sid', array(':sid'=>$store_id, ':cid'=>$chain_id))
                        ->limit(1)
                        ->queryAll();


                if(!$exists)
                        Yii::app()->db->createCommand()->insert('cat_chain_store', array('chain_id'=>$chain_id, 'store_id'=>$store_id));
                else
                        die(CJSON::encode(array('success'=>false, 'message'=>'Связка уже существует')));

                die(CJSON::encode(array('success'=>true, 'message'=>'')));
        }

        /**
         * Массовое создание связок магазины-сеть
         * @throws CHttpException
         */
        public function actionAddStores()
        {
                // набор id магазинов, разделенный запятыми
                $stores_ids = Yii::app()->request->getParam('stores_ids');

                if($stores_ids)
                        $stores = explode(',', $stores_ids);
                else
                        throw new CHttpException(400);

                $stores = array_map('intval', $stores);

                $chain_id = (int) Yii::app()->request->getParam('chain_id');

                // если передан id сети и набор id магазинов - сохраняем связки в бд
                if($stores && $chain_id && Chain::model()->exists('id=:id', array(':id'=>(int) $chain_id))) {
                        // проход по всем переданным магазинам
                        foreach($stores as $sid) {

                                // проверка наличия такой связки в базе
                                $exists = Yii::app()->db->createCommand()->from('cat_chain_store')
                                        ->where('chain_id=:cid and store_id=:sid', array(':cid'=>(int)$chain_id, ':sid'=>$sid))->queryRow();

                                if($exists || !Store::model()->exists('id=:id', array(':id'=> $sid)))
                                        continue;

                                // сохранение новой связки
                                Yii::app()->db->createCommand()
                                        ->insert('cat_chain_store', array('chain_id'=>(int)$chain_id, 'store_id'=>$sid));
                        }

                        return $this->redirect($this->createUrl('update', array('id'=>(int) $chain_id)));
                }


                $criteria = new CDbCriteria();
                $criteria->compare('id', $stores);

                $dataProvider = new CActiveDataProvider('Store', array(
                        'criteria'=>$criteria,
                        'pagination'=>array(
                                'pageSize'=>10,
                        ),
                ));

                $this->render('addStores', array(
                        'dataProvider'=>$dataProvider,
                ));
        }

        /**
         * Удаляет связку сети-магазина
         * @param $chain_id
         * @param $store_id
         * @throws CHttpException
         */
        public function actionDeleteStore($chain_id, $store_id)
        {
                $this->layout = false;

                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $chain_id = (int) $chain_id;
                $store_id = (int) $store_id;

                if(!Store::model()->exists('id=:id', array(':id'=>$store_id)) || !Chain::model()->exists('id=:id', array(':id'=>$chain_id)))
                        throw new CHttpException(404);

                Yii::app()->db->createCommand()->delete('cat_chain_store', 'chain_id=:cid AND store_id=:sid', array(':cid'=>$chain_id, ':sid'=>$store_id));
        }

        /**
         * Создание связки  магазины сети - производитель
         * @throws CHttpException
         */
        public function actionAddVendor()
        {
                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $chain_id = (int) Yii::app()->request->getParam('chain_id');
                $vendor_id = (int) Yii::app()->request->getParam('vendor_id');

                $chain = $this->loadModel($chain_id);

                if(!Vendor::model()->exists('id=:id', array(':id'=>$vendor_id)))
                        die(CJSON::encode(array('success'=>false, 'message'=>'Некорректное значение производителя')));

                $stores = Store::model()->findAll('id in ('.$chain->getStores(false, true).')');

                foreach($stores as $store) {
                        $exists = Yii::app()->db->createCommand()->from('cat_store_vendor')
                                ->where('store_id=:sid AND vendor_id=:vid', array(':sid'=>$store->id, ':vid'=>$vendor_id))
                                ->limit(1)
                                ->queryAll();

                        if(!$exists)
                                Yii::app()->db->createCommand()->insert('cat_store_vendor', array('vendor_id'=>$vendor_id, 'store_id'=>$store->id));
                        else
                                die(CJSON::encode(array('success'=>false, 'message'=>'Связка уже существует')));
                }

                die(CJSON::encode(array('success'=>true, 'message'=>'')));
        }

        /**
         * Автокомплит по сетям магазинов
         * @param $term
         * @throws CHttpException
         */
        public function actionAcChain($term)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);
                $this->layout = false;
                $data = Yii::app()->db->createCommand("SELECT t.id, t.name FROM `cat_chain` t WHERE t.name LIKE '" . CHtml::encode($term) . "%'")->queryAll();
                $results = array();
                foreach($data as $record) {
                        $results[] = array(
                                'label'=>$record['name'],
                                'value'=>$record['name'],
                                'id'=>$record['id'],
                        );
                }
                die(CJSON::encode($results));
        }

}
