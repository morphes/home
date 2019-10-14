<?php

/**
 * @brief Idea block for main page
 * @author Alexey Shvedov
 */
class IdeaController extends AdminController
{

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'view', 'update', 'create', 'delete', 'ideainfo', 'imageupdate',
				'switchstatus', 'group_action', 'save_settings', 'description'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_JOURNALIST,
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
        
	const UNIT_KEY = 'idea';
	/*
	 * Стандартный массив для инициализации модуля.
	 * Так же служит образцом для разработчика.
	 */

	public $data = array(
		'unitSettings' => array(
			'output' => '1',
			'description' => array(
				'status' => '0',
				'data' => '',
			),
		),
		'unitData' => array(
/*			'1_1' => array(// key => $idea_id.'_'.$type_id
				'id' => '1',
				'name' => 'name 1',
				'type_id' => '1', // Interior, etc
				'status' => '1',
				'create_time' => '1',
				'update_time' => '1',
				'image_id' => '1',
				'desc' => '1',
				'system_message' => array(
					'author_id' => '1',
					'comment' => '1',
					'create_time' => '1',
				),
			),
 */
		),
 
	);

	public function beforeAction($action)
	{
		Yii::import('application.modules.idea.models.*');
		return true;
	}

	/**
	 * Вывод всех unitData с предоставлением выбора возможной операции над ними
	 */
	public function actionIndex()
	{
		$unit = Unit::getUnitSettings(self::UNIT_KEY);

		if (is_null($unit))
			throw new CHttpException(404);

		// если юнит не настроен, запускается процедура инициализации базовой конфигурации
		if (empty($unit->data))
			$unit = Unit::setUnitData($unit, $this->data);

		$settings = unserialize($unit->data);
		$provider = new CUnitDataProvider($settings, array(
				'pagination' => array('pageSize' => Unit::PAGE_SIZE)
			));

		return $this->render('index', array('unit' => $unit, 'provider' => $provider, 'settings' => $settings['unitSettings']));
	}

	private function getIdeaKey($ideaId, $ideaTypeId)
	{
		return $ideaId . '_' . $ideaTypeId;
	}

	private function getIdByKey($key)
	{
		$arr = explode('_', $key);
		return reset($arr);
	}

	/**
	 * Операция создания unitData
	 */
	public function actionCreate()
	{
		$unit = Unit::getUnitSettings(self::UNIT_KEY);

		if (is_null($unit))
			throw new CHttpException(404);

		// если юнит не настроен, запускается процедура инициализации базовой конфигурации
		if (empty($unit->data))
			$unit = Unit::setUnitData($unit, $this->data);

		// массив с конфигурацией юнита
		$settings = unserialize($unit->data);

		// Проверка данных от формы и создание новой записи в случае успеха
		$ideaId = Yii::app()->request->getParam('idea_id');

		$ideaTypeId = Yii::app()->request->getParam('type_id');
		$newKey = $this->getIdeaKey($ideaId, $ideaTypeId);


		if (!is_null($ideaId) && !is_null($ideaTypeId) && !empty(Config::$ideaTypes[$ideaTypeId]) && !empty($_POST['file_id'])
			&& ( empty($settings['userData'][$newKey]) )
			&& !empty($_POST['name']) && !empty($_POST['status'])
		) {

			if (CActiveRecord::model(Config::$ideaTypes[$ideaTypeId])->exists('id = :id', array(':id' => $ideaId))) {

				$settings['unitData'][$newKey] = array(
					'id' => $ideaId,
					'name' => CHtml::encode($_POST['name']),
					'type_id' => $ideaTypeId,
					'image_id' => $_POST['file_id'],
					'status' => (int) $_POST['status'],
					'create_time' => time(),
					'update_time' => time(),
					'system_message' => array(),
				);

				// формирование внутреннего комментария
				if (!empty($_POST['system_message'])) {
					$settings['unitData'][$newKey]['system_message'][] = array(
						'author_id' => Yii::app()->user->id,
						'comment' => CHtml::encode($_POST['system_message']),
						'create_time' => time(),
					);
				}

				Unit::setUnitData($unit, $settings);

				Yii::app()->user->setFlash('design_unit_success', 'Анонс успешно создан');
				$this->redirect($this->createUrl('index'));
			}
		}

		return $this->render('create', array('unit' => $unit, 'settings' => $settings['unitSettings']));
	}

