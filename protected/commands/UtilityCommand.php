<?php
/**
 * Description of UtilityCommand
 * Обработчики для однократного вызова, восстановления и тп
 *
 * @author alexsh
 */
class UtilityCommand extends CConsoleCommand
{
	/**
	 * Обновление списка городов, в которых оказываются услуги
	 */
	public function actionServiceCityList()
	{
		$start = time();
		echo "ServiceCityList \n";
		echo 'Time start: ' . date('d-M-Y H:i:s') . "\n";
		Yii::import('application.modules.member.models.UserServicecity');

		$sql = 'SELECT id, city_id FROM `user`';

		$command = Yii::app()->db->createCommand($sql);
		$users = $command->queryAll();

		foreach($users as $user) {
			$locations = UserServicecity::model()->findAllByAttributes(array('user_id'=>$user['id']) );
			$cityList = '';
			$cnt = 0;
			foreach ($locations as $location) {
				if (!is_null($user['city_id']) && $location->city_id == $user['city_id'])
					continue;
				if ($cnt > 0)
					$cityList .= ', ';
				$cityList .= $location->getLocationLabel();
				$cnt++;
			}
			Yii::app()->db->createCommand()->update('user_data', array('service_city_list'=>$cityList), 'user_id=:uid', array( ':uid'=>$user['id'] ));
			unset ($locations);
		}

		echo 'Time stop: ' . date('d-M-Y H:i:s') . "\n";
		echo 'Total time: ' . (time() - $start) . "\n\n";
	}

	/**
	 * Переопределение тэгов interior_content
	 */
	public function actionRepairTags()
	{
		$start = time();
		echo "ServiceCityList \n";
		echo 'Time start: ' . date('d-M-Y H:i:s') . "\n";

		Tag::model()->deleteAll();

		echo 'Interior Content'."\n";
		Yii::import('application.modules.idea.models.*');

		$sql = 'SELECT id, tag FROM interior_content';

		$intContents = Yii::app()->db->createCommand($sql)->queryAll();
		$cnt=0;
		foreach ($intContents as $content) {
			if ($cnt%1000 == 0)
				echo $cnt."\n";
			InteriorContentTag::updateTags($content['id'], $content['tag']);
			$cnt++;
		}

		echo 'Architecture '."\n";

		$sql = 'SELECT id, tag FROM architecture';

		$intContents = Yii::app()->db->createCommand($sql)->queryAll();
		$cnt=0;
		foreach ($intContents as $content) {
			if ($cnt%1000 == 0)
				echo $cnt."\n";
			$arr = Tag::saveTagsFromString($content['tag']);

			// Сначала удаляем все привязки для проекта
			Yii::app()->getDb()->createCommand('DELETE FROM architecture_tag WHERE architecture_id = '.$content['id'])->execute();

			if (!empty($arr))
			{
				$sql = 'INSERT INTO architecture_tag (`tag_id`, `architecture_id`) VALUES ';
				for ($i = 0, $ci = count($arr); $i < $ci; $i++)
				{
					$tagId = (int)$arr[$i];

					$sql .= "('{$tagId}', '{$content['id']}')";
					$sql .= ($i != $ci - 1) ? ',' : '';
				}

				Yii::app()->getDb()->createCommand($sql)->execute();
			}
			$cnt++;
		}

		echo 'Time stop: ' . date('d-M-Y H:i:s') . "\n";
		echo 'Total time: ' . (time() - $start) . "\n\n";
	}

	public function actionRestoreProjectQt()
	{
		$start = time();
		echo __METHOD__." \n";
		echo 'Time start: ' . date('d-M-Y H:i:s') . "\n";

		Yii::import('application.modules.idea.models.*');
		$sql = 'SELECT user_id, service_id FROM user_service WHERE service_id=2';
		$userServices = Yii::app()->db->createCommand($sql)->queryAll();

		foreach ($userServices as $userService) {
			$tmp1 = Interiorpublic::model()->scopeOwnPublic($userService['user_id'], $userService['service_id'])->count();
			$tmp2 = Interior::model()->scopeOwnPublic($userService['user_id'], $userService['service_id'])->count();
			$project_qt = $tmp1 + $tmp2;

			Yii::app()->db->createCommand()->update('user_service_data', array(
				'project_qt'=>$project_qt,
			), 'user_id=:uid AND service_id=:sid', array(':uid'=>$userService['user_id'], ':sid'=>$userService['service_id']));

			Yii::app()->gearman->appendJob('userService', array('userId'=>$userService['user_id'], 'serviceId'=>$userService['service_id']));
		}

		echo 'Time stop: ' . date('d-M-Y H:i:s') . "\n";
		echo 'Total time: ' . (time() - $start) . "\n\n";
	}

