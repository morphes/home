<?php

//require 'CsvFileIterator.php';

class CatalogueTest extends WebTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->setElements();
	}

	public function setElements()
	{
		return array_merge(parent::setElements(), array(
				'step_1'	=>	'link=Центр управления',
				'step_2'	=>	'link=Каталог товаров',
				'step_3'	=>	'//div[@id="category-grid"]/table/tbody/tr[%d]/td/a',
				'step_4'	=>	'name=yt3',
				'step_5'	=>	'name=yt1',
				'input'		=>	'//div[@id="category-ext-options"]/table/tbody/tr[%d]/td[%d]/%s'
			)
		);
	}
/*
	public function registrationDataSet()
	{
		return new CsvFileIterator('/var/www/myhome/protected/tests/functional/data/registration.csv');
	}
*/
	public $collection = array(
		// Мебель
		'1'	=>	array(
			// Мягкая мебель
			'1'	=>	array(
				// Диваны
				'1'	=>	array(
					'input',
					'textarea',
					'textarea',
					'input',
					'input'
				)
			)
		)
	);

	public function dataSet()
	{
		return array(
			array('1', '1', '1', array('Наименование дивана', 'Описание дивана', 'Теги, тег1, тег2, тег3', 'Артикул', '100.50'))
		);
	}
	
	/**
	*	@dataProvider dataSet
	*/
	public function testGoods($cat, $sub, $div, $data)
	{
		$this->startAction('/', array('login' => 'logonarium', 'password' => '1'));
		$this->clickAndWait($this->getElement('step_1'));
		$this->clickAndWait($this->getElement('step_2'));
		$this->clickAndWait($this->getElement('step_3', $cat));
		$this->clickAndWait($this->getElement('step_3', $sub));
		$this->clickAndWait($this->getElement('step_3', $div));
		$this->clickAndWait($this->getElement('step_4'));
		$this->click($this->getElement('step_5'));
		sleep(1);
		$i = 0; $tr = 1; $td = 2;
		while($i < count($this->collection[$sub][$cat][$div])) {
			$this->type($this->getElement('input', array($tr, $td, $this->collection[$cat][$sub][$div][$i])), $data[$i]);
			$i++; $td++;
		}
		sleep(5);
	}
}
?>