	/**
	 * Операция редактирования unitData
	 * @param int $key 
	 */
	public function actionUpdate($key = null)
	{
		$unit = Unit::getUnitSettings(self::UNIT_KEY);

		if (is_null($unit) || is_null($key))
			throw new CHttpException(404);

		// если юнит не настроен, останавливаем удаление
		if (empty($unit->data))
			throw new CHttpException(500);

		// массив с конфигурацией юнита
		$settings = unserialize($unit->data);

		// Проверка данных от формы и создание новой записи в случае успеха

		$ideaId = Yii::app()->request->getParam('idea_id', $this->getIdByKey($key));
		$ideaTypeId = Yii::app()->request->getParam('type_id');
		$newKey = $this->getIdeaKey($ideaId, $ideaTypeId);

		// Проверка данных от формы и обновление записи в случае успеха
		if (!is_null($ideaId) && !is_null($ideaTypeId) && !empty(Config::$ideaTypes[$ideaTypeId]) && !empty($_POST['file_id'])
			&& !empty($_POST['name']) && !empty($_POST['status'])
		) {
			if (CActiveRecord::model(Config::$ideaTypes[$ideaTypeId])->exists('id = :id', array(':id' => $ideaId))) {

				$ideaSettings = $settings['unitData'][$key];
				unset($settings['unitData'][$key]);
				$key = $newKey; // update key value for render

				$settings['unitData'][$newKey] = array(
					'id' => $ideaId,
					'type_id' => $ideaTypeId,
					'name' => CHtml::encode($_POST['name']),
					'image_id' => $_POST['file_id'],
					'status' => (int) $_POST['status'],
					'create_time' => empty($ideaSettings['unitData'][$newKey]['create_time']) ? time() : $ideaSettings['unitData'][$newKey]['create_time'],
					'update_time' => time(),
					'system_message' => empty($ideaSettings['unitData'][$newKey]['system_message']) ? array() : $ideaSettings['unitData'][$newKey]['system_message'],
				);

				// формирование внутреннего комментария
				if (!empty($_POST['system_message'])) {
					$settings['unitData'][$newKey]['system_message'][] = array(
						'author_id' => Yii::app()->user->id,
						'comment' => CHtml::encode($_POST['system_message']),
						'create_time' => time(),
					);
				}

				Unit::setUnitData($unit, $settings);

				Yii::app()->user->setFlash('design_unit_success', 'Анонс успешно обновлен');
				$this->redirect($this->createUrl('index'));
			}
		}

		if (!empty($settings['unitData'][$key])) {
			return $this->render('update', array('id' => $ideaId,
				'key' => $key,
				'unit' => $unit,
				'settings' => $settings['unitSettings'],
				'data' => $settings['unitData'][$key]));
		}
		throw new CHttpException(500);
	}

	/**
	 * Операция просмотра unitData
	 * @param int $key
	 */
	public function actionView($key = null)
	{
		$unit = Unit::getUnitSettings(self::UNIT_KEY);

		if (is_null($unit) || is_null($key))
			throw new CHttpException(404);

		// если юнит не настроен, останавливаем просмотр
		if (empty($unit->data))
			throw new CHttpException(500);

		// массив с конфигурацией юнита
		$settings = unserialize($unit->data);

		if (!empty($settings['unitData'][$key])) {
			$id = $this->getIdByKey($key);
			$image = UploadedFile::model()->findByPk($settings['unitData'][$key]['image_id']);
			return $this->render('view', array('id' => $id,
				'key' => $key,
				'unit' => $unit,
				'settings' => $settings['unitSettings'],
				'data' => $settings['unitData'][$key],
				'image' => $image
			));
		}
	}

	/**
	 * Операция удаления unitData
	 * @param int $key 
	 */
	public function actionDelete($key = null)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$unit = Unit::getUnitSettings(self::UNIT_KEY);
		$this->layout = false;

		if (is_null($key) || is_null($unit) || empty($unit->data))
			return $this->renderText('error');

		// массив с конфигурацией юнита
		$settings = unserialize($unit->data);

		if (!empty($settings['unitData'][$key]))
			unset($settings['unitData'][$key]);

		Unit::setUnitData($unit, $settings);

