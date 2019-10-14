<?php

class ImageCommand extends CConsoleCommand
{
	public function actionWorker()
	{
		Yii::import('application.components.imageHandler.imageHandler');

		$worker = Yii::app()->gearman->worker();
		$worker->addFunction('preview_generator', array($this, 'generatePreview'));

		while($worker->work() ){
			if (GEARMAN_SUCCESS != $worker->returnCode()) {
				echo "Worker failed: " . $worker->error() . "\n";
			}
			echo "\n";
		}
	}

	public function generatePreview(GearmanJob $job)
	{
		echo date('[Y-m-d H:i:s] ');
		$data = $job->workload();
		try {
			$fullData = unserialize($data);
			$imgInfo = $fullData['imgInfo'];
			$config = $fullData['config'];
			$forceGenerate = !empty($fullData['forceGenerate']); // Использовать для принудительной генерации

			$origin = Yii::app()->basePath.'/../'.UploadedFile::UPLOAD_PATH.'/'.$imgInfo['path'].'/'.$imgInfo['name'].'.'.$imgInfo['ext'];
			if (!file_exists($origin)) {
				echo 'Origin image does not exists DATA:'.$data;
				return;
			}

			foreach ($config as $configItem) {

				$name = $configItem[0].'x'.$configItem[1].$configItem[2].'_'.$imgInfo['name'];
				$previewName = Yii::app()->basePath.'/../'.UploadedFile::PUBLIC_PREFIX .'/'. $imgInfo['path'] . '/' . $name . '.jpg';

				if (!$forceGenerate && file_exists($previewName)) {
					continue;
				}
				$folder = Yii::app()->basePath.'/../'.UploadedFile::PUBLIC_PREFIX . '/' . $imgInfo['path'];

				if (!file_exists($folder)) {
					mkdir($folder, 0755, true);
				}

				// image processing
				$imageHandler = new imageHandler($origin, imageHandler::FORMAT_JPEG);
                                $imageHandler->updateColorspace();

				$bestFit = isset($configItem[4]) ? $configItem[4] : true;
                                $border = isset($configItem['border']) ? $configItem['border'] : false;

				if ($configItem[2] == 'crop') {
					$imageHandler->cropImage($configItem[0], $configItem[1], $configItem[3]);
				} else {

					if (isset($configItem['decrease']) && $configItem['decrease'] == true) {
						// Если фотка будет меньше указанного размера, то ресайзиться не будет
						$resizeType = imageHandler::RESIZE_DECREASE;
					} else {
						// Ресайзим
						$resizeType = imageHandler::RESIZE_BOTH;
					}
					$imageHandler->resizeImage($configItem[0], $configItem[1], $configItem[3], $resizeType, $bestFit);

					// Нужен ли водяной знак
					if (isset($configItem['watermark']) && $configItem['watermark'] == true) {
						$imageHandler->watemark(Yii::app()->basePath.'/../img/logo-wm.png');
					}

                                        // добавление прозрачного бордера
                                        if ($border)
                                                $imageHandler->addTransparentBorder($configItem[0], $configItem[1]);
				}

				$imageHandler->saveImage($previewName);
			}

			echo 'Image resize featured ORIGIN: /'.$imgInfo['path'].'/'.$imgInfo['name'].'.'.$imgInfo['ext'];;


		} catch (Exception $e) {
			echo 'Error data: '.$data."\n";
			echo $e->getMessage();
		}
	}
}
