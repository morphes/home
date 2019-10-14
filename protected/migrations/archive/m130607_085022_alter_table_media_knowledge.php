<?php

class m130607_085022_alter_table_media_knowledge extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.media.models.*');
		$this->addColumn('media_knowledge','model_first','TINYINT(1) NOT NULL after article_first ');
		$this->addColumn('media_knowledge','model_second','TINYINT(1) NOT NULL after article_second');
		$this->addColumn('media_knowledge','model_third','TINYINT(1) NOT NULL after article_third ');
		$this->update('media_knowledge', array('model_first'=>MediaNew::ARTICLE_MODEL_KNOWLEDGE,
						 'model_second'=>MediaNew::ARTICLE_MODEL_KNOWLEDGE,
						 'model_third'=>MediaNew::ARTICLE_MODEL_KNOWLEDGE));
	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'model_first');
		$this->dropColumn('media_knowledge', 'model_second');
		$this->dropColumn('media_knowledge', 'model_third');

	}
}