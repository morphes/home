<?php

class m130618_032128_alter_city extends CDbMigration
{
	public function up()
	{
		$this->addColumn('city', 'lat', 'FLOAT(5, 2) after `region_id`');
		$this->addColumn('city', 'lng', 'FLOAT(5, 2) after `lat`');

		$cities = City::model()->findAll();
		echo "\n\r";

		foreach($cities as $city) {
			$coord = YandexMap::getGeocode($city->name);
			$coord = unserialize($coord);
			if (isset($coord[0]) && isset($coord[1]))
				City::model()->updateByPk($city->id, array(
					'lng'=>round($coord[0], 2),
					'lat'=>round($coord[1], 2),
				));
			else
				echo "\n".$city->name . " broken" . "\n";
			echo "\r" . $city->id . " detected";
		}
		echo "\n\r" . 'Completed!' . "\n";
	}

	public function down()
	{
		$this->dropColumn('city', 'lat');
		$this->dropColumn('city', 'lng');
	}

}