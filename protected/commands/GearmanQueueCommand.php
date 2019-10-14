<?php

/**
 * User: desher
 * Date: 13.07.12
 * Time: 10:40
 */
class GearmanQueueCommand extends CConsoleCommand
{
        public function actionWorker()
        {
                $worker = Yii::app()->gearman->worker();

		$worker->addFunction('mail:send_mailer', array($this, 'sendMailer'));
		$worker->addFunction('mail:send_tender', array($this, 'sendTender'));
                $worker->addFunction('mail:sendmail', array($this, 'sendmail'));

                while($worker->work() ){
                        if (GEARMAN_SUCCESS != $worker->returnCode()) {
                                echo "Worker failed: " . $worker->error() . "\n";
                        }
			echo "\n";
                }

        }

	/**
	 * Генерация писем для тендера и постановка их на отправку
	 * @param GearmanJob $job
	 * @return mixed
	 */
	public function sendTender(GearmanJob $job)
	{

		$workload = $job->workload();
		echo date("[Y-m-d H:i:s]") . ' '.__METHOD__.' ';
		$data = array('total' => 0, 'success' => 0);

		try {
			Yii::import('application.modules.tenders.models.Tender');
			$tenderId = unserialize($workload);
			/** @var $tender Tender */
			$tender = Tender::model()->findByPk($tenderId);
			/** @var afterSaveCommit убираем обработку afterSave */
			$tender->afterSaveCommit = true;

			if ( empty($tender) || !( in_array($tender->status, array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED) ) && $tender->send_notify == Tender::NOTIFY_SEND ) ) {
				echo 'Invalid Tender ID:'.$tenderId;
				return;
			}

			$signA = Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage();

			$tender->send_notify = Tender::NOTIFY_SENDED;
			$tender->save(false);
            $tender->updateSphinx();

			$sql = 'SELECT u.email, CONCAT_WS(" ", u.firstname,u.lastname,u.secondname) as name '
				.'FROM user as u '
				.'INNER JOIN user_data as ud ON ud.user_id=u.id '
				.'INNER JOIN ( '
					.'SELECT DISTINCT us.user_id FROM user_service as us '
					.'INNER JOIN tender_service as ts ON ts.service_id = us.service_id '
					.'WHERE ts.tender_id = '.$tender->id.' '
				.') as tmp ON tmp.user_id = u.id '
				.'INNER JOIN ( '
					.'SELECT DISTINCT usc.user_id FROM user_servicecity as usc '
					.'INNER JOIN city  ON  city.id=usc.city_id OR (ISNULL(usc.city_id) AND city.region_id = usc.region_id) '
					.'WHERE city.id = '.$tender->city_id.' '
				.') as citytmp ON citytmp.user_id = u.id '
				.'WHERE u.status='.User::STATUS_ACTIVE.' AND (u.role='.User::ROLE_SPEC_FIS.' OR u.role='.User::ROLE_SPEC_JUR.') AND ud.tender_notify=1 ';
			if (!is_null($tender->author_id))
				$sql .='AND u.id<>'.$tender->author_id;
			
			$sendData = Yii::app()->db->createCommand($sql)->queryAll();

			$data['total'] = count($sendData);
			$closeTime = Yii::app()->getDateFormatter()->format('d MMMM yyyy', $tender->expire);
			$cityName = $tender->getCityName();

			foreach ($sendData as $item) {
				Yii::app()->mail->create('tenderSpecNotify')
					->to($item['email'])
					->priority(EmailComponent::PRT_LOW)
					->params(array(
					'user_name' 	=> trim($item['name']),
					'tender_name'	=> CHtml::link($tender->name, Yii::app()->homeUrl.$tender->getLink()),
					'close_time'	=> $closeTime,
					'city_name'	=> $cityName,
					'sign_A'	=> $signA,
				))
				->send();
				$data['success']++;
			}

		} catch (Exception $e) {
			echo 'Error data: '.$workload."\n";
			echo $e->getMessage();
		}
		echo 'TENDER DATA:'.$workload.' TOTAL:'.$data['total'].' SUCCESS:'.$data['success'].' FAIL:'.($data['total']-$data['success']);
	}

	/**
	 * Генерация писем для рассылки и постановка их на отправку
	 * @param GearmanJob $job
	 * @return mixed
	 */
	public function sendMailer(GearmanJob $job)
	{
		Yii::import('application.modules.admin.models.*');
		$workload = $job->workload();
		$data = array('total' => 0, 'success' => 0);
		echo date("[Y-m-d H:i:s]") . ' createMailer ';

		try {
			$deliveryId = unserialize($workload);
			$delivery = Mailer::model()->findByPk($deliveryId);
			if ( empty($delivery) || $delivery->status != Mailer::STATUS_TO_SEND) {
				echo 'Invalid Delivery ID:'.$deliveryId;
				return;
			}

			/** @var $command CDbCommand */
			$command = Yii::app()->db->createCommand()
				->select('user.email, user.firstname')
				->from('user');
			if (!empty($delivery->group_id)) { // при указанной группе, фильтр по группе
				$command->setJoin('LEFT JOIN user_groupuser AS ug ON ug.user_id=user.id');
				$command->where('ug.group_id=:group', array(':group' => $delivery->group_id));
			}

			if (!empty($delivery->role)) { // При указанной роли фильтр по ролям
				$where = $command->getWhere();
				if (!empty($where)) { // склеивание условий
					$where .= ' AND ';
				}
				$where .= 'user.role=:role';
				$command = $command->where($where, array(':role' => $delivery->role));
			}

			if (!empty($delivery->user_status)) {
				$where = $command->getWhere();
				if (!empty($where)) { // склеивание условий
					$where .= ' AND ';
				}
				$where .= 'user.status=:status';

				$command = $command->where($where, array(':status' => $delivery->user_status));
			}

			// исключение юзеров, отказавшихся от рассылки
			$join = $command->getJoin();
			$command->setJoin($join.' INNER JOIN user_data ON user.id=user_data.user_id');
			$where = $command->getWhere();
			if (!empty($where)) { // склеивание условий
				$where .= ' AND ';
			}
			$where .= 'portal_notice = 1) OR user.role='.User::ROLE_POWERADMIN;
			$where = '('.$where;
			$command->where($where);

			$users = $command->queryAll();

			$data['total'] = count($users);

			foreach ($users as $user) {
				Yii::app()->mail->create()
					->from(array('email'=>$delivery->from, 'author'=>$delivery->author))
					->subject($delivery->subject)
					->to($user['email'])
					->message($delivery->data)
					->useView(false)
					->priority(EmailComponent::PRT_LOW)
					->params(array(
						'username' => $user['firstname'],
					))
					->send();
				$data['success']++;
			}

			$delivery->status = Mailer::STATUS_SENDED;
			$delivery->save(false);

		} catch (Exception $e) {
			echo 'Error data: '.$workload."\n";
			echo $e->getMessage();
		}

		echo 'DELIVERY DATA:'.$workload.' TOTAL:'.$data['total'].' SUCCESS:'.$data['success'].' FAIL:'.($data['total']-$data['success']);
	}

	/**
	 * Отправка писем
	 * @param GearmanJob $job
	 * @return string
	 * @throws Exception
	 */
	public function sendmail(GearmanJob $job)
        {
                echo date("[Y-m-d H:i:s]") . ' sendmail ';

                $workload = $job->workload();

                try {
                        $mail = unserialize($workload);

                        if (
				!is_array($mail)
				|| !isset($mail['language'])
				|| !isset($mail['mail_table'])
				|| !isset($mail['template_key'])
                                || !isset($mail['to'])
				|| !isset($mail['subject'])
				|| !isset($mail['message'])
				|| !isset($mail['mailhash'])
                                || !isset($mail['headers'])
				|| !isset($mail['from_email'])
				|| !isset($mail['from_author'])
				|| !isset($mail['enable_log'])
			) {
                                throw new Exception("Wrong mail parameters");
                        }

                        echo $mail['to'] . ' ';

                        mb_language($mail['language']);
                        $send_status = mail($mail['to'], $mail['subject'], $mail['message'], $mail['headers'], '-f'.$mail['from_email']);

			$data_for_log = array();

			// Если выставлен флаг
			if ($mail['enable_log'] == true) {

				$data_for_log = array(
					'status'       => $send_status
						? EmailComponent::STATUS_SENDED
						: EmailComponent::STATUS_NOT_SENDED,
					'mailhash'     => $mail['mailhash'],
					'to'           => $mail['to'],
					'template_key' => $mail['template_key'],
					'from_email'   => $mail['from_email'],
					'from_author'  => $mail['from_author'],
					'subject'      => $mail['subject'],
					'message'      => $mail['message'],
					'create_time'  => time(),
				);

				Yii::app()->db->createCommand()->insert($mail['mail_table'], $data_for_log);
			}

                        echo 'sended ';

                        return serialize($data_for_log);

                } catch (Exception $e) {
                        echo $e->getMessage();
                }
        }
}