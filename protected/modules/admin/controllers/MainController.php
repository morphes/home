<?php

/**
 * @brief Стартовый контроллер панели администрирования
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 */
class MainController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';
        
        public function actionIndex()
        {
                $this->render('index');
        }

}