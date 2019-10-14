<?php

class m130514_044105_alter_lents_logotips extends CDbMigration
{
	public function up()
	{
		$this->addColumn('cat_tapestore', 'city_id', 'integer not null after `id`');
		$this->addColumn('index_product_brand', 'city_id', 'integer not null after `id`');

		$this->update('cat_tapestore', array('city_id'=>4549));
		$this->update('index_product_brand', array('city_id'=>4549));
	}

	public function down()
	{
		$this->dropColumn('cat_tapestore', 'city_id');
		$this->dropColumn('index_product_brand', 'city_id');
	}
}