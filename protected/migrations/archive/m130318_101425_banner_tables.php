<?php

class m130318_101425_banner_tables extends CDbMigration
{
	public function up()
	{
		// список заказанных баннеров
		$this->createTable('banner_item', array(
			'id' => 'pk',
			'user_id'=>'integer not null',
			'type_id'=>'integer not null',
			'customer'=>'string not null default ""',
			'status'=>'tinyint(1) not null',
			'file_id'=>'integer',
			'create_time' => 'integer not null',
			'update_time' => 'integer not null',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('idx_user_id', 'banner_item', 'user_id');
		$this->createIndex('idx_type_id', 'banner_item', 'type_id');
		$this->createIndex('idx_status', 'banner_item', 'status');

		// настройка ротации баннера для каждого раздела сайта
		$this->createTable('banner_item_section', array(
			'id' => 'pk',
			'item_id' => 'integer not null',
			'section_id'=>'integer not null',
			'tariff_id'=>'tinyint(1) not null',
			'start_time'=>'integer not null',
			'end_time'=>'integer not null',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		// настройка гео-привязанности баннера для конкретного раздела сайта
		$this->createTable('banner_item_section_geo', array(
			'id' => 'pk',
			'item_id' => 'integer',
			'section_id'=>'integer not null',
			'city_id'=>'integer',
			'region_id'=>'integer',
			'country_id'=>'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');
		$this->createIndex('idx_item_id_section_id', 'banner_item_section_geo', 'item_id, section_id');

		$this->createTable('banner_rotation', array(
			'id' => 'pk',
			'section_id'=>'integer not null',
			'type_id'=>'integer not null',
			'geo_id'=>'integer not null',
			'city_id'=>'integer not null',
			'region_id'=>'integer not null',
			'country_id'=>'integer not null',
			'start_time'=>'integer not null',
			'end_time'=>'integer not null',
			'item_id'=>'integer not null',
			'tariff_id'=>'tinyint(1) not null',
			'file_id'=>'integer not null',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('idx_section_id', 'banner_rotation', 'section_id');
		$this->createIndex('idx_geo_id', 'banner_rotation', 'geo_id');
		$this->createIndex('idx_city_id', 'banner_rotation', 'city_id');
		$this->createIndex('idx_region_id', 'banner_rotation', 'region_id');
		$this->createIndex('idx_country_id', 'banner_rotation', 'country_id');
		$this->createIndex('idx_type_id', 'banner_rotation', 'type_id');
		$this->createIndex('idx_start_time', 'banner_rotation', 'start_time');
		$this->createIndex('idx_end_time', 'banner_rotation', 'end_time');
	}

	public function down()
	{
		$this->dropTable('banner_item');
		$this->dropTable('banner_item_section');
		$this->dropTable('banner_item_section_geo');
		$this->dropTable('banner_rotation');
	}
}