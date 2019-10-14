<?php

class m130627_042817_insert_report_service extends CDbMigration
{
	public function up()
	{
		$this->insert('report_service', array('service_id'=>3, 'pos'=>16));
		$this->insert('report_service', array('service_id'=>8, 'pos'=>17));
		$this->insert('report_service', array('service_id'=>30, 'pos'=>18));
		$this->insert('report_service', array('service_id'=>50, 'pos'=>19));
		$this->insert('report_service', array('service_id'=>51, 'pos'=>20));
		$this->insert('report_service', array('service_id'=>53, 'pos'=>21));
	}

	public function down()
	{
		return true;
	}
}