	/**
	 * Тест производительности кэширования путей до превьюх, показал, что на больших объемах
	 * кэш не эффективен (ActiveRecord)
	 */
	/*public function actionTest()
	{
		$users = User::model()->findAll(array('limit'=>5000));
		$b = microtime(true);
		foreach ($users as $user) {
			$user->getPreview(Config::$preview['crop_150']);
		}
		$e = microtime(true);

		echo $first = $e - $b;

		echo "\n";

		$b = microtime(true);
		foreach ($users as $user) {
			$user->getPreview2(Config::$preview['crop_150']);
		}
		$e = microtime(true);

		echo $second = $e - $b;

		echo "\n";

		echo ($first) / ($second)."\n";
	}*/

	/**
	 * Добавляет в очередь на ресайз изображения интерьеров и планировок
	 */
	public function actionResizeImage()
	{
		$start = time();
		echo __METHOD__." \n";
		echo 'Time start: ' . date('d-M-Y H:i:s') . "\n";

		// interior content items
		$criteria = new CDbCriteria();
		$criteria->select = 'DISTINCT *';
		$criteria->join = 'INNER JOIN idea_uploaded_file as iuf ON iuf.uploaded_file_id = t.id AND iuf.idea_type_id=:type';
		$criteria->params = array(':type'=>Config::INTERIOR);

		$data = UploadedFile::model()->findAll($criteria);

		$config = array(
			array(520, 0, 'resize', 90, false),
		);

		foreach ($data as $uFile) {
			$imgInfo = array(
				'path' => $uFile->path,
				'name' => $uFile->name,
				'ext' => $uFile->ext,
			);
			Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config'=>$config));
		}

		$criteria = new CDbCriteria();
		$criteria->select = 'DISTINCT *';
		$criteria->join = 'INNER JOIN layout_uploaded_file as luf ON luf.uploaded_file_id = t.id';

		$data = UploadedFile::model()->findAll($criteria);
		foreach ($data as $uFile) {
			$imgInfo = array(
				'path' => $uFile->path,
				'name' => $uFile->name,
				'ext' => $uFile->ext,
			);
			Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config'=>$config));
		}

		echo 'Time stop: ' . date('d-M-Y H:i:s') . "\n";
		echo 'Total time: ' . (time() - $start) . "\n\n";
	}

	/**
	 * Перемещает одни категории в каталоге товаров в другие.
	 */
	public function actionMoveCategory()
	{
		Yii::import('application.modules.catalog.models.*');

		// куда перемещать
		$target_id = 37;

		/**
		 * Перемещение и удаление категорий
		 */

		// что перемещать в target
		$for_move = array(48, 40);
		// что удалить после перемещения
		$for_delete = array(38);

		// получение target
		$target = Category::model()->findByPk($target_id);
		if(!$target)
			throw new Exception('Target not found');
		// перемещение в target
		foreach($for_move as $cid) {
			$cat = Category::model()->findByPk($cid);
			$cat->moveAsLast($target);
		}
		// удаление
		foreach($for_delete as $cid) {
			$cat = Category::model()->findByPk($cid);
			$cat->deleteNode();
		}
	}

