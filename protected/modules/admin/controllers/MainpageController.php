<?php

/**
 * @brief Настройки главной страницы сайта
 * @details Обеспечивает модерирование блоков гл. страницы сайта
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 */
class MainpageController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';
	
        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('index', 'switchstatus'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN,
				User::ROLE_JOURNALIST,
				User::ROLE_SENIORMODERATOR,
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
        
	public function beforeAction($action)
        {
                Yii::import('application.modules.idea.models.*');
                return true;
        }

        public function actionIndex()
        {   
                $units = Unit::getUnitsForClass(Unit::CLASS_HOMEPAGE);

                $this->render('index', array('units'=>$units));
        }      
        
        public function actionSwitchstatus($unit = null)
        {
                if(Yii::app()->request->isAjaxRequest && $unit){
                        Unit::switchUnitStatus($unit);   
                }
        }

}