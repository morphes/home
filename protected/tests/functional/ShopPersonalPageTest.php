<?php
class ShopPersonalPageTest extends WebTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->setElements();
	}

	public function setElements()
	{
		return array_merge(parent::setElements(), array(
				'shopProfile_link'			=>	'link=Мой профиль',
				'shopProfile_tab1'			=>	'',
				'shopProfile_tab2'			=>	'link=Магазины и товары',
				'shopProfile_tab3'			=>	'link=Добавление товаров',
				'shopList_sort_address'			=>	'//span[@data-fieldname="address"]/a',
				'shopList_sort_name'			=>	'//span[@data-fieldname="name"]/a',
				'shopList_sort_qt'			=>	'//span[@data-fieldname="product_qt"]/a',
				'shopList_firstItem_address'		=>	'//div[@class="added_stores_list"]/div[@class="item"][1]/div[1]/a',
				'shopList_firstItem_name'		=>	'//div[@class="added_stores_list"]/div[@class="item"][1]/div[1]/p',
				'shopList_firstItem_qt'			=>	'//div[@class="added_stores_list"]/div[@class="item"][1]/div[2]/a',
				'shopList_add_store'			=>	'link=Добавить новый магазин',
				'storeForm_name'			=>	'id=Store_name',
				'storeForm_city_id'			=>	'id=City_id',
				'storeForm_address'			=>	'id=Store_address',
				'storeForm_phone'			=>	'id=Store_phone',
				'storeForm_email'			=>	'id=Store_email',
				'storeForm_weekdaysWork_from'		=>	'id=weekdays_work_from',
				'storeForm_weekdaysWork_to'		=>	'id=weekdays_work_to',
				'storeForm_saturdayWork_from'		=>	'id=saturday_work_from',
				'storeForm_saturdayWork_to'		=>	'id=saturday_work_to',
				'storeForm_sundayWork_from'		=>	'id=sunday_work_from',
				'storeForm_sundayWork_to'		=>	'id=sunday_work_to',
				'storeForm_weekdaysDinner'		=>	'id=weekdays_dinner_enabled',
				'storeForm_weekdayDinner_from'		=>	'id=weekdays_dinner_from',
				'storeForm_weekdayDinner_to'		=>	'id=weekdays_dinner_to',
				'storeForm_saturdayDinner'		=>	'id=saturday_dinner_enabled',
				'storeForm_saturdayDinner_from'		=>	'id=saturday_dinner_from',
				'storeForm_saturdayDinner_to'		=>	'id=saturday_dinner_to',
				'storeForm_sundayDinner'		=>	'id=sunday_dinner_enabled',
				'storeForm_sundayDinner_from'		=>	'id=sunday_dinner_from',
				'storeForm_sundayDinner_to'		=>	'id=sunday_dinner_to',
				'storeForm_about'			=>	'id=Store_about',
				'goodsList_sort_name'			=>	'//span[@data-fieldname="name"]/a',
				'goodsList_sort_vendor'			=>	'//span[@data-fieldname="vendor"]/a',
				'goodsList_sort_cat'			=>	'//span[@data-fieldname="category"]/a',
				'goodsList_sort_date'			=>	'//span[@data-fieldname="date"]/a',
				'goodsList_sort_status'			=>	'//span[@data-fieldname="status"]/a',
				'goodsList_firstItem_name'		=>	'//div[@class="added_products_list"]/div[2]/div[1]/div[2]/span',
				'goodsList_firstItem_vendor'		=>	'//div[@class="added_products_list"]/div[2]/div[1]/div[2]/p',
				'goodsList_firstItem_cat'		=>	'//div[@class="added_products_list"]/div[2]/div[1]/div[3]',
				'goodsList_firstItem_date'		=>	'//div[@class="added_products_list"]/div[2]/div[1]/div[4]',
				'goodsList_firstItem_status'		=>	'//div[@class="added_products_list"]/div[2]/div[1]/div[5]/span',
				'goodsList_controls_editItem'		=>	'//div[@class="added_products_list"]/div[2]/div[%d]/div[5]/a'
			)
		);
	}
