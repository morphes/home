<?php

class UserCommand extends CConsoleCommand
{
	/**
	 * Пересчитывает количество проектов для каждого юзера
	 * и складывает это значение в UserData.project_quantity 
	 */
	public function actionRecountProjectQuantity()
	{
		$start = time();
		echo 'Time start: '.date('d-M-Y h:i:s')."\n";

		Yii::import('application.modules.idea.models.*');
		Yii::import('application.models.*');
		$users = Yii::app()->db->createCommand('SELECT id FROM `user`')->queryAll();
                
                foreach ($users as $user) {
			$project_quantity = Interior::model()->scopeOwnPublic($user['id'])->count();
			$project_quantity += Interiorpublic::model()->scopeOwnPublic($user['id'])->count();
			$project_quantity+= Portfolio::model()->scopeOwnPublic($user['id'])->count();
			$project_quantity+= Architecture::model()->scopeOwnPublic($user['id'])->count();
                        UserData::model()->updateByPk($user['id'], array('project_quantity' => $project_quantity));
                }
                
		echo 'Time stop: '.date('d-M-Y h:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n";
	}


        /**
         * Пересчитывает количество комментариев для каждого юзера
         * и складывает это значение в UserData.comment_count
         */
        public function actionCountCommentQuantity()
        {
                Yii::import('application.models.*');

                $users = User::model()->findAll();

                foreach ($users as $user) {

                        $comment_count = Comment::model()->count('author_id=:uid', array(':uid'=>$user->id));
                        UserData::model()->updateByPk($user->id, array('comment_count' => $comment_count));
                }

                echo 'Finished';
        }

	/**
	 * Для всех пользователей проверяет их firstname и lastname,
	 * удаляя крайние пробелы. 
	 */
	public function actionTrimFio()
	{
		Yii::import('application.models.*');

		$users = User::model()->findAll();
                
                foreach ($users as $user) {
                        User::model()->updateByPk($user->id, array(
				'firstname' => trim($user->firstname),
				'lastname' => trim($user->lastname),
			));
                }
                
                echo 'Finished';
	}

        /**
         * Пересчет количества пользователей для услуги 
         */
        public function actionRecountService()
        {
            $start = time();
            echo "Recount service \n";
            echo 'Time start: '.date('d-M-Y h:i:s')."\n";
            Yii::import('application.modules.member.models.*');
            Yii::app()->db->createCommand(
			'UPDATE service '
                        .'LEFT JOIN ( '
				.'SELECT service_id, count(user_id) as user_quantity FROM user_service '
				.'INNER JOIN user ON user.id=user_service.user_id '
				.'WHERE user.status='.User::STATUS_ACTIVE.' AND user.role IN ('.User::ROLE_SPEC_FIS.','.User::ROLE_SPEC_JUR.') '
				.'GROUP BY service_id '
                        .') as tmp ON tmp.service_id = service.id '
                        .'SET service.user_quantity = tmp.user_quantity'
		)->execute();
            
            echo 'Time stop: '.date('d-M-Y h:i:s')."\n";
            echo 'Total time: '.(time()-$start)."\n";
        }

}
