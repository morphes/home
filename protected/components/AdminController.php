<?php

/**
 * @brief Базовые правила безопасности и layout для Панели администрирования
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 */
class AdminController extends Controller
{
        /**
         * @brief Указывает на layout для панели администрирования
         * @var string
         */
	public $layout = 'application.modules.admin.views.layouts.admin';
        public $rightbar = '';
        
	public function filters() {
		return array('accessControl');
	}

        /**
         * @return array
         */
	public function accessRules() {

		return array(
			array('allow',
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SEO,
					User::ROLE_JOURNALIST,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_FREELANCE_STORE,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_IDEA,
				),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}
}
