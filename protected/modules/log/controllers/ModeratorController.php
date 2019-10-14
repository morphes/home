<?php

class ModeratorController extends AdminController
{
	
	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('operationlist'),
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
         * Отображает список пользователей с фильтром
         */
        public function actionOperationlist($user_id=null, $operation=null, $part=null, $time_from=null, $time_to=null)
        {

                if (Yii::app()->request->isAjaxRequest)
                        $this->layout = false;

                $condition = array();
                if ($user_id) {
                        $condition[] = 'user_id ='.CHtml::encode($user_id);
                }
                if ($operation) {
                        $condition[] = 'crud_id = '.CHtml::encode($operation);
                }
                if ($part) {
                        $condition[] = 'class_id = '.intval($part);
                }
                if ($time_from) {
                        $condition[] = 'create_time >= '.strtotime($time_from);
                }
                if ($time_to) {
                        $condition[] = 'create_time <= '.strtotime($time_to);
                }
		$condition = implode(' AND ', $condition);

                $dataProvider = new CActiveDataProvider('ModeratorLog', array(
                            'criteria' => array(
                                'condition' => $condition,
                                'order' => 'create_time DESC',
                            ),
                            'pagination' => array(
                                'pageSize' => 20,
                            ),
                        ));

		$moderators = User::getUsersByRoles(array(User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_SENIORMODERATOR));
                Yii::app()->user->setReturnUrl($this->createUrl($this->action->id));
                $this->render('operationlist', array('dataProvider' => $dataProvider, 'moderators' => $moderators, 'userId' => $user_id));
        }

}