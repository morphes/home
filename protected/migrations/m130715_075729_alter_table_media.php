<?php

class m130715_075729_alter_table_media extends CDbMigration
{
	public function up()
	{
		$this->addColumn('media_knowledge', 'count_view', 'INT(11) NOT NULL after count_comment');

		Yii::import('application.modules.media.models.*');

		$pattern = 'Media:Knowledge:View:*';

		$keys = Yii::app()->redis->getKeysByPattern($pattern);
		echo 'Update Mediaknowledge';
		echo "\n";

		foreach ($keys as $key) {

			preg_match('#^Media:Knowledge:View:([\d]+)#', $key, $matches);

			if ($matches[1]) {
				$id = (int)$matches[1];
				echo 'Update ' . $id;
				echo "\n";

				$count = Yii::app()->redis->get($key);

				MediaKnowledge::model()->updateByPk($id, array('count_view' => $count));
			}
		}

	}

	public function down()
	{
		$this->dropColumn('media_knowledge', 'count_view');
	}

}