/*
	public function testShopListActions()
	{
		// Стартуем, логинимся
		$this->startAction('/', array('login' => 'ekatezha', 'password' => '1'));
		// Входим в "Мой профиль"
		$this->clickAndWait($this->getElement('shopProfile_link'));
		// Кликаем по табу "Магазины и товары"
		$this->clickAndWait($this->getElement('shopProfile_tab2'));
		// Проверяем, что вкладка открылась
		$this->assertEquals('Список магазинов', $this->getText('css=h1'));
		// Проверяем сортировку списка по адресу
		$expected = $this->getText($this->getElement('shopList_firstItem_address'));
		$this->click($this->getElement('shopList_sort_address'));
		$actual = $this->getText($this->getElement('shopList_firstItem_address'));
		try {
			$this->assertNotEquals($expected, $actual);
		} catch (Exception $e) {
			$this->fail('Exception: возможно не работает сортировка по адресу магазина');
		}
		sleep(1);
		// Проверяем сортировку списка по названию
		$expected = $this->getText($this->getElement('shopList_firstItem_address'));
		$this->click($this->getElement('shopList_sort_name'));
		$actual = $this->getText($this->getElement('shopList_firstItem_address'));
		try {
			$this->assertNotEquals($expected, $actual);
		} catch (Exception $e) {
			$this->fail('Exception: возможно не работает сортировка по названию магазина');
		}
		sleep(1);
		// Проверяем сортировку списка по количеству товаров
		$expected = $this->getText($this->getElement('shopList_firstItem_qt'));
		$this->click($this->getElement('shopList_sort_qt'));
		$actual = $this->getText($this->getElement('shopList_firstItem_qt'));
		try {
			$this->assertNotEquals($expected, $actual);
		} catch (Exception $e) {
			$this->fail('Exception: возможно не работает сортировка по количеству товаров');
		}
	}

	public function testGoodsListActions()
	{
		// Стартуем, логинимся
		$this->startAction('/', array('login' => 'ekatezha', 'password' => '1'));
		// Входим в "Мой профиль"
		$this->clickAndWait($this->getElement('shopProfile_link'));
		// Кликаем по табу "Магазины и товары"
		$this->clickAndWait($this->getElement('shopProfile_tab3'));
		// Проверяем, что вкладка открылась
		$this->assertEquals('Добавление товаров', $this->getText('css=h1'));
		// Проверяем сортировку списка по статусу
		$expected = $this->getText($this->getElement('goodsList_firstItem_status'));
		$this->click($this->getElement('goodsList_sort_status'));
		$actual = $this->getText($this->getElement('goodsList_firstItem_status'));
		try {
			$this->assertNotEquals($expected, $actual);
		} catch (Exception $e) {
			$this->fail('Exception: возможно не работает сортировка по статусу товара');
		}
		sleep(1);
		// Проверяем сортировку списка по категории
		$expected = $this->getText($this->getElement('goodsList_firstItem_cat'));
		$this->click($this->getElement('goodsList_sort_cat'));
		$actual = $this->getText($this->getElement('goodsList_firstItem_cat'));
		try {
			$this->assertNotEquals($expected, $actual);
		} catch (Exception $e) {
			$this->fail('Exception: возможно не работает сортировка по категории товара');
		}
		sleep(1);
		// Проверяем сортировку списка по дате добавления
		$expected = $this->getText($this->getElement('goodsList_firstItem_date'));
		$this->click($this->getElement('goodsList_sort_date'));
		$actual = $this->getText($this->getElement('goodsList_firstItem_date'));
		try {
			$this->assertNotEquals($expected, $actual);
		} catch (Exception $e) {
			$this->fail('Exception: возможно не работает сортировка по дате добавления товара');
		}
		sleep(1);
	}
*/
	public function testShopActions() {
		$this->startAction('/', array('login' => 'ekatezha', 'password' => '1'));
		// Входим в "Мой профиль"
		$this->clickAndWait($this->getElement('shopProfile_link'));
		// Кликаем по табу "Магазины и товары"
		$this->clickAndWait($this->getElement('shopProfile_tab2'));
		// Проверяем заголовок страницы
		$this->assertEquals("Список магазинов", $this->getText("css=h1"));
		// Жмем "Добавить новый магазин"
		$this->clickAndWait($this->getElement('shopList_addStore'));
		// Заполняем форму
		// Название магазина
		$this->type("id=Store_name", "Магазин_1");
		// Город
		$this->type("id=City_id", "Новос");
		$this->click("id=ui-active-menuitem");
		// Адрес
		$this->type("id=Store_address", "ул. Ленина, д. 1, корп. 2, стр. 3, оф. 4");
		// Телефон
		$this->type("id=Store_phone", "8 (383) 123-45-67");
		// E-mail
		$this->type("id=Store_email", "test_shop_1@mail.ru");
		//
		$this->click("id=weekdays_dinner_enabled");
		$this->assertTrue($this->isElementPresent("id=weekdays_dinner_from"));
		$this->assertTrue($this->isElementPresent("id=weekdays_dinner_to"));
		$this->click("id=saturday_dinner_enabled");
		$this->assertTrue($this->isElementPresent("id=saturday_dinner_from"));
		$this->assertTrue($this->isElementPresent("id=saturday_dinner_to"));
		$this->click("id=sunday_dinner_enabled");
		$this->assertTrue($this->isElementPresent("id=sunday_dinner_from"));
		$this->assertTrue($this->isElementPresent("id=sunday_dinner_to"));
		// Описание
		$this->type("id=Store_about", "Описание для Магазин_1");
		// Сохраняем
		$this->click("name=yt0");
		$this->waitForPageToLoad("30000");

	}
/*
	public function testGoodActions()
	{
		$this->startAction('/', array('login' => 'elena_shirnina', 'password' => '1'));
	}

	public function testGoodsCopying()
	{
		$this->startAction('/', array('login' => 'elena_shirnina', 'password' => '1'));
	}

	public function testGoodsLinkage()
	{
		$this->startAction('/', array('login' => 'elena_shirnina', 'password' => '1'));
	}
*/
}