	/**
	 * Обработка данных GeoIp и совмещение с нашей базой городов
	 */
	public function actionMergeGeoipCity()
	{
		$connection = Yii::app()->db;

		// выборка блоков для обработки
		$blocks_locs = $connection->createCommand()->from('geoip_blocks b')->join('geoip_locs l', 'b.loc_id=l.loc_id')->queryAll();

		// список городов, для которых найдены соответствия в базе
		$used_locs = array();

		// кеш переводов
		$translated_cities = array();

		foreach($blocks_locs as $bl) {

			// поиск по eng_name
			$city = $connection->createCommand()->from('city')->where('eng_name=:name', array(':name'=>strtolower($bl['city'])))->queryRow();

			// поиск по русскому названию города
			if(!$city) {

				// проверка наличия в кеше перевода города
				$key = crc32($bl['city']);
				if(!isset($translated_cities[$key])) {

					// получение перевода города от яндекса
					try {
						$trans = new SimpleXMLElement(Yii::app()->curl->run('http://translate.yandex.net/api/v1/tr/translate?lang=en-ru&text='.urlencode($bl['city'])));
					} catch (Exception $e) {
						echo 'Not found translation for ' . $bl['city'] . "\n";
						continue;
					}

					// кеширование переведенного города
					if(isset($trans->text[0]))
						$translated_cities[$key] = $trans->text[0];
					else
						$translated_cities[$key] = '';

					$trans_text = $translated_cities[$key];
					echo 'Founded translation for ' . $bl['city'] . ' - ' . $trans_text . "\n";

				} else {
					// взятие перевода из кеша
					$trans_text = $translated_cities[$key];
					echo 'Founded in cache translation for ' . $bl['city'] . ' - ' . $trans_text . "\n";
				}

				$city = $connection->createCommand()->from('city')->where('name=:name', array(':name'=>$trans_text))->queryRow();
			} else {

				echo 'Founded in city db for ' . $bl['city'] . "\n";
			}

			if($city) {
				// список локаций для удаления
				$used_locs[$bl['loc_id']] = $bl['loc_id'];

				// вставка обработанного блока в итоговую таблицу
				$connection->createCommand()->insert('geoip', array(
					'start'=>$bl['start'],
					'end'=>$bl['end'],
					'country'=>$bl['country'],
					'city_id'=>$city['id'],
				));

				// удаление обработанных блоков
				$connection->createCommand()->delete('geoip_blocks', 'geoip_blocks.start=:st and geoip_blocks.end=:en and geoip_blocks.loc_id=:lid', array(
					':st'=>$bl['start'],
					':en'=>$bl['end'],
					':lid'=>$bl['loc_id'],
				));
			}
		}
		// очистка использованных локаций
		$connection->createCommand()->delete('geoip_locs', 'loc_id in ('.implode(',', $used_locs).')');
	}

	public function actionResizeProductImages()
	{
		$start = time();
		echo __METHOD__." \n";
		echo 'Time start: ' . date('d-M-Y H:i:s') . "\n";

		$config = array(
			'resize_510' => array(510, 510, 'resize', 80, 'decrease' => true),
		);

		$criteria = new CDbCriteria();
		$criteria->select = 't.*';
		$criteria->join = 'INNER JOIN cat_product_image pi ON pi.file_id = t.id';
		$criteria->params = array(':type'=>Config::INTERIOR);

		$data = UploadedFile::model()->findAll($criteria);

		foreach ($data as $uFile) {
			$imgInfo = array(
				'path' => $uFile->path,
				'name' => $uFile->name,
				'ext' => $uFile->ext,
			);
			Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config'=>$config, 'forceGenerate'=>true));
		}

		$configCover = array(
			'resize_510' => array(510, 510, 'resize', 80, 'decrease' => true),
		);

		$criteria = new CDbCriteria();
		$criteria->select = 't.*';
		$criteria->join = 'INNER JOIN cat_product p ON p.image_id = t.id';

		$data = UploadedFile::model()->findAll($criteria);
		foreach ($data as $uFile) {
			$imgInfo = array(
				'path' => $uFile->path,
				'name' => $uFile->name,
				'ext' => $uFile->ext,
			);
			Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config'=>$configCover, 'forceGenerate'=>true));
		}

		echo 'Time stop: ' . date('d-M-Y H:i:s') . "\n";
		echo 'Total time: ' . (time() - $start) . "\n\n";
	}


