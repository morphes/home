<?php

/**
 * @brief контроллер для тестов
 */
class TestController extends Controller
{

        public $layout = 'webroot.themes.myhome.views.layouts.simple';

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    	array('allow',
                        	'roles' => array(
					User::ROLE_POWERADMIN
				),
                    	),
                    	array('deny',
	                        'users' => array('*'),
                    	),
                );
        }

        public function beforeAction($action)
        {
                Yii::import('application.modules.content.models.*');
                return true;
        }

        public function actionUploader()
        {

                if (isset($_FILES['uploaded-file'])) {

                        move_uploaded_file($_FILES['uploaded-file']['tmp_name'], '/var/www/myhome/uploads/' . $_FILES['uploaded-file']['name']);
                        $result = CJSON::encode(array('result' => '/uploads/' . $_FILES['uploaded-file']['name']));
                        die($result);
                }

                $this->render('uploader');
        }

	public function actionSphinxql()
	{
		$sphinx = Yii::app()->sphinx;

		$sphinxQl = 'SELECT * FROM {{tender}} WHERE services=2 LIMIT 10';
		$result = $sphinx->createCommand($sphinxQl)->queryAll();
		FirePHP::getInstance()->fb($result);
		die();
	}

	public function actionInfo()
	{
		phpinfo();
		die();
	}

	public function actionGearman()
	{
		//Yii::app()->gearman->appendJob("sphinx:user_login", 1);
		//Yii::app()->gearman->appendJob("sphinx:interior_content", array('action'=>'update', 'interior_id'=>8705));

		for ($i=300; $i<305; $i++) {
			//Yii::app()->gearman->appendJob('sphinx:interior_content', array('interior_id'=>  $i, 'action'=>'update'));
			//Yii::app()->gearman->appendJob('sphinx:architecture', $i);
			//Yii::app()->gearman->appendJob('sphinx:interiorpublic', $i);
//			Yii::app()->gearman->appendJob('sphinx:tender', $i);
			Yii::app()->gearman->appendJob('sphinx:product', array('product_id' => $i, 'action' => 'update'));
		}
		die();
	}

	public function actionPreview()
	{
		Yii::import('application.modules.catalog.models.*');
		/** @var $img UploadedFile */
		$img = UploadedFile::model()->findByPk(277794);
		$img->generatePreview(Product::$preview['crop_338']);
		$url = $img->getPreviewName(Product::$preview['crop_338']);
		echo CHtml::image('/'.$url);

		print_r($url);
		die();
	}

	public function actionFileapi()
	{
		$this->layout = false;


		if (Yii::app()->request->isPostRequest){
			FirePHP::getInstance()->fb($_POST);
			FirePHP::getInstance()->fb($_FILES);
			die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
		}

		$this->render('fileapi');

	}

	public function actionSphinx($term)
	{
		Yii::import('application.modules.idea.models.*');

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);
		$provider = new CSphinxDataProvider($sphinxClient, array('index' => 'idea',
									     'modelClass' => Idea::model(),
									     'query' => $term . '*',
									     'matchMode' => 'SPH_MATCH_ANY',
										'filters'=>array('idea_type'=>Config::ARCHITECTURE),
									     'pagination' => array('pageSize' => 10),
		));

		$data = $provider->getData();
		foreach ($data as $model) {
			FirePHP::getInstance()->fb($model);
		}
		die();

	}

	public function actionRedis($q='')
	{
		$redis = Yii::app()->redis;

		$keys = $redis->keys($q.'*');
		CVarDumper::dump($keys, 10, true);
		die();

	}

	public function actionRedisVal($key='')
	{
		if (empty($key))
			die();
		$val = Yii::app()->predis->get($key);
		CVarDumper::dump($val, 10, true);
		 die();
	}

	public function actionTest()
	{
//		$url = Yii::app()->img->getPreview(67, 'crop_200');
//		echo CHtml::image($url);
//
//		$dav = new WebDavFile();
//		$dav->setOptions(array('port'=>8080, 'userpwd'=>'admin:12345'));
//		$tmp = $dav->getFile('uploads/1.jpg');
//
////		$tmp = $dav->getFile('/dav/000/000/000/091.jpg');
//
//
//		FirePHP::getInstance()->fb($tmp);

		$img = Yii::app()->img->getOrigin(50);
		FirePHP::getInstance()->fb($img);
		$file = $img->getFile();
		FirePHP::getInstance()->fb($file);

		die();
	}

	public function actionWebdav()
	{
		$dav = new WebDavFile();
		$dav->setOptions(array(
			'host'=>'img.myhome.local',
			'port'=>8080,
			'userpwd'=>'admin:12345'
		));
		$result = $dav->putFile('/var/www/myhome/images/default.svg', 'dav/test.svg');
		var_dump($result);
		echo "\n<br/>\n";
		CVarDumper::dump($result);
	}

	public function actionImage()
	{
		$dir = '/home/alexsh/fuck!';
		if ($handle = opendir($dir)) {
			/* Именно этот способ чтения элементов каталога является правильным. */
			while (false !== ($file = readdir($handle))) {
				$fileName = $dir.'/'.$file;
				if (!is_file($fileName))
					continue;
				$id = Yii::app()->img->putImage($fileName, $file, 1);
				if (!$id) {
					echo "fail upload {$file}\n<br/>";
					continue;
				}

				$src = Yii::app()->img->getPreview($id, 'crop_200');


				echo CHtml::image($src);

//				echo "$file\n";
			}

			closedir($handle);
		}
	}

	public function actionXSend()
	{
		//We want to force a download box with the filename hello.txt
		//header('Content-Disposition: attachment;filename=hello.jpg');
		header('Content-type: image/jpg');

		//File is located at /home/username/hello.txt
		header('X-Sendfile: /var/www/myhome/uploads/protected/catalog/1/10/product/15774/product15774_44_1356497179.jpg');

		Yii::app()->end();
	}

	public function actionCharCode()
	{
		$eng = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
			'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W',
			'V', 'X', 'Y', 'Z'
		);
		$rus = array(
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
			'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
			'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
		);
		$num = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$sym = array('!', '@', '#', '$', '%', '^', '&', '*', '(', ')');

		$echoCodes = function($symbols){
			// длина строки
			$dlina = count ($symbols);
			// в цикле преобразуем каждый исмвол в ASCII код
			$i=0;
			while ($i < $dlina)
			{
				echo crc32 ($symbols[$i]) . ' <= ' . $symbols[$i] . '<br/>';
				$i++;
			}
		};

		echo '<h3>Английский алфафит</h3>';
		$echoCodes($eng);

		echo '<h3>Русский алфавит</h3>';
		$echoCodes($rus);

		echo '<h3>Цифры</h3>';
		$echoCodes($num);

		echo '<h3>Символы</h3>';
		$echoCodes($sym);

		echo '</body></html>';
	}

	public function actionUdpTest()
	{
		/*$errno = $errstr = '';
		$socket = stream_socket_server("udp://192.168.10.126:514", $errno, $errstr, STREAM_SERVER_BIND);
		if (!$socket) {
			die("$errstr ($errno)");
		}

		do {
			$pkt = stream_socket_recvfrom($socket, 1, 0, $peer);
			echo "$peer\n";
			stream_socket_sendto($socket, date("D M j H:i:s Y\r\n"), 0, $peer);
		} while ($pkt !== false);*/

		/*$sock = stream_socket_server("udp://192.168.10.126:514");
		$name = stream_socket_get_name($sock, true);
		echo $name;*/

		/* ICMP ping packet with a pre-calculated checksum */
		$host = '192.168.10.126';
		$msg =  LOG_INFO . ' message blin';
		$timeout = 1;

		$socket  = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));

		//Set up to send to port 8000, change to any port
		socket_connect($socket, $host, 514);

		socket_send($socket, $msg, strLen($msg), 0);

		socket_close($socket);
	}

	public function actionExcel()
	{
		Yii::import('ext.PHPExcel.PHPExcel');

		// Create new PHPExcel object
		echo date('H:i:s') , " Create new PHPExcel object" , '<br>';
		$objPHPExcel = new PHPExcel();

		// Set document properties
		echo date('H:i:s') , " Set document properties" , '<br>';
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
			->setLastModifiedBy("Maarten Balliauw")
			->setTitle("PHPExcel Test Document")
			->setSubject("PHPExcel Test Document")
			->setDescription("Test document for PHPExcel, generated using PHP classes.")
			->setKeywords("office PHPExcel php")
			->setCategory("Test result file");


		// Add some data
		echo date('H:i:s') , " Add some data" , '<br>';
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', 'Hello')
			->setCellValue('B2', 'world!')
			->setCellValue('C1', 'Hello')
			->setCellValue('D2', 'world!');

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

		$styleA1 = $objPHPExcel->getActiveSheet()->getStyle('A1');
		$styleA1->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_YELLOW ) );
		$styleA1->getFont()->setBold(true);
		$styleA1->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$styleA1->getFill()->getStartColor()->setARGB('FF808080');
		$styleA1->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		// Miscellaneous glyphs, UTF-8
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A4', 'Miscellaneous glyphs')
			->setCellValue('A5', 'Привет турист');


		$objPHPExcel->getActiveSheet()->setCellValue('A8',"Hello\nWorld");
		$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(-1);
		$objPHPExcel->getActiveSheet()->getStyle('A8')->getAlignment()->setWrapText(true);


		// Rename worksheet
		echo date('H:i:s') , " Rename worksheet" , '<br>';
		$objPHPExcel->getActiveSheet()->setTitle('Simple');


		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);


		// Save Excel 2007 file
		echo date('H:i:s') , " Write to Excel2007 format" , '<br>';
		$callStartTime = microtime(true);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		$callEndTime = microtime(true);
		$callTime = $callEndTime - $callStartTime;

		echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , '<br>';
		echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , '<br>';
		// Echo memory usage
		echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , '<br>';


		// Save Excel 95 file
		echo date('H:i:s') , " Write to Excel5 format" , '<br>';
		$callStartTime = microtime(true);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save(str_replace('.php', '.xls', __FILE__));
		$callEndTime = microtime(true);
		$callTime = $callEndTime - $callStartTime;

		echo date('H:i:s') , " File written to " , str_replace('.php', '.xls', pathinfo(__FILE__, PATHINFO_BASENAME)) , '<br>';
		echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , '<br>';
		// Echo memory usage
		echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , '<br>';


		// Echo memory peak usage
		echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , '<br>';

		// Echo done
		echo date('H:i:s') , " Done writing files" , '<br>';
		echo 'Files have been created in ' , getcwd() , '<br>';
	}
}