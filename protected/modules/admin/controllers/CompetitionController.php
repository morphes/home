<?php

class CompetitionController extends AdminController
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
                                'roles' => array(
                                        User::ROLE_ADMIN,
                                        User::ROLE_POWERADMIN,
                                ),
                        ),
                        array('deny',
                                'users' => array('*'),
                        ),
                );
        }

        /**
         * Страница со списком прорабов, участвующих в конкурсе
         */
        public function actionJuneForeman($show_winners = false)
        {
                $condition = '';
                if($show_winners)
                        $condition.= ' AND ud.june_foreman_winner = 1';

                $users = Yii::app()->db->createCommand('
                        SELECT u.id, u.login, u.firstname, u.lastname, ud.june_foreman_winner FROM `user` u
                        LEFT JOIN `user_service` us ON us.user_id = u.id
                        LEFT JOIN `user_data` ud ON ud.user_id = u.id
                        LEFT JOIN `portfolio`p ON u.id = p.author_id
                        WHERE u.role IN (3,4) AND u.status = 2 AND u.update_time > 1338656400 AND
                                us.service_id NOT IN(2,3,4,8,10,11,12,13,14,15,16,17,18,19) AND
                                ud.about IS NOT NULL AND ud.about <> "" AND u.image_id IS NOT NULL AND
                                us.project_qt >= 1 AND p.count_photos >= 2 AND p.status = 2
                                ' . $condition . '
                        GROUP BY u.id
                        ORDER BY id ASC;
                ')->queryAll();

                $dataProvider = new CArrayDataProvider($users, array(
                        'pagination'=>array(
                                'pageSize'=>20,
                        )));

                $this->render('juneForeman', array(
                        'dataProvider'=>$dataProvider,
                ));
        }

        /**
         * Действие, отмечающее прораба как победителя в конкурсе
         * @param $uid
         * @param $value
         * @throws CHttpException
         */
        public function actionJuneForemanWinner($uid, $value)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(500);

                $uid = (int) $uid;
                $value = (int) $value;

                Yii::app()->db->createCommand("
                        UPDATE user_data SET june_foreman_winner = '{$value}' WHERE user_id = '{$uid}'
                ")->execute();

                die(CJSON::encode(array('result'=>true)));
        }

        /**
         * Страница со списком архитекторов, участвующих в конкурсе
         */
        public function actionJuneArchitect($show_winners=false)
        {
                $condition = '';
                if($show_winners)
                        $condition.= ' AND ud.june_archit_winner = 1';

                $users = Yii::app()->db->createCommand('
                        SELECT u.id, u.login, u.firstname, u.lastname, ud.june_archit_winner FROM `user` u
                        LEFT JOIN `user_service` us ON us.user_id = u.id
                        LEFT JOIN `user_data` ud ON ud.user_id = u.id
                        LEFT JOIN `architecture`a ON u.id = a.author_id
                        WHERE u.role IN (3,4) AND u.status = 2 AND u.update_time > 1339520400 AND
                                us.service_id = 4 AND
                                ud.about IS NOT NULL AND ud.about <> "" AND u.image_id IS NOT NULL AND
                                us.project_qt >= 3 AND a.count_photos >= 3 AND a.status NOT IN (5,1) AND
                                u.id NOT IN (3060,3504,4069,5251,5825,6862,6983,7340,8658,8950,8973,9234,10185,10400,12367)
                                ' . $condition . '
                        GROUP BY u.id
                        ORDER BY id ASC;
                ')->queryAll();

                $dataProvider = new CArrayDataProvider($users, array(
                        'pagination'=>array(
                                'pageSize'=>20,
                        )));

                $this->render('juneArchitect', array(
                        'dataProvider'=>$dataProvider,
                ));
        }

        /**
         * Действие, отмечающее архитектора как победителя в конкурсе
         * @param $uid - integer
         * @param $value - integer
         */
        public function actionJuneArchitectWinner($uid, $value)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(500);

                $uid = (int) $uid;
                $value = (int) $value;

                Yii::app()->db->createCommand("
                        UPDATE user_data SET june_archit_winner = '{$value}' WHERE user_id = '{$uid}'
                ")->execute();

                die(CJSON::encode(array('result'=>true)));
        }


}