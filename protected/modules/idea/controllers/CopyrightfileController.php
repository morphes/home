<?php

/**
 * @brief Обработка личного кабинета
 * @author Sergey Seregin <sss@medvediza.ru>
 */
class CopyrightfileController extends FrontController
{

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(                    
                    array('allow',
                        'actions' => array('index'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_GUEST,
				User::ROLE_JUNIORMODERATOR,
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SALEMANAGER,
				User::ROLE_SPEC_FIS,
				User::ROLE_SPEC_JUR,
				User::ROLE_USER
			),
                    ),
                    
                    array('deny'),
                );
        }

        public function beforeAction($action)
        {

                Yii::import('application.modules.idea.models.*');

		
		$this->menuIsActiveLink = true;
		
                return true;
        }

	/**
	 * Генерирует pdf-свидетельство для интерьера с ID, хранящемся в $intid
	 * 
	 * @param integer $intid ID интерьера
	 * @param string $type Тип пользователя для которого генерится pdf (Автор, Правообладатель)
	 * @throws CHttpException 
	 */
        public function actionIndex($intid = NULL, $type = 'copy')
        {
		/** @var $interior Interior */
		$interior = Interior::model()->findByPk((int)$intid);
		if ( ! $interior || $interior->author_id != Yii::app()->user->model->id)
			throw new CHttpException(404);
		
		// Дата формирования pdf-свидетельства
		$dateCreatePdf = time();
		
		$mPDF1 = Yii::app()->ePdf->mPDF('', 'A4');

		
		$stylesheet = file_get_contents(Yii::getPathOfAlias('webroot.css') . '/pdf.css');
		$mPDF1->WriteHTML($stylesheet, 1);

		// Верхний колонититул
		$mPDF1->SetHTMLHeader('
			<div id="header">
				<table width="100%">
					<tr>
						<td class="adress">Новосибирск, 630047, ул. Светлановская, 50, (383) 230-44-30, info@myhome.ru </td>
						<td id="logo" align="right"><img src="img/tmp/logo-pdf.jpg" /></td>
					</tr>
				</table>
			</div>
		'); 
		// Нижный колонтитул
		$mPDF1->SetHTMLFooter('
			<div id="footer">
				<div class="clear"></div>
				
				<table width="100%">
					<tr>
						<td id="f_left"><p>Генеральный директор ООО «МайХоум»</p></td>
						<td id="f_right" align="right">
							<p>______________________________ Мамыкин М.С</p>
						</td>
					</tr>
				</table>
				<div class="page">Страница {PAGENO}</div>
			</div>
		');
		
		// Получаем следующий номер для свидетельства
		$res = Yii::app()->db->createCommand("SELECT id FROM ".Copyrightfile::model()->tableName()." ORDER BY id DESC LIMIT 1")->queryRow();
		if ($res) {
			$nextNumber = intval($res['id']) + 1;
		} else {
			$nextNumber = 1;
		}
		
		
		// *** выводим ПЕРВУЮ СТРАНИЦУ ***
		
		$mPDF1->WriteHTML($this->renderPartial('//idea/copyright/_firstPage', array(
			'user'		=> Yii::app()->user->model,
			'interior'	=> $interior,
			'nextNumber'	=> $nextNumber,
			'date'		=> date('d/m/Y в H:i:s', $dateCreatePdf),
			'type'		=> $type,
		), true));
		
		
		
		// *** выводим ФОТОЧКИ ***
		$photos = $interior->getPhotos();
		
		if ( ! empty($photos)) {
			$allCntImg = count($photos);
			$i = 0;
			foreach ($photos as $item) {
				$i++;
				$mPDF1->addPage();
				$mPDF1->WriteHTML($this->renderPartial('//idea/copyright/_imgPage', array(
					'photo' => $item,
					'curCntImg' => $i+1,
					'allCntImg' => $allCntImg,
					'projName'  => $interior->name
				), true));
			}
		}

		
		// *** СОХРАНЯЕМ ***

		$pathSave = 'uploads/protected/copyright_file/'.(Yii::app()->user->model->id % 10000);
		if ( ! file_exists($pathSave)) {
			mkdir($pathSave, 0700, true);
		}
		
		$fileName = 'myhome.ru_'.$interior->id.'('.date('d.m.Y_His', $dateCreatePdf).').pdf';
		
		
		// Сохраняем документ
		$mPDF1->Output($pathSave.'/'.$fileName);
		
		
		$copyright = new Copyrightfile();
		$copyright->setAttributes(array(
			'number' => $nextNumber,
			'name' => $fileName,
			'path' => $pathSave,
			'author_id' => Yii::app()->user->model->id,
			'interior_id' => $interior->id
		));
		
		
		$success = false;
		$history_html = '';
		$copyright_id = 0;
		
		if ($copyright->save()) {
			$success = true;
			$history_html = self::getHistoryHtml($interior->id);
			$copyright_id = $copyright->id;
		}
		
		die(CJSON::encode(array(
			'success'	=> $success,
			'history_html'	=> $history_html,
			'copyright_id'	=> $copyright_id,
		)));
        }
	
	
	
	public static function getHistoryHtml($intid = NULL)
	{
		$historyDeposition = Copyrightfile::model()->getHistory((int)$intid);
		
		$html = '';
	
		if ( ! empty($historyDeposition)) {
			$html .= CHtml::link('История документов<i></i>', '/', array('class' => 'handler'));
			$html .= CHtml::openTag('div', array('class' => 'docs_list'));
			$html .= CHtml::openTag('ul');
		
			foreach ($historyDeposition as $item)
			{
				$html .= CHtml::openTag('li');
				$html .= '- ';
				$html .= CHtml::link( Yii::app()->getDateFormatter()->format('d MMMM yyyy', $item->create_time), array('/download/pdfdeposition', 'id' => $item->id));
				$html .= CHtml::closeTag('li');
			}
		
			$html .= CHtml::closeTag('ul');
			$html .= CHtml::tag('p', array(), 'В истории хранятся три последние версии документа');
			$html .= Chtml::closeTag('div');
		}
		
		return $html;
	}
	
}