<?php

class m130904_041525_alter_ideas extends CDbMigration
{
	public function up()
	{
		$this->addColumn('interior', 'count_rejected', 'INT(11) NOT NULL DEFAULT 0 after count_photos');
		$this->addColumn('interiorpublic', 'count_rejected', 'INT(11) NOT NULL DEFAULT 0 after count_photos');
		$this->addColumn('architecture', 'count_rejected', 'INT(11) NOT NULL DEFAULT 0 after count_photos');
	}

	public function down()
	{
		$this->dropColumn('interior', 'count_rejected');
		$this->dropColumn('interiorpublic', 'count_rejected');
		$this->dropColumn('architecture', 'count_rejected');
	}
}