	/**
	 * Собирает и отправляет инвайты зарегестрированным пользователям, но
	 * не активировавшим себя.
	 */
	public function actionSendInvite()
	{
		// Определяем функцию для оптарвки писем и сохранения пароля.
		$sendInviteForUsers = function($users, $manager){
			foreach ($users as $id) {

				// Ищем пользователя в БД
				$user = User::model()->findByPk($id);

				if (!$user) {
					echo "Пользователь #" . $id . " не найден\n";
				}

				$user->password = Amputate::generatePassword();
				$unsafe_pass = $user->password;
				$user->password = md5($user->password);
				$user->password2 = md5($user->password2);

				$user->activateKey = User::generateActivateKey();
				$user->status = User::STATUS_VERIFYING;
				$user->referrer_id = $manager->id;


				$user->save();

				// Сохраняем новый пароль в редис
				Yii::app()->redis->set($user->id . '_pass', serialize($unsafe_pass));


				/* ------------------------------------
				 *  Отправка письма пользователю
				 * ------------------------------------
				 */
				Yii::app()->mail->create('invite')
					->from(array('email' => $manager->email))
					->to($user->email)
					->notifier(true)
					->params(array(
					'user_name' 	=> $user->name,
					'manager_name' 	=> $manager->name,
					'activate_link'	=> CHtml::link(
						Yii::app()->homeUrl . '/site/activation/key/' . $user->activateKey,
						Yii::app()->homeUrl . '/site/activation/key/' . $user->activateKey
					),
					'sign_C' => Yii::app()->mail->create('sign_C')->params(array(
						'manager_name'  => $manager->name,
						'manager_email' => $manager->email,
						'manager_skype' => $manager->data->skype,
						'manager_phone' => $manager->phone,
					))->useView(false)->prepare()->getMessage(),
				))
					->send();
			}
		};



		// Елена Верещагина
		$manager = User::model()->findByPk(22332);

		if (!$manager) {
			throw new CHttpException(400, 'Менеджер не найден');
		}

		/* Получаем пользователей:
		 * - физ и юр лица
		 * - на подтверждении
		 * - Новосибирск
		 * - дизайн интерьеров
		 */

		$sql = '
			SELECT id
			FROM user
			INNER JOIN user_service_data usd
				ON usd.user_id = user.id
			WHERE
				user.`status` = 7
				AND
				(user.role = 3 OR user.role = 4)
				AND
				usd.service_id = 2
				AND
				user.city_id = :cid
			LIMIT 100
		';

		// Массив идентификаторов пользователей по Москве
		$users = Yii::app()->db
			->createCommand($sql)
			->bindValue(':cid', 4400)
			->queryColumn();


		$sendInviteForUsers($users, $manager);


		// Массив идентификаторов пользователей по Новосибирску
		$users = Yii::app()->db
			->createCommand($sql)
			->bindValue(':cid', 4549)
			->queryColumn();

		$sendInviteForUsers($users, $manager);
	}

	public function actionCheckFiles()
	{
		$sql = 'SELECT * FROM tmp_arch_files WHERE status=2';

		$success = 0;
		$error = 0;

		$data = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($data as $item) {
			$imagePath = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.$item['path'].'/'.$item['name'].'.'.$item['ext'];
			if (file_exists($imagePath)) {
				$error++;
				echo $imagePath."\n";
			} else {
				$success++;
			}
		}

		echo 'SUCCESS: '.$success.' ERROR: '.$error."\n";
	}

	public function actionRepairPhotoArch()
	{
		Yii::import('application.modules.idea.models.*');

		$arch = Architecture::model()->findAll();
		foreach ($arch as $item) {
			$item->countPhotos();
		}
	}

