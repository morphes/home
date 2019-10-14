<?php

class m130603_081916_alter_ideauploadedfile extends CDbMigration
{
	public function up()
	{
		$this->dropForeignKey('fk_solution_content_uploaded_file_uploaded_file1', 'idea_uploaded_file');
		$this->dropForeignKey('fk_solution_content_uploaded_file1', 'interior_content');
	}

	public function down()
	{
//		return true;
		echo "m130603_081916_alter_ideauploadedfile does not support migration down.\n";
		return false;
	}


}