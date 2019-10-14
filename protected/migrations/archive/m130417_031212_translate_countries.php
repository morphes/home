<?php

class m130417_031212_translate_countries extends CDbMigration
{
	public function up()
	{
		// Создаем поле для английского названия стран
		$this->addColumn('country', 'eng_name', 'VARCHAR(128) NOT NULL DEFAULT "" after name');


		$countries = Country::model()->findAll();

		foreach ($countries as $c) {
			$trans = new SimpleXMLElement(Yii::app()->curl->run('http://translate.yandex.net/api/v1/tr/translate?lang=ru-en&text='.urlencode($c->name)));
			Yii::app()->db
				->createCommand('UPDATE country SET eng_name = :en WHERE id = :id')
				->bindValues(array(
					':en' => Amputate::rus2route(mb_strtolower($trans->text[0])),
					':id' => $c->id
				))
				->execute();
		}
	}

	public function down()
	{
		$this->dropColumn('country', 'eng_name');
	}
}