	public function actionMigrateArch()
	{
		Yii::import('application.modules.idea.models.Architecture');
		$sql = 'SELECT DISTINCT uf.*, a.id as aid FROM uploaded_file as uf '
			.'INNER JOIN architecture as a ON a.image_id=uf.id '
			.'LEFT join tmp_arch_files as tmp ON tmp.item_id=a.id AND tmp.ismain=1 '
			.'where NOT isnull(a.image_id) AND isnull(tmp.id)';

		$data = Yii::app()->db->createCommand($sql)->queryAll();

		/** @var $imgComp ImageComponent */
		$imgComp = Yii::app()->img;
		// 0 - default, 1 - ok, 2 -error

		$cnt = 0;
		foreach ($data as $item) { // Обход по главным картинкам
			$transaction = Yii::app()->db->beginTransaction();
			try {
				$sql = 'insert IGNORE INTO tmp_arch_files (id, author_id, path, `name`, ext, original_name, '
					.'size, `type`, `desc`, keywords, update_time, create_time, item_id, ismain, status) '
					."VALUES ({$item['id']}, {$item['author_id']}, '{$item['path']}', '{$item['name']}', '{$item['ext']}', '{$item['original_name']}', "
					."{$item['size']}, {$item['type']}, '{$item['desc']}', '{$item['keywords']}', {$item['update_time']}, {$item['create_time']}, "
					."{$item['aid']}, 1, ";

				$imagePath = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.$item['path'].'/'.$item['name'].'.'.$item['ext'];
				$name = !empty($item['original_name']) ? $item['original_name'] : $item['name'].'.'.$item['ext'];
				$imgId = false;
				if (is_file($imagePath)) {
					$imgId = $imgComp->putImage($imagePath, $name, $item['author_id'], $item['desc']);
				}

				if ($imgId===null) {
					throw new CException('Invalid file insert');
				}

				if ($imgId===false) {
					$sql .= '2)';
				} else {
					$sql .= '1)';
					$imgComp->generatePreview($imgId, Architecture::model()->getImageConfig(false), false);
					$imgComp->generatePreview($imgId, Architecture::model()->getImageConfig(true), true);
				}
				Yii::app()->db->createCommand($sql)->execute();

				$uid = $item['id'];
				$aid = $item['aid'];
				if ($imgId!==false) {
					$sql = 'UPDATE architecture SET image_id=:fid WHERE id=:aid';
					$rows = Yii::app()->db->createCommand($sql)->bindParam(':fid', $imgId)->bindParam(':aid', $aid)->execute();
					if ($rows != 1)
						throw new CException('Inavid update row id='.$aid);
				} else {
					$sql = 'UPDATE architecture SET image_id=NULL WHERE id=:aid';
					Yii::app()->db->createCommand($sql)->bindParam(':aid', $aid)->execute();
				}

				$sql = 'DELETE FROM uploaded_file WHERE id=:id';
				$rows = Yii::app()->db->createCommand($sql)->bindParam(':id', $uid)->execute();
				if ($rows != 1)
					throw new CException('Inavid delete row id='.$uid);

				$transaction->commit();
			} catch(Exception $e) {
				print_r($item);
				$transaction->rollback();
				throw $e;
			}
			$cnt++;
			echo "\r".$cnt;
		}
		echo "\n STEP2 \n";

		$sql = 'SELECT DISTINCT uf.*, iuf.item_id as item_id FROM uploaded_file as uf '
			.'INNER JOIN idea_uploaded_file as iuf ON iuf.uploaded_file_id=uf.id '
			.'LEFT JOIN tmp_arch_files as tmp ON tmp.item_id=iuf.item_id AND tmp.ismain=0 '
			.'where isnull(tmp.id) AND iuf.idea_type_id=2';

		$data = Yii::app()->db->createCommand($sql)->queryAll();
		echo "\n START STEP2 \n";

		foreach ($data as $item) { // Обход по главным картинкам


			$transaction = Yii::app()->db->beginTransaction();
			try {
				$sql = 'insert IGNORE INTO tmp_arch_files (id, author_id, path, `name`, ext, original_name, '
					.'size, `type`, `desc`, keywords, update_time, create_time, item_id, ismain, status) '
					."VALUES ({$item['id']}, {$item['author_id']}, '{$item['path']}', '{$item['name']}', '{$item['ext']}', '{$item['original_name']}', "
					."{$item['size']}, {$item['type']}, '{$item['desc']}', '{$item['keywords']}', {$item['update_time']}, {$item['create_time']}, "
					."{$item['item_id']}, 0, ";

				$imagePath = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.$item['path'].'/'.$item['name'].'.'.$item['ext'];
				$name = !empty($item['original_name']) ? $item['original_name'] : $item['name'].'.'.$item['ext'];
				$imgId = false;
				if (is_file($imagePath)) {
					$imgId = $imgComp->putImage($imagePath, $name, $item['author_id'], $item['desc']);
				}

				if ($imgId===null) {
					throw new CException('Invalid file insert');
				}

				if ($imgId===false) {
					$sql .= '2)';
				} else {
					$sql .= '1)';
					$imgComp->generatePreview($imgId, Architecture::model()->getImageConfig(false), false);
					$imgComp->generatePreview($imgId, Architecture::model()->getImageConfig(true), true);
				}
				Yii::app()->db->createCommand($sql)->execute();

				$uid = $item['id'];
				$aid = $item['item_id'];

				if ($imgId!==false) {
					$sql = 'UPDATE idea_uploaded_file SET uploaded_file_id=:fid WHERE item_id=:aid AND idea_type_id=2 AND uploaded_file_id=:uf';
					Yii::app()->db->createCommand($sql)->bindParam(':fid', $imgId)->bindParam(':aid', $aid)->bindParam(':uf', $uid)->execute();
				} else {
					$sql = 'DELETE FROM idea_uploaded_file WHERE idea_type_id=2 AND item_id=:aid AND uploaded_file_id=:uf';
					Yii::app()->db->createCommand($sql)->bindParam(':aid', $aid)->bindParam(':uf', $uid)->execute();
				}

				$sql = 'DELETE FROM uploaded_file WHERE id=:id';
				Yii::app()->db->createCommand($sql)->bindParam(':id', $uid)->execute();

				$transaction->commit();
			} catch(Exception $e) {
				$transaction->rollback();
				print_r($item);
				echo "\n";
				throw $e;
			}
			$cnt++;
			echo "\r".$cnt;

		}
	}

