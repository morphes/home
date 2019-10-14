<?php

class m130521_082614_alter_cat_category extends CDbMigration
{
	public function up()
	{
		Yii::import('application.modules.catalog.models.Category');

		// Создаем поле винительного падежа
		$this->addColumn('cat_category', 'accusativeCase', 'VARCHAR(255) NOT NULL DEFAULT "" after genitiveCase');


		$cats = Category::model()->findAll();

		foreach ($cats as $c) {

			$resp = new SimpleXMLElement(Yii::app()->curl->run('http://morpher.ru/WebService.asmx/GetXml?s='.urlencode($c->name)));

			if ($resp->code != 5) {
				// В - русская буква, обозночающая Родительный падеж
				$accusativeCase = (string)$resp->В;
				echo $accusativeCase . ', ';

				Yii::app()->db
					->createCommand('UPDATE cat_category SET accusativeCase = :n WHERE id = :id')
					->bindValues(array(
						':n' => $accusativeCase,
						':id' => $c->id
					))
					->execute();
			}
		}
	}

	public function down()
	{
		$this->dropColumn('cat_category', 'accusativeCase');
	}

}