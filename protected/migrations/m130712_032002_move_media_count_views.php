<?php

class m130712_032002_move_media_count_views extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.media.models.*');


		$this->renameColumn('media_knowledge', 'count_view', 'count_view_old');

		// Перенос просмотров Статей
		$models = MediaKnowledge::model()->findAll();
		if ($models) {
			echo "Кол-во Статей для переноса просмотров: " . count($models);
			foreach ($models as $item) {
				// Получаем текущее значение, которое лежит в редисе
				$currentViews = (int)Yii::app()->redis->get(MediaKnowledge::getCacheKeyView($item->id));
				// Получаем старое значение, которо лежит в БД
				$oldViews = (int)Yii::app()->db
					->createCommand('SELECT count_view_old FROM media_knowledge WHERE id = ' . $item->id)
					->queryScalar();
				// Складываем сумму текущих и старых просмотров в редис.
				Yii::app()->redis->set(MediaKnowledge::getCacheKeyView($item->id), $currentViews + $oldViews);
			}

			$this->dropColumn('media_knowledge', 'count_view_old');
		}
	}

	public function down()
	{
		echo "m130712_032002_move_media_count_views does not support migration down.\n";
		return false;
	}
}