	/**
	 * Рассылает письма российским производителям.
	 */
	public function actionSendForRusVendors()
	{
		// В массиве лежит 100 email'ов
		$emails = array(
			'salon@neopoliscasa.ru','info@aldo.ru','info@auping.ru','info@comfortno.ru','info@estadoor.ru','2080436@gmail.com','2129988@mail.ru','555sa@rambler.ru','6429347@mail.ru','alexandr@mirzerkal.ru ','amini@divan-tam.ru','arenasib@arenasib.ru','artmax_grand@mail.ru','atamanmebel@yandex.ru','barcelona-nsk@mail.ru','bion-kora@ngs.ru','boconcept@boconcept.ru','borovikov@lerom.ru','capitole@capitole.ru','clients@mebelkit.ru ','davincinov@gmail.com','deltansk2006@yandex.ru','dl32@mail.ru','door@pannelloporta.ru','dveri-kredo-01@yandex.ru','elismebel.info@yandex.ru','emfa@renet.ru','fabricstyle@rambler.ru','fgrandy@mail.ru','gdl@theodore.ru','gonzalux@mail.ru','info@gonzalux.ru','grand@belfan.ru','grand@grande.ru','grand@kxd.ru','grand@olimar.ru','olimar@olimar.ru','info@carpets-shop.ru ','info@color-style.ru','info@euroansa.ru','info@fabiansmith.ru','info@faceandtable.com','info@grandecor.ru','info@homerefit.ru','info@komandor-vostok.ru','info@latorre-spa.ru','info@maninimobili.ru','info@manngroup.ru','info@marya.ru','INFO@MEKRAN.COM','info@miassmobili.com','info@mrdoors.ru','info@naturtex.es','info@port-land.ru','info@ros-door.ru','info@russimex.ru','info@tylo.ru','info@vsestulya.ru','info@zerkala777.ru','ita-grand@yandex.ru','klose-mebel@mail.ru','kv_@mail.ru','lilifi77@mail.ru','cipollino@som-nsk.ru  ','loddengrand@mail.ru','mail@artis21.ru','mfr@gorizontmebel.ru','moy_sad@bk.ru','office@moysad.ru','msk@smart-company.ru','msk@ttpro.ru','nskmebel@mail.ru','nskmebel@mail.ru','office@lazurit.info','office@prestig-doors.spb.ru','oldi2001@mail.ru','penopol@mail.ru','sale@gkmf.ru','sales@forema.ru','sales@kpm-rzn.ru','sales@mebelium.ru','sales@viromax.ru','sandal83@bk.ru','sbyt@potrio.ru','sontime@bk.ru','te_grand@mebel-moskva.ru','termeb@inbox.ru','toporovain@ulmebel.com','ulyanovskiedveri@gmail.com','v.vermax@yandex.ru','vechimark@mail.ru','viktokuleshov@yandex.ru','volodin@kover.ru','Web-m@ster','yantar@yantarmebel.ru','yucon@forteks.ru','ukonmebel@mail.ru','yurkeeva@bk.ru','2300487@ngs.ru','zakaz@zaodera.ru','komdir@zaodera.ru'
		);


		$message = <<< MSG

			<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
			<p style="font-size:24px;font-family:arial;margin:0;">Добрый день!</p>
			<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
			<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Для обеспечения высоких интернет-продаж совершенно необязательно тратить много времени и средств на продвижение собственного сайта.</p>
			<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
			<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">
				<a style="color:#dd3724" href="http://myhome.ru/">MyHome</a> — интернет-площадка
				для продвижения производителей <a style="color:#dd3724" href="http://myhome.ru/catalog/">товаров для дома</a>.
				Ежемесячная посещаемость портала — более 700 000 человек (из них 90% — пользователи,
				которые делают или в ближайшее время начнут делать ремонт).
			</p>
			<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
			<p style="font-size:14px;font-family:arial;margin:0;line-height:30px;">
				<b>Открыть онлайн-представительство вашей компании на MyHome просто:</b><br>
				1.  Выберите подходящий <a style="color:#dd3724" href="http://www.myhome.ru/advertising/rates">вариант размещения</a><br>
				2.  Заполните <a style="color:#dd3724" href="http://www.myhome.ru/advertising/rates/?request=true">заявку на размещение</a><br>
				3.  Менеджер свяжется с вами в течение 24 часов<br>
			</p>
			<div style="height:40px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=40 alt=""></div>
			<p style="font-size:14px;font-family:arial;margin:0;line-height:22line-height:30px;px;">
				Возникли вопросы?<br>
				Обратитесь по телефону 8 800 700 1511,<br>
				<a style="color:#2d2d2d" href="mailto:sales@myhome.ru">электронной почте</a> или через
				<a style="color:#2d2d2d" href="http://www.myhome.ru/advertising/rates/?feedback=true">форму обратной связи</a>.
			</p>
			<div style="height:40px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=40 alt=""></div>
			<p style="font-size:14px;font-family:arial;margin:0; line-height:22px;">
				С уважением к вам и вашему бизнесу,<br>
				<a style="color:#2d2d2d" href="http://myhome.ru/">MyHome.ru</a>
			</p>
			<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=30 alt=""></div>
MSG;

		foreach ($emails as $email) {

			/* ------------------------------------
			 *  Отправка письма пользователю
			 * ------------------------------------
			 */
			Yii::app()->mail->create()
				->from(array('email' => 'noreply@myhome.ru'))
				->to($email)
				->subject('Разместите свои товары на лучшем портале России!')
				->message($message)
				->notifier(true)
				->send();
		}
	}

