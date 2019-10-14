<?php

class m130923_040203_update_user_servicecity extends CDbMigration
{
	public function up()
	{
		$conn = Yii::app()->db;

		$specs = $conn->createCommand()->select('id as user_id, city_id')->from('user')
			->where('role=:fiz or role=:jur', array(
				':fiz'=>User::ROLE_SPEC_FIS,
				':jur'=>User::ROLE_SPEC_JUR,
			))
			->queryAll();

		foreach($specs as $spec) {

			$exist = $conn->createCommand()->select('user_id')->from('user_servicecity')
				->where('user_id=:uid and city_id=:cid', array(
					':uid'=>$spec['user_id'],
					':cid'=>$spec['city_id'],
				))->queryScalar();

			if ( $exist )
				continue;

			$city = City::model()->findByPk($spec['city_id']);
			if ( !$city )
				continue;

			$conn->createCommand()->insert('user_servicecity', array(
				'user_id'=>$spec['user_id'],
				'city_id'=>$city->id,
				'region_id'=>$city->region->id,
				'country_id'=>$city->country->id,
			));
		}
	}

	public function down()
	{
		return true;
	}
}