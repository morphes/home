<?php

class m130717_033531_create_store_city extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_store_city', array(
			'city_id' => 'INT(11) NOT NULL',
			'store_id'     => 'INT(11) NOT NULL, PRIMARY KEY (`city_id`, `store_id`)',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		// migrate all GEO

		$sql = 'INSERT IGNORE INTO cat_store_city '
			.'( '
			.'select c.id as city_id, g.store_id  from cat_store_geo as g '
			.'INNER JOIN city as c ON c.id=g.geo_id AND g.`type`=1 '
			.') '
			.'UNION '
			.'( '
			.'select c2.id as city_id, g.store_id from cat_store_geo as g '
			.'INNER JOIN city as c2 ON c2.region_id=g.geo_id AND g.`type`=2 '
			.') '
			.'UNION '
			.'( '
			.'select c3.id as city_id, g.store_id from cat_store_geo as g '
			.'INNER JOIN city as c3 ON c3.country_id=g.geo_id AND g.`type`=3 '
			.')';

		Yii::app()->db->createCommand($sql)->execute();

	}

	public function down()
	{
		$this->dropTable('cat_store_city');
	}
}