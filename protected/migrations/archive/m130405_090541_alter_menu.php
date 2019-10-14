<?php

class m130405_090541_alter_menu extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.catalog.models.Category');

		Yii::app()->db->createCommand("DELETE FROM menu WHERE type_id = :t")
			->bindValue(':t', Menu::TYPE_FOOTER_CAT)->execute();

		$sql = 'INSERT INTO menu (`key`, `type_id`, `parent_id`,'
			.' `status`, `label`, `url`, `create_time`, `update_time`) VALUES';


		$arrCategory = array(
			7, // Мебель
			15, // Сантекхинка
			30, // Освещение
			71, // Аксесcуары и декор
			34, // Отделочные материалы
			37, // Двери
		);

		$sqlValues = '';

		foreach ($arrCategory as $catId) {
			$model = Category::model()->findByPk($catId);
			$url = '/catalog/' . $model->eng_name;

			if ($sqlValues != '') {
				$sqlValues .= ',';
			}

			$sqlValues .= "('".$model->eng_name."', '".Menu::TYPE_FOOTER_CAT."', 0, "
				."'1', '".$model->name."', '".$url."', NOW(), NOW())";

		}

		Yii::app()->db->createCommand($sql . $sqlValues)->execute();
	}

	public function down()
	{
		Yii::app()->db->createCommand("DELETE FROM menu WHERE type_id = ".Menu::TYPE_FOOTER_CAT);
	}
}