		return $this->renderText('success');
	}

	/**
	 * Операция редактирования блока название и подпись
	 */
	public function actionDescription()
	{

		$unit = Unit::getUnitSettings(self::UNIT_KEY);

		if (is_null($unit) || empty($unit->data))
			throw new CHttpException(404);

		// массив с конфигурацией юнита
		$settings = unserialize($unit->data);

		if (!empty($_POST['Unit']['alias'])) {
			$unit->alias = $_POST['Unit']['alias'];
			$unit->save();
		}
		
		if (!empty($_POST['data'])) {
			$settings['unitSettings']['description'] = array(
				'status' => (int) @$_POST['status'],
				'data' => CHtml::encode($_POST['data']),
			);

			Unit::setUnitData($unit, $settings);
		}

		return $this->render('description', array('unit' => $unit, 'settings' => $settings['unitSettings']));
	}

	/**
	 * Операция смены статуса вкл/выкл
	 * @param int $id 
	 */
	public function actionSwitchstatus($id = null)
	{
		if (Yii::app()->request->isAjaxRequest && $id) {

			$unit = Unit::getUnitSettings(self::UNIT_KEY);

			if (!is_null($unit)) {

				// если юнит не настроен, останавливаем редактирование статуса
				if (empty($unit->data))
					Yii::app()->end();

				// массив с конфигурацией юнита
				$settings = unserialize($unit->data);

				if (!empty($settings['unitData'][$id])) {

					if ($settings['unitData'][$id]['status'] == Unit::STATUS_DISABLED)
						$settings['unitData'][$id]['status'] = Unit::STATUS_ENABLED;
					else
						$settings['unitData'][$id]['status'] = Unit::STATUS_DISABLED;
					Unit::setUnitData($unit, $settings);
				}
			}
		}
	}

	/**
	 * Групповые операции удаления, включения, выключения unitData
	 * @param string $action
	 * @param array $ids 
	 */
	public function actionGroup_action()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$this->layout = false;

		$action = Yii::app()->request->getParam('action');
		$ids = Yii::app()->request->getParam('ids');

		if (!empty($ids)) {
			$unit = Unit::getUnitSettings(self::UNIT_KEY);

			if (!is_null($unit)) {
				// если юнит не настроен, останавливаем редактирование статуса
				if (empty($unit->data))
					Yii::app()->end();

				// массив с конфигурацией юнита
				$settings = unserialize($unit->data);

				switch ($action) {
					case 'disable':
						$status = Unit::STATUS_DISABLED;
						break;
					case 'enable':
						$status = Unit::STATUS_ENABLED;
						break;
					case 'delete':
						$status = null;
						break;
					default:
						Yii::app()->end();
						break;
				}

				// обновление статусов (если статус не задан, то удаление unitData)
				foreach ($ids as $id) {
					if (!empty($settings['unitData'][$id]) && $status)
						$settings['unitData'][$id]['status'] = $status;

					elseif (!empty($settings['unitData'][$id]) && !$status)
						unset($settings['unitData'][$id]);
				}

				Unit::setUnitData($unit, $settings);
			}
		}
		return;
	}

	/**
	 * Операция обновления глобальных настроек юнита
	 */
	public function actionSave_settings()
	{
		if (!Yii::app()->request->isPostRequest)
			throw new CHttpException(404);

		$unit = Unit::getUnitSettings(self::UNIT_KEY);

		if (!is_null($unit)) {
			// если юнит не настроен, останавливаем редактирование настроек
			if (empty($unit->data))
				Yii::app()->end();

			// массив с конфигурацией юнита
			$settings = unserialize($unit->data);

			if (!is_null(Yii::app()->request->getParam('output')))
				$settings['unitSettings']['output'] = Yii::app()->request->getParam('output');

			Unit::setUnitData($unit, $settings);
		}
		if (Yii::app()->request->isAjaxRequest) {
			$this->layout = false;
			return $this->renderText('success');
		}
		$this->redirect('index');
	}

	/**
	 * Get idea info 
	 */
	public function actionIdeainfo()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$this->layout = false;

		$ideaId = Yii::app()->request->getParam('ideaId');
		$ideaTypeId = Yii::app()->request->getParam('typeId');

		if (!is_null($ideaId) && !empty(Config::$ideaTypes[$ideaTypeId])) {
			$idea = CActiveRecord::model(Config::$ideaTypes[$ideaTypeId])->findByPk($ideaId);

			if (!is_null($idea)) {
				if (!empty($idea->image_id))
					$image = $idea->image_id;
				else
					$image = '';

				$result = array(
					'name' => $idea->name,
					'image' => $image,
				);
				return $this->renderText(CJSON::encode($result));
			}
		}
		return $this->renderText(CJSON::encode(array('error' => 'Некорректный ID')));
	}

	/**
	 * Вывод iframe с изображением, а так же сохранение нового изображения
	 */
	public function actionImageupdate($file_id = null)
	{

		$this->layout = false;

		if (!is_null($file_id)) {
			$image = UploadedFile::model()->findByPk($file_id);
		} else {
			$image = new UploadedFile();


			$image->author_id = Yii::app()->user->id;
			$image->uploadfile = CUploadedFile::getInstance($image, 'uploadfile');

			if ($image->uploadfile && $image->validate()) {
				$formatName = time() . '_' . Amputate::rus2translit($image->uploadfile->getName());
				
				$path = 'unit/idea';
				$filePath = UploadedFile::UPLOAD_PATH.'/'.$path;
				if (!file_exists($filePath))
					mkdir ($filePath, 0700, true);
				
				$image->uploadfile->saveAs($filePath . '/' . $formatName);
				$image->path = $path;
				$image->name = Amputate::getFilenameWithoutExt($formatName);
				$image->ext = $image->uploadfile->getExtensionName();
				$image->size = $image->uploadfile->getSize();
				$image->type = UploadedFile::IMAGE_TYPE;
				$image->save();
				$image->generatePreview(Config::$preview['crop_150']);
			}
		}

		if (!is_null($image) && !$image->getIsNewRecord()) {
			return $this->renderPartial('_imageUpdate', array('image' => $image));
		}

		Yii::app()->end();
	}

}