<?php

class m130607_082659_alter_table_media_new extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.media.models.*');
		$this->addColumn('media_new','model_first','TINYINT(1) NOT NULL after article_first ');
		$this->addColumn('media_new','model_second','TINYINT(1) NOT NULL after article_second');
		$this->addColumn('media_new','model_third','TINYINT(1) NOT NULL after article_third ');
		$this->update('media_new', array('model_first'=>MediaNew::ARTICLE_MODEL_NEW,
						 'model_second'=>MediaNew::ARTICLE_MODEL_NEW,'model_third'=>MediaNew::ARTICLE_MODEL_NEW));
	}

	public function down()
	{
		$this->dropColumn('media_new', 'model_first');
		$this->dropColumn('media_new', 'model_second');
		$this->dropColumn('media_new', 'model_third');

	}
}