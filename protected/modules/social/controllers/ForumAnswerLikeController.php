<?php

class ForumAnswerLikeController extends AdminController
{
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions' => array('add'),
				'users' => array('@'),
			),
			array('deny',
				'users' => array('*'),
			),
		);
	}


	public function actionAdd()
	{
		$success = false;
		$errorMsg = '';

		$answerId = intval(Yii::app()->request->getParam('answerId'));
		$modelAnswer = ForumAnswer::model()->findByPk($answerId);

		if ( ! $modelAnswer) {
			$errorMsg = 'Answer ID is not found';
			goto the_end;
		}

		$longIp = ip2long($_SERVER['REMOTE_ADDR']);
		$userAgentMD5 = md5($_SERVER['HTTP_USER_AGENT']);

		if (ForumAnswerLike::canVote($modelAnswer->id, $longIp, $userAgentMD5))
		{
			$like = new ForumAnswerLike();
			$like->long_ip   = $longIp;
			$like->useragent = $userAgentMD5;
			$like->user_id   = Yii::app()->user->id;
			$like->answer_id = $modelAnswer->id;

			if ($like->save()) {
				Yii::app()->db->createCommand("UPDATE forum_answer SET count_like = count_like + 1 WHERE id = '{$modelAnswer->id}'")->execute();
				$success = true;
			} else {
				$errorMsg = 'Voting error';
				goto the_end;
			}

		} else {
			$errorMsg = 'You already voted on this answer';
			goto the_end;
		}


		the_end:
		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg
		)));
	}

}