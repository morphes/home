<?php

/**
 * @brief Обеспечивает потоковую отдачу файла и контроль доступа к файлам
 * @details Производит проверку возможности получения пользователем файла и отдает файл в поток
 * @see EDownloadHelper
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 */
class DownloadController extends Controller
{

        public function filters()
        {
                return array('accessControl');
        }

        /**
         * @brief Разрешает доступ всем пользователям
         * @return array
         */
        public function accessRules()
        {

                return array(
			array('allow',
				'actions' => array('attachfile', 'emailstub', 'pdfdeposition', 'pricelist', 'regfile', 'catalogCsv', 'tenderfile', 'CatCsv'),
				'users' => array('*'),
			),
			array(
				'allow',
				'actions' => array('reportfile'),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
				),
			), array(
				'allow',
				'actions' => array('feedback', 'productImgOriginal'),
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_IDEA,
					User::ROLE_FREELANCE_STORE,
					User::ROLE_SALEMANAGER,
					User::ROLE_JOURNALIST,
				),
			),
		    
			array('deny',
				'users'=>array('*'),
			),
                );
        }
        
        /**
         * @brief Скачивание аттачей в личных сообщениях
         * @param integer $id
         * @return stream 
         */
        public function actionAttachfile($id = NULL)
        {
                Yii::import('application.modules.member.models.*');
                $file = UploadedFile::model()->findByPk((int) $id);
                if($file && (($file->msgBodys[0]->author_id == Yii::app()->user->id) || ($file->msgBodys[0]->recipient_id == Yii::app()->user->id))){
                        $fname = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . $file->path . DIRECTORY_SEPARATOR . $file->name . '.' . $file->ext;
                        if(file_exists($fname)) 
                                return EDownloadHelper::download($fname, 1000, false);
                } 
                        throw new CHttpException(404);
        }
        
        /**
         * @brief Скачивание аттачей регистрационных файлов
         * @param integer $id
         * @return stream 
         */
        public function actionRegfile($id = NULL)
        {
                $file = UploadedFile::model()->findByPk((int) $id);
                if($file && Yii::app()->user->getRole() == User::ROLE_ADMIN){
                        $fname = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . $file->path . DIRECTORY_SEPARATOR . $file->name . '.' . $file->ext;
                        if(file_exists($fname)) 
                                return EDownloadHelper::download($fname, 1000, false);
                } 
                        throw new CHttpException(404);
        }

	/**
	 * Скачивание сформированных PDF-свидетельств в профиле пользователя.
	 * Можно скачивать только принадлежащие автору свидетельства.
	 * @param interger $id ID свидетельства
	 * @return file
	 * @throws CHttpException 
	 */
	public function actionPdfdeposition($id = NULL)
	{
		Yii::import('application.modules.idea.models.Copyrightfile');
		
		$file = Copyrightfile::model()->findByPk((int)$id);
		
		if ($file && $file->author_id == Yii::app()->user->model->id)
		{
			$fname = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . $file->path . DIRECTORY_SEPARATOR . $file->name;
			if (file_exists($fname)) 
				return EDownloadHelper::download($fname, 1000, false);
		}
		throw new CHttpException(404);
	}

        /**
         * Отдача прайс-листа
         */
	public function actionPricelist($id = NULL)
	{
		$file = UploadedFile::model()->findByPk($id);

                if(!$file)
                        throw new CHttpException(404);

                $ud = UserData::model()->findByPk($file->author_id);

                if($ud && $ud->price_list != $file->id)
                        throw new CHttpException(403);

                return EDownloadHelper::download($file->path.'/'.$file->name.'.'.$file->ext);
	}

        /**
         * Установка статуса "прочтено" письму
         * Отдача прозрачной png 1x1 px
         * @param $mid id in mail table
         */
        public function actionEmailstub($mid = null)
        {
                $mail = Yii::app()->db->createCommand()
                        ->select('id, status')
                        ->from('mail_log')
                        ->where('mailhash=:mh', array(':mh'=>CHtml::encode($mid)))
                        ->queryRow();

                if(empty($mail) || $mail['status'] == EmailComponent::STATUS_OPENED)
                        return EDownloadHelper::download(Config::$emailStubPic, 1000, true);

                Yii::app()->db->createCommand()
                        ->update('mail_log', array('status'=>EmailComponent::STATUS_OPENED),'mailhash=:mh', array(':mh'=>CHtml::encode($mid)));

                return EDownloadHelper::download(Config::$emailStubPic, 1000, true);
        }
	
	public function actionTenderfile($id = null)
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN `tender_file` ON `tender_file`.`file_id`=t.id';
		$criteria->condition = 't.id=:id';
		$criteria->params = array(':id'=>$id);
		
		$file = UploadedFile::model()->find($criteria);
		if ($file) {
			$fname = Yii::getPathOfAlias('webroot') . '/'. UploadedFile::UPLOAD_PATH . '/' . $file->path . '/' . $file->name . '.' . $file->ext;
			return EDownloadHelper::download($fname);
		} else {
			throw new CHttpException(404);
		}
	}

	/**
	 * Загрузка сгенерированных отчетов
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionReportfile($id = null)
	{
		if (is_null($id))
			throw new CHttpException(404);

		$id = intval($id);
		Yii::import('admin.models.Report');
		/** @var $report Report */
		$report = Report::model()->findByPk($id);

		if ($report && $report->status==Report::STATUS_SUCCESS) {
			$fname = Yii::getPathOfAlias('webroot') . '/' . $report->file;
			return EDownloadHelper::download($fname);
		} else {
			throw new CHttpException(404);
		}

		/*$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN `report` ON report.file_id = t.id';
		$criteria->condition = 't.id = :id';
		$criteria->params = array(':id'=>$id);

		$file = UploadedFile::model()->find($criteria);
		if ($file) {
			$fname = Yii::getPathOfAlias('webroot') . '/' . $file->path . '/' . $file->name . '.' . $file->ext;
			return EDownloadHelper::download($fname);
		} else {
			throw new CHttpException(404);
		}*/
	}

	public function actionFeedback($id = null)
	{
		if (is_null($id))
			throw new CHttpException(404);
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN `feedback_file` as ff ON ff.file_id = t.id';
		$criteria->condition = 't.id = :id';
		$criteria->params = array(':id'=>$id);

		$file = UploadedFile::model()->find($criteria);
		if ($file) {
			$fname = Yii::getPathOfAlias('webroot') . '/'. UploadedFile::UPLOAD_PATH . '/' . $file->path . '/' . $file->name . '.' . $file->ext;
			return EDownloadHelper::download($fname);
		} else {
			throw new CHttpException(404);
		}
	}


	public function actionCatalogCsv($id = NULL)
	{
		Yii::import('application.modules.catalog.models.CatExportCsv');

		$model = CatExportCsv::model()->findByPk((int)$id);

		if ($model)
		{
			$fname = Yii::getPathOfAlias('webroot') . $model->download_file;
			if (file_exists($fname))
				return EDownloadHelper::download($fname, 1000, false);
		}
		throw new CHttpException(404);
	}

	/**
	 * Скачивалка сгенерированных csv файлов с товарами каталога.
	 *
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionCatCsv($id = null)
	{
		Yii::import('application.modules.catalog.models.CatCsv');

		$model = CatCsv::model()->findByPk((int)$id);

		if ($model)
		{
			$fname = Yii::getPathOfAlias('webroot') . $model->file;
			if (file_exists($fname))
				return EDownloadHelper::download($fname, 1000, false);
		}
		throw new CHttpException(404);
	}

	/**
	 * Метод для получения оригинальных изображений Каталога Товаров
	 *
	 * @param $file_id
	 */
	public function actionProductImgOriginal($file_id)
	{
		$uFile = UploadedFile::model()->findByPk($file_id);

		if ( ! $uFile)
			throw new CHttpException(404);

		$filePath = Yii::getPathOfAlias('webroot').'/'.$uFile->getFullname();

		/* // X-Accel-Redirect не заработал на Боевом серваке, пока по старинке выводим файл
		$options = array(
			'saveName' => 'image_for_crop.jpg',
			'mimeType' => 'image/jpeg',
			'terminate' => true
		);

		$xHeader = (Yii::app()->params->serverType == 'apache') ? 'X-Sendfile' : 'X-Accel-Redirect';
		$options['xHeader'] = $xHeader;

		Yii::app()->request->xSendFile($filePath, $options );*/

		return EDownloadHelper::download($filePath, 10000, false);
	}
}