	/**
	 * Метод считает сколько больших фотографий среди всех фотографий
	 * интерьеров
	 */
	public function actionCountBigPhotos()
	{
		$sql = 'SELECT uf.* FROM idea_uploaded_file iuf
				INNER JOIN uploaded_file uf
					ON uf.id = iuf.uploaded_file_id
			WHERE iuf.idea_type_id = 1 OR iuf.idea_type_id = 3';

		$photos = Yii::app()->db->createCommand($sql)->queryAll();

		/*
		 * Встроенная функция для вывода статистики обработки
		 */
		$showProgress = function($totalQt, $currentQt, $bigQt, $noFiles){

			if ($totalQt == 0) {
				$allPercent = 0;
			} else {
				$allPercent = round($currentQt * 100 / $totalQt);
			}

			if ($currentQt == 0) {
				$bigPercent = 0;
			} else {
				$bigPercent = round($bigQt * 100 / $currentQt);
			}


			echo "\r ===> Обработано ".$allPercent.'% файлов, больших фотографий '.$bigPercent.'%. Потеряно: '.$noFiles.'шт.';
		};

		if ($photos) {
			$totalQt = $bigQt = $currentQt = $noFiles = 0;

			$totalQt = count($photos);

			echo "Подсчет количества фотографий:\n";

			$showProgress($totalQt, $currentQt, $bigQt, $noFiles);

			foreach ($photos as $photo) {
				$filePath = __DIR__.'/../../uploads/protected/' . $photo['path'] . '/' . $photo['name'] . '.' . $photo['ext'];

				if (file_exists($filePath)) {
					list($width, $height) = getimagesize($filePath);

					if ($width > 1000 || $height > 1000) {
						$bigQt++;
					}
				} else {
					$noFiles++;
				}

				$currentQt++;

				$showProgress($totalQt, $currentQt, $bigQt, $noFiles);
			}

			echo "\n";
		}
	}

	public function actionRecountRating()
	{
		$specs = User::model()->findAllByAttributes(array('status'=>User::STATUS_ACTIVE, 'role'=>array(User::ROLE_SPEC_FIS, 'role'=>User::ROLE_SPEC_JUR)));

		foreach($specs as $spec)
		{
			Yii::app()->gearman->appendJob('userService', array('userId'=>$spec->id, 'serviceId'=>75));
		}
	}

