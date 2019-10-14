<?php

class MallController extends FrontController
{
	public function filters()
	{
		return array(
			'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'roles'=>array(User::ROLE_POWERADMIN, User::ROLE_MALL_ADMIN),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

        public function actionIndex()
        {
		$criteria = new CDbCriteria(array(
			'condition' => 'status IN (:st1, :st2)',
			'order' => 'position ASC',
			'params' => array(':st1'=>MallPromo::STATUS_ACTIVE, ':st2'=>MallPromo::STATUS_DISABLED),
		));

		$dataProvider = new CActiveDataProvider('MallPromo', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 100,
			),
		));

		$userId = Yii::app()->getUser()->getId();
		$mall = MallBuild::model()->findByAttributes(array('admin_id'=>$userId));

		if ($mall===null) {
			throw new CHttpException(403);
		}

		$this->render('index', array(
				'dataProvider'=>$dataProvider,
				'mall'=>$mall,
			),
			false,
			array('profileMall', array(
				'user' => Yii::app()->getUser()->getModel(),
			)
		));
        }

	/**
	 * Создание и обновление записей
	 */
	public function actionUpdate()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsPostRequest() ) {
			throw new CHttpException(400);
		}

		$mallId = intval($request->getParam('mall_id'));
		$promoId = intval($request->getParam('promo_id'));

		$mall = MallBuild::model()->findByPk($mallId);
		$userId = Yii::app()->getUser()->getId();

		if ( $mall===null || $mall->admin_id != $userId ) {
			throw new CHttpException(404);
		}

		if ( empty($promoId) ) {
			$mallPromo = new MallPromo();
		} else {
			$mallPromo = MallPromo::model()->findByPk($promoId);
			if ($mallPromo===null) {
				throw new CHttpException(404);
			}
		}

		$mallPromo->attributes = $_POST['MallPromo'];

		$active = intval( $request->getParam('active') );
		if ($active) {
			$mallPromo->status = MallPromo::STATUS_ACTIVE;
		} else {
			$mallPromo->status = MallPromo::STATUS_DISABLED;
		}
		$mallPromo->user_id = $userId;
		$mallPromo->mall_id = $mall->id;

		if ($mallPromo->validate()) {
			if ($mallPromo->getIsNewRecord()) {
				$mallPromo->save(false);
			}
			$mallPromo->setImageType('mallpromo');
			$file = UploadedFile::loadImage($mallPromo, 'file');
			if ($file) {
				$mallPromo->image_id = $file->id;
			}

			$mallPromo->save(false);
			die ( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
		}

		die ( json_encode(array('error'=>true, 'message'=>''), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Получение формы для правки изображения
	 * @throws CHttpException
	 */
	public function actionAjaxGetItem()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$itemId = intval( $request->getParam('item_id') );

		/** @var $item MallPromo */
		$item = MallPromo::model()->findByPk($itemId);
		if ( $item===null || $item->status==MallPromo::STATUS_DELETED )
			throw new CHttpException(404);

		if ( $item->user_id != Yii::app()->getUser()->getId())
			throw new CHttpException(403);

		$html = $this->renderPartial('_editForm', array('item'=>$item), true);

		die( json_encode(array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Удаление баннера
	 * @throws CHttpException
	 */
	public function actionAjaxRemoveBanner()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$itemId = intval( $request->getParam('item_id') );

		/** @var $item MallPromo */
		$item = MallPromo::model()->findByPk($itemId);
		if ( $item===null )
			throw new CHttpException(404);

		if ( $item->user_id != Yii::app()->getUser()->getId())
			throw new CHttpException(403);

		$item->status = MallPromo::STATUS_DELETED;

		$item->save(false, array('status', 'update_time'));

		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Смена статуса баннера
	 * @throws CHttpException
	 */
	public function actionAjaxHideBanner()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$itemId = intval( $request->getParam('item_id') );
		$active = intval( $request->getParam('active') );

		/** @var $item MallPromo */
		$item = MallPromo::model()->findByPk($itemId);
		if ( $item===null )
			throw new CHttpException(404);

		if ( $item->user_id != Yii::app()->getUser()->getId())
			throw new CHttpException(403);

		if ($active) {
			$item->status = MallPromo::STATUS_ACTIVE;
		} else {
			$item->status = MallPromo::STATUS_DISABLED;
		}

		$item->save(false, array('status', 'update_time'));

		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Перремещение баннеров
	 * @throws CHttpException
	 */
	public function actionAjaxMoveItem()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$itemId = intval( $request->getParam('item_id') );
		$position = intval( $request->getParam('position') );

		/** @var $item MallPromo */
		$item = MallPromo::model()->findByPk($itemId);
		if ( $item===null )
			throw new CHttpException(404);

		if ( $item->user_id != Yii::app()->getUser()->getId())
			throw new CHttpException(403);

		$item->position = $position;
		$item->save(false, array('position', 'update_time'));

		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}
}