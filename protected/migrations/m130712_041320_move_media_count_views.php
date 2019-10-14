<?php

class m130712_041320_move_media_count_views extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.media.models.*');


		$this->renameColumn('media_new', 'count_view', 'count_view_old');

		// Перенос просмотров Новостей
		$models = MediaNew::model()->findAll();
		if ($models) {
			echo "Кол-во Новостей для переноса просмотров: " . count($models);
			foreach ($models as $item) {
				// Получаем текущее значение, которое лежит в редисе
				$currentViews = (int)Yii::app()->redis->get(MediaNew::getCacheKeyView($item->id));
				// Получаем старое значение, которо лежит в БД
				$oldViews = (int)Yii::app()->db
					->createCommand('SELECT count_view_old FROM media_new WHERE id = ' . $item->id)
					->queryScalar();
				// Складываем сумму текущих и старых просмотров в редис.
				Yii::app()->redis->set(MediaNew::getCacheKeyView($item->id), $currentViews + $oldViews);
			}

			$this->dropColumn('media_new', 'count_view_old');
		}
	}

	public function down()
	{
		echo "m130712_041320_move_media_count_views does not support migration down.\n";
		return false;
	}
}