	public function actionSendMessage()
	{

		$message = <<< MSG
			Добрый день!
			Мы открыли новую рубрику "Декорирование интерьера".
			Если вы оказываете эту услугу, смело добавляйте её в
			список своих услуг и выкладывайте новые портфолио!Хорошего вам дня и отличного настроения.
			Это письмо сформировано автоматически. Пожалуйста, не отвечайте на него.

MSG;

		// Елена Верещагина
		$manager = User::model()->findByPk(22332);

		if (!$manager) {
			throw new CException(400, 'Менеджер не найден');
		}

		$sql = 'SELECT DISTINCT id FROM user '
			. ' INNER JOIN user_service as us ON us.user_id = user.id'
			. ' WHERE status = :st AND role IN (:fis, :jur)';


		$status = User::STATUS_ACTIVE;
		$roleFis = User::ROLE_SPEC_FIS;
		$roleJur = User::ROLE_SPEC_JUR;
		$specList = Yii::app()->db
			->createCommand($sql)
			->bindParam(':st', $status)
			->bindParam(':fis', $roleFis)
			->bindParam(':jur', $roleJur)
			->queryColumn();

		$success = 0;
		$error = 0;

		foreach ($specList as $spec) {
			if (MsgBody::newMessage($spec, $message, $manager->id)) {
				$success++;
				echo $spec;
				echo "\n";
			} else {
				$error++;
			}
		}

		echo 'SUCCESS: '.$success.' ERROR: '.$error."\n";

	}


	/**
	 * Собирает и отправляет инвайты зарегестрированным пользователям, но
	 * не активировавшим себя.
	 */
	public function actionSendInviteIpad4()
	{
		// Определяем функцию для оптарвки писем и сохранения пароля.
		$sendInviteForUsers = function($users, $totalUsersQt) {
			$index = 0;
			foreach ($users as $id) {

				echo ++$index . ' / ' . $totalUsersQt . "\r";

				if ($index % 1000 == 0) {
					echo "\n";
				}


				try {
					// Ищем пользователя в БД
					$user = User::model()->findByPk($id);

					if (!$user) {
						echo "\nПользователь #" . $id . " не найден\n";
					}

					$user->password = Amputate::generatePassword();
					$unsafe_pass = $user->password;
					$user->password = md5($user->password);
					$user->password2 = md5($user->password2);

					$user->activateKey = User::generateActivateKey();
					$user->status = User::STATUS_VERIFYING;

					$user->save();

					// Сохраняем новый пароль в редис
					Yii::app()->redis->set($user->id . '_pass', serialize($unsafe_pass));


					/* ------------------------------------
					 *  Отправка письма пользователю
					 * ------------------------------------
					 */
					Yii::app()->mail
						->create('inviteSpecialistIpad4')
						->from(array('email' => 'noreply@myhome.ru'))
						->to($user->email)
						->params(array(
							'user_name'     => $user->name,
							'activate_link' => Yii::app()->homeUrl . '/site/activation/key/' . $user->activateKey,
						))
						->useView(false)
						->send();

				} catch (Exception $e) {
					echo 'Ошибка на пользователе #' . $id ."\n";
				}
			}

			echo "Завершено\n";
		};

		/* Получаем пользователей:
		 * - физ и юр лица
		 * - на подтверждении
		 */

		$sql = '
			SELECT id
			FROM user
			WHERE
				user.`status` = 7
				AND
				(user.role = 3 OR user.role = 4)
			LIMIT 1400, 25000
		';

		// Массив идентификаторов пользователей
		$users = Yii::app()->db
			->createCommand($sql)
			->queryColumn();

		$totalUsersQt = count($users);

		echo "Общее количество пользователей: " . $totalUsersQt . "\n";

		$sendInviteForUsers($users, $totalUsersQt);
	}

	public function actionSpecMailer()
	{
		$criteria = new CDbCriteria();
		$criteria->condition = 't.role in (3,4) and status=2';

		$specs = User::model()->findAll($criteria);

		foreach($specs as $spec) {
			MsgBody::newMessage($spec->id, 'Добрый день!
Спешу сообщить, что осталось 48 часов до окончания акции «iPad4 за спасибо»!
Ещё 48 часов для того, чтобы привлечь своих клиентов к оставлению отзывов о вас: подарить им возможность выиграть ценный приз, и себя приблизить к победе!
Не упускайте свой шанс:)
(Подробности акции по ссылке http://www.myhome.ru/ipad_za_spasibo)', 38913);
		}

	}
}