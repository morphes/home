<?php

class m130418_014219_alter_cat_category extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.catalog.models.Category');

		// Создаем поле для английского названия стран
		$this->addColumn('cat_category', 'genitiveCase', 'VARCHAR(255) NOT NULL DEFAULT "" after eng_name');


		$cats = Category::model()->findAll();

		foreach ($cats as $c) {

			$resp = new SimpleXMLElement(Yii::app()->curl->run('http://morpher.ru/WebService.asmx/GetXml?s='.urlencode($c->name)));

			if ($resp->code != 5) {
				// Р - русская буква, обозночающая Родительный падеж
				$genetiveCase = (string)$resp->Р;
				echo $genetiveCase . ', ';

				Yii::app()->db
					->createCommand('UPDATE cat_category SET genitiveCase = :n WHERE id = :id')
					->bindValues(array(
						':n' => $genetiveCase,
						':id' => $c->id
					))
					->execute();
			}
		}
	}

	public function down()
	{
		$this->dropColumn('cat_category', 'genitiveCase');
	}
}