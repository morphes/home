<?php

class GroupOperationController extends AdminController
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
					User::ROLE_FREELANCE_PRODUCT,
				),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{
		// Текстовый результат обработки запроса
		$result = '';

		// Получаем ID категории и определяем действие
		$category_id = (int)Yii::app()->request->getParam('category_id');

		// Получаем значение нового статуса для группы
		$new_status = Yii::app()->request->getParam('new_status');

		if ($category_id > 0)
		{
			if (isset($_POST['similar'])) {
				$this->makeSimilar($category_id);
				$result = 'Аналогичные товары были успешно проставлены.';
			}
			elseif (isset($_POST['set_status'])) {
				$this->setStatus($category_id, $new_status);
				if (isset(Product::$statuses[$new_status]))
					$result = 'Статус «'.Product::$statuses[$new_status].'» успешно указан.';
			}

			elseif (isset($_POST['redirect_edit']))
				$this->makeRedirect($category_id);

			elseif (isset($_POST['clear_group']))
				$this->clearGroup($category_id);
		}


		$models = GroupOperation::model()->findAll(array(
			'order' => 'category_id ASC, product_id ASC'
		));

		$this->render('index', array(
			'models'      => $models,
			'new_status'  => $new_status,
			'category_id' => $category_id,
			'result'      => $result
		));
	}

	/**
	 * Добавляет (удаляет) указанный $id продукта в корзину.
	 *
	 * @param integer $id
	 *
	 *
	 */
	public function actionDealWithCart($id = null)
	{
		$success = false;
		$error = '';

		$product = Product::model()->findByPk((int)$id);

		if ( ! $product) {
			$error = 'Неизвестный товар';
			goto the_end;
		}

		// Получаем имя действия, которое нужно провести с товаров $id
		$action = Yii::app()->request->getParam('action');
		switch ($action)
		{
			case 'add': // Добавление товара в коризну
				$group = new GroupOperation();
				$group->product_id = $product->id;
				$group->category_id = $product->category_id;
				if ($group->save())
				{
					$success = true;
				} else {
					$error = $group->getError("product_id");
					goto the_end;
				}
				break;


			case 'delete': // Удаление товара из корзины
				$group = GroupOperation::model()->findByAttributes(array('product_id' => $product->id, 'user_id'=>Yii::app()->user->id));
				if ($group) {
					$group->delete();
					$success = true;
				} else {
					$error = 'Товар уже удален из корзины';
				}
				break;


			default:
				$error = 'Недоступное дествие с товаром';
				goto the_end;
		}


		the_end:
		die(json_encode(array(
			'success' => $success,
			'error' => $error
		)));
	}

	/**
	 * Возвращает кол-во элементов в корзине
	 */
	public function actionGetSizeCart()
	{
		die(json_encode(array(
			'success' => true,
			'qt' => GroupOperation::model()->count()
		)));
	}

	/**
	 * Делает все товары из группы $category_id аналогичными друг другу
	 *
	 * @param integer $category_id
	 */
	private function makeSimilar($category_id)
	{
		$models = GroupOperation::model()->findAllByAttributes(array(
			'category_id' => $category_id
		));

		// Пробегаем по каждому товаров
		foreach ($models as $model) {
			$product_id = $model->product_id;

			// Все товары, кроме него самого, добавляем в таблицу аналогичных товаров
			foreach ($models as $model2) {
				if ($product_id != $model2->product_id) {
					if ( ! Yii::app()->dbcatalog2->createCommand("SELECT product_id FROM cat_similar_product WHERE product_id = '".$product_id."' AND similar_product_id = '".$model2->product_id."'")->queryScalar()) {
						Yii::app()->dbcatalog2->createCommand("INSERT INTO cat_similar_product (`product_id`, `similar_product_id`) VALUES ('".$product_id."', '".$model2->product_id."')")->execute();
					}
				}
			}
		}
	}

	/**
	 * Удалает все добавленные товары из группы $category_id
	 *
	 * @param $category_id
	 */
	private function clearGroup($category_id)
	{
		GroupOperation::model()->deleteAllByAttributes(array(
			'category_id' => $category_id,
                        'user_id' => Yii::app()->user->id,
		));
	}


	private function setStatus($category_id, $new_status)
	{
		if (array_key_exists($new_status, Product::$statuses)) {
			$models = GroupOperation::model()->findAllByAttributes(array(
				'category_id' => $category_id
			));

			// Обновляем всем товарам группы статус и время обновления
			$transaction = Yii::app()->dbcatalog2->beginTransaction();
			try {
				foreach ($models as $model) {
					Product::model()->updateByPk($model->product_id, array(
						'status' => $new_status,
						'update_time' => time()
					));
				}

				$transaction->commit();

			} catch (Exception $e) {
				$transaction->rollback();
			}
		}
	}


	private function makeRedirect($category_id)
	{
		$models = GroupOperation::model()->findAllByAttributes(array(
			'category_id' => $category_id
		));

		$ids = array();
		foreach ($models as $model) {
			$ids[] = $model->product_id;
		}

		if ( ! empty($ids))
			$this->redirect('/catalog2/admin/product/update/category_id/'.$category_id.'/ids/'.implode(',', $ids));
	}
}