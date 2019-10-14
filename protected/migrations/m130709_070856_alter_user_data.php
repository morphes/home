<?php

class m130709_070856_alter_user_data extends CDbMigration
{
	public function up()
	{
		$this->addColumn('user_data', 'average_rating', 'INT(11) NOT NULL after review_recommend');

		$sql = 'SELECT user_id FROM user_data';
		$result = Yii::app()->db->createCommand($sql)->queryColumn();
		foreach ($result as $userId) {
			$sqlStat = 'SELECT AVG(rating) FROM review WHERE type = 1 AND status = 1 AND spec_id=' . $userId;
			$stat = Yii::app()->db->createCommand($sqlStat)->queryScalar();

			if ($stat) {
				Yii::app()->db->createCommand()->update('user_data', array(
					'average_rating' => intval($stat),
				), 'user_id=:uid', array(':uid' => $userId));
			}
		}
	}

	public function down()
	{
		$this->dropColumn('user_data','average_rating');


	}
}