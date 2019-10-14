<?php

/**
 * @brief Блок Дизайнеры и архитекторы для главной страницы
 * @author Roman Kuzakov
 */
class DesignerController extends AdminController
{
        
        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'view', 'update', 'create', 'delete', 'group_action',
				'image_update', 'save_settings', 'switchstatus', 'user_info', 'description'),
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
        
        
        /*
         * Стандартный массив для инициализации модуля.
         * Так же служит образцом для разработчика.
         */

        public $data = array(
            'unitSettings' => array(
                'description' => array(
                    'status' => '0',
                    'data' => '',
                ),
                'largeOutput' => '1',
                'smallOutput' => '1',
                'services' => array(
                    'display' => false,
                    'data' => array(),
                ),
            ),
            'unitData' => array(
//                '1' => array(
//                    'name' => '1',
//                    'login' => '1',
//                    'image_id' => '1',
//                    'desc' => '1',
//                    'service_id' => '0',
//                    'status' => '1',
//                    'create_time' => '1',
//                    'update_time' => '1',
//                    'system_message' => array(
//                        'author_id' => '1',
//                        'comment' => '1',
//                        'create_time' => '1',
//                    ),
//                ),
            ),
        );

        /**
         * Вывод всех unitData с предоставлением выбора возможной операции над ними
         */
        public function actionIndex()
        {
                $unit = Unit::getUnitSettings('designer');

                if ($unit) {

                        // если юнит не настроен, запускается процедура инициализации базовой конфигурации
                        if (empty($unit->data))
                                $unit = Unit::setUnitData($unit, $this->data);

                        // массив с конфигурацией юнита
                        $settings = unserialize($unit->data);

                        $provider = new CUnitDataProvider($settings, array(
                                    'pagination' => array('pageSize' => Unit::PAGE_SIZE)
                                ));

                        return $this->render('index', array('unit' => $unit, 'provider' => $provider, 'settings' => $settings['unitSettings']));
                }
                throw new CHttpException(404);
        }

        /**
         * Операция создания unitData
         */
        public function actionCreate()
        {
                $unit = Unit::getUnitSettings('designer');

                if ($unit) {

                        // если юнит не настроен, запускается процедура инициализации базовой конфигурации
                        if (empty($unit->data))
                                $unit = Unit::setUnitData($unit, $this->data);

                        // массив с конфигурацией юнита
                        $settings = unserialize($unit->data);

                        // Проверка данных от формы и создание новой записи в случае успеха
                        if (!empty($_POST['user_id']) && !(array_key_exists($_POST['user_id'], $settings['unitData']))
                                && !empty($_POST['name']) && !empty($_POST['file_id'])
                                && !empty($_POST['status']) && !empty($_POST['desc'])
                        ) {

                                $user = User::model()->findByPk((int)$_POST['user_id']);
                                if ($user) {
                                        $settings['unitData'][$_POST['user_id']] = array(
                                            'name' => CHtml::encode($_POST['name']),
                                            'login' => $user->login,
                                            'image_id' => $_POST['file_id'],
                                            'desc' => CHtml::encode($_POST['desc']),
                                            'service_id' => CHtml::encode($_POST['service_id']),
                                            'status' => (int) $_POST['status'],
                                            'create_time' => time(),
                                            'update_time' => time(),
                                            'system_message' => array(),
                                        );

                                        // формирование внутреннего комментария
                                        if (!empty($_POST['system_message'])) {
                                                $settings['unitData'][$_POST['user_id']]['system_message'][] = array(
                                                    'author_id' => Yii::app()->user->id,
                                                    'comment' => CHtml::encode($_POST['system_message']),
                                                    'create_time' => time(),
                                                );
                                        }

                                        Unit::setUnitData($unit, $settings);

                                        Yii::app()->user->setFlash('design_unit_success', 'Анонс успешно создан');
                                        $this->redirect($this->createUrl($this->id . '/index'));
                                }
                        }

                        return $this->render('create', array('unit' => $unit, 'settings' => $settings['unitSettings']));
                }
                throw new CHttpException(404);
        }

        /**
         * Операция редактирования unitData
         * @param int $id 
         */
        public function actionUpdate($id = null)
        {
                $unit = Unit::getUnitSettings('designer');

                if ($unit && $id) {

                        // если юнит не настроен, останавливаем удаление
                        if (empty($unit->data))
                                throw new CHttpException(500);

                        // массив с конфигурацией юнита
                        $settings = unserialize($unit->data);

                        // Проверка данных от формы и обновление записи в случае успеха
                        if (!empty($_POST['user_id']) && array_key_exists($_POST['user_id'], $settings['unitData'])
                                && !empty($_POST['name']) && !empty($_POST['file_id'])
                                && !empty($_POST['status']) && !empty($_POST['desc'])
                        ) {

                               $user = User::model()->findByPk((int)$_POST['user_id']);
                               
                                if ($user) {
                                        $designerSettings = $settings['unitData'][$_POST['user_id']];

                                        $settings['unitData'][$_POST['user_id']] = array(
                                            'name' => CHtml::encode($_POST['name']),
                                            'login' => $user->login,
                                            'image_id' => $_POST['file_id'],
                                            'desc' => CHtml::encode($_POST['desc']),
                                            'service_id' => CHtml::encode($_POST['service_id']),
                                            'status' => (int) $_POST['status'],
                                            'create_time' => empty($designerSettings['create_time']) ? time() : $designerSettings['update_time'],
                                            'update_time' => time(),
                                            'system_message' => $designerSettings['system_message'],
                                        );

                                        // формирование внутреннего комментария
                                        if (!empty($_POST['system_message'])) {
                                                $settings['unitData'][$_POST['user_id']]['system_message'][] = array(
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

                        if (array_key_exists($id, $settings['unitData'])) {
                                return $this->render('update', array('id' => $id, 'unit' => $unit, 'settings' => $settings['unitSettings'], 'data' => $settings['unitData'][$id]));
                        }
                }
                throw new CHttpException(404);
        }

        /**
         * Операция просмотра unitData
         * @param int $id
         */
        public function actionView($id = null)
        {
                $unit = Unit::getUnitSettings('designer');

                if ($unit && $id) {

                        // если юнит не настроен, останавливаем просмотр
                        if (empty($unit->data))
                                throw new CHttpException(500);

                        // массив с конфигурацией юнита
                        $settings = unserialize($unit->data);

                        if (array_key_exists($id, $settings['unitData'])) {

                                $image = UploadedFile::model()->findByPk($settings['unitData'][$id]['image_id']);
                                return $this->render('view', array('id' => $id, 'unit' => $unit, 'settings' => $settings['unitSettings'], 'data' => $settings['unitData'][$id], 'image' => $image));
                        }
                }
        }

        /**
         * Операция удаления unitData
         * @param int $id 
         */
        public function actionDelete($id = null)
        {
                if (Yii::app()->request->isAjaxRequest && $id) {
                        $unit = Unit::getUnitSettings('designer');

                        if ($unit) {

                                // если юнит не настроен, останавливаем удаление
                                if (empty($unit->data))
                                        Yii::app()->end();

                                // массив с конфигурацией юнита
                                $settings = unserialize($unit->data);

                                if (!empty($settings['unitData'][$id]))
                                        unset($settings['unitData'][$id]);

                                Unit::setUnitData($unit, $settings);
                        }
                }

                Yii::app()->end();
        }

        /**
         * Операция редактирования блока название и подпись
         */
        public function actionDescription()
        {

                $unit = Unit::getUnitSettings('designer');

                if ($unit) {

                        // если юнит не настроен, останавливаем удаление
                        if (empty($unit->data))
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
        }

        /**
         * Операция смены статуса вкл/выкл
         * @param int $id 
         */
        public function actionSwitchstatus($id = null)
        {
                $this->layout = false;

                if (Yii::app()->request->isAjaxRequest && $id) {

                        $unit = Unit::getUnitSettings('designer');

                        if ($unit) {

                                // если юнит не настроен, останавливаем редактирование статуса
                                if (empty($unit->data))
                                        Yii::app()->end();

                                // массив с конфигурацией юнита
                                $settings = unserialize($unit->data);

                                if (!empty($settings['unitData'][$id])) {

                                        if ($settings['unitData'][$id]['status'] == Unit::STATUS_DISABLED)
                                                $settings['unitData'][$id]['status'] = Unit::STATUS_SMALL_LARGE;
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
                $this->layout = false;

                $action = Yii::app()->request->getParam('action');
                $ids = Yii::app()->request->getParam('ids');

                if (Yii::app()->request->isAjaxRequest && $ids) {

                        $unit = Unit::getUnitSettings('designer');

                        if ($unit) {

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
        }

        /**
         * Операция обновления глобальных настроек юнита
         */
        public function actionSave_settings()
        {
                $this->layout = false;

                $unit = Unit::getUnitSettings('designer');

                if (Yii::app()->request->isAjaxRequest && $unit) {

                        // если юнит не настроен, останавливаем редактирование настроек
                        if (empty($unit->data))
                                Yii::app()->end();

                        // массив с конфигурацией юнита
                        $settings = unserialize($unit->data);

                        if (Yii::app()->request->getParam('largeOutput'))
                                $settings['unitSettings']['largeOutput'] = Yii::app()->request->getParam('largeOutput');

                        if (Yii::app()->request->getParam('smallOutput'))
                                $settings['unitSettings']['smallOutput'] = Yii::app()->request->getParam('smallOutput');

                        Unit::setUnitData($unit, $settings);

                        Yii::app()->end();
                }
        }

        /**
         * Функция возвращает данный пользователя, вносимого в юнит
         */
        public function actionUser_info()
        {
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		
                $this->layout = false;

                if (Yii::app()->request->getParam('uid')) {
                        $user = User::model()->findByPk((int) Yii::app()->request->getParam('uid'));

                        if (!is_null($user)) {
				if (!empty($user->image_id))
					$image = $user->image_id;
				else
					$image = '';
                                $result = array(
                                    'name' => $user->name,
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
        public function actionImage_update($file_id = null)
        {

                $this->layout = false;

                if ($file_id) {
                        $image = UploadedFile::model()->findByPk($file_id);
                } else {
                        $image = new UploadedFile();


                        $image->author_id = Yii::app()->user->id;
                        $image->uploadfile = CUploadedFile::getInstance($image, 'uploadfile');

                        if ($image->uploadfile && $image->validate()) {
                                $formatName = time() . '_' . Amputate::rus2translit($image->uploadfile->getName());
				
				$path = 'unit/designer';
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

                if ($image) {
                        Yii::app()->cache->set('uid' . Yii::app()->user->id . '_' . $this->id . '_tmp_up_file_id', $image->id);
                        return $this->renderPartial('_imageUpdate', array('image' => $image));
                }

                Yii::app()->end();
        }

}