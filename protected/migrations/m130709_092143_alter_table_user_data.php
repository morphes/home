<?php

class m130709_092143_alter_table_user_data extends CDbMigration
{
	public function up()
	{
		$this->addColumn('user_data', 'review_count', 'INT(11) default 0 after average_rating');

		$sql = 'SELECT user_id FROM user_data';
		$result = Yii::app()->db->createCommand($sql)->queryColumn();
		foreach ($result as $userId) {
			$sqlCount = 'SELECT count(*) FROM review WHERE type = 1 AND status = 1 AND spec_id=' . $userId;
			$count = Yii::app()->db->createCommand($sqlCount)->queryScalar();

			if ($count) {
				Yii::app()->db->createCommand()->update('user_data', array(
					'review_count' => intval($count),
				), 'user_id=:uid', array(':uid' => $userId));
			}
		}
	}

	public function down()
	{
		$this->dropColumn('user_data','review_count');


	}
}