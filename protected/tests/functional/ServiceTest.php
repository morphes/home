<?php
class ServiceTest extends WebTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->setElements();
	}

	public function setElements()
	{
		return array_merge(parent::setElements(), array(

			)
		);
	}	

	public function serviceData()
	{
		return array(
			// data set #1
			array(1,
				array(
					// Дизайн и архитектура
					'category' => 1,
					// Услуги
					'items' => array(
						// Дизайн интерьера
						array(1, 3, 2, 4),
						// Архитектурный дизайн
						array(2, 2, 3, 3),
					),
				),
				array(
					// Монтажные работы
					'category' => 2,
					// Услуги
					'items' => array(
						// Установка окон
						array(1, 3, 2, 4),
						// Установка лестниц
						array(3, 2, 3, 3),
					),
				),
			),
			// data set #2
		);
	}

	/**
	 *	@dataProvider serviceData
	 */
	public function testService($iteration, $service)
	{
		$users = explode('\n', file_get_contents('/var/www/myhome/protected/tests/functional/data/users.txt'));
		$user = explode(':', $users[1]);
		try {
			if ($user[0] != '' && $user[1] != '') {
				break;
			}
		} catch (Exception $e) {}
		// Стартуем, входим на сайт
		$this->startAction('/', array('login' => $user[0], 'password' => $user[1]));
	}
}	
?>