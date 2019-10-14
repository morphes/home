<?php
class UserProfileTest extends WebTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->setElements();
	}

	public function setElements()
	{
		return array_merge(parent::setElements(), array(
				'profile_edit'				=>	'link=Редактировать профиль',
				'profile_image'				=>	'id=User_image_file',
				'profile_firstname'			=>	'id=User_firstname',
				'profile_lastname'			=>	'id=User_lastname',
				'profile_email'				=>	'id=User_email',
				'profile_gender'			=>	'id=fp-sex-%s',
				'profile_bDay'				=>	'id=fp-bd-day',
				'profile_bMonth'			=>	'id=fp-bd-month',
				'profile_bYear'				=>	'id=fp-bd-year',
				'profile_specAbout'			=>	'css=body.mceContentBody',
				'profile_phone'				=>	'id=User_phone',
				'profile_specPhone'			=>	'id=fp-contacts-phone',
				'profile_skype'				=>	'id=UserData_skype',
				'profile_icq'				=>	'id=UserData_icq',
				'profile_site'				=>	'id=UserData_site',
				'profile_city'				=>	'id=User_city_id',
				'profile_cityListItem'		=>	'//body/ul[@role="listbox"]/li[%d]/a',
				'profile_oldPassword'		=>	'id=User_old_password',
				'profile_password'			=>	'id=User_password',
				'profile_password2'			=>	'id=User_password2',
				'profile_passChangeBalloon'	=>	'css=p.good-title',
				'submit'					=>	'css=input.btn_grey',
				'profile_tab'				=>	'//div[@id="right_side"]/div[1]/ul/li[%d]/a',
				'profile_menuItem'			=>	'//div[@id="user_menu"]/ul/ul/li[%d]/a',
				'profile_settingsChk'		=>	'//ul[@class="checkbox-list"]/li[%d]/label/input[2]',
				'profile_back'				=>	'css=a.back_to_profile',
				'step_1'					=>	'link=Центр управления',
				'step_2'					=>	'link=Пользователи сайта',
				'td'						=>	'//div[@id="user-grid"]/table/tbody/tr[%d]/td[%d]%s'
			)
		);
	}

	/**
	*	User data provider
	*/
	public function userData()
	{
		return array(
			array('1',
				array(
					'type'					=>	'user',
					'image'					=>	true,
					'firstname'				=>	'Алексей',
					'lastname'				=>	'Кулинкин',
					'email'					=>	'test_user_%d@mail.ru',
					'gender'				=>	'female',
					'b_day'					=>	'13',
					'b_month'				=>	'Августа',
					'b_year'				=>	'1978',
					'about'					=>	'',
					'phone'					=>	'89039090212',
					'skype'					=>	'aleksey',
					'icq'					=>	'9004567',
					'site'					=>	'www.aleksey.com',
					'city'					=>	'Москва',
					'city_list_item'		=>	'1'
				)
			),
			array('2',
				array(
					'type'					=>	'spec',
					'image'					=>	true,
					'firstname'				=>	'Владимир',
					'lastname'				=>	'Амбросов',
					'email'					=>	'test_user_%d@mail.ru',
					'gender'				=>	'male',
					'b_day'					=>	'19',
					'b_month'				=>	'Октября',
					'b_year'				=>	'1989',
					'about'					=>	'Текст поля формы "О себе"',
					'phone'					=>	'89039080316',
					'skype'					=>	'vladimir',
					'icq'					=>	'9006788',
					'site'					=>	'www.vladimir.com',
					'city'					=>	'Ново',
					'city_list_item'		=>	'2'
				)
			),
			array('3',
				array(
					'type'					=>	'specJur',
					'image'					=>	true,
					'firstname'				=>	'Антон',
					'lastname'				=>	'Захаров',
					'email'					=>	'test_user_%d@mail.ru',
					'gender'				=>	'female',
					'b_day'					=>	'29',
					'b_month'				=>	'Июля',
					'b_year'				=>	'1978',
					'about'					=>	'Текст поля формы "О себе"',
					'phone'					=>	'8903',
					'skype'					=>	'anton',
					'icq'					=>	'333222565',
					'site'					=>	'www.anton.com',
					'city'					=>	'Ново',
					'city_list_item'		=>	'2'
				)
			),
		);
	}

	/**
	*	@dataProvider userData
	*/
	public function testUserProfile($iteration, $data)
	{
		$id = $this->timestamp();
		// Получаем пару логин/пароль и другие доп. параметры
		$users_data = file_get_contents('/var/www/myhome/protected/tests/functional/data/users.txt');
		$users = explode('\n', $users_data);
		array_splice($users, count($users) - 1);
		$user = explode(':', $users[$iteration - 1]);
		$tr = count($users) - ($iteration - 1); 
		// Стартуем, входим на сайт
		$this->startAction('/', array('login' => $user[0], 'password' => $user[1]));
		sleep(3);
		// Входим в "Мой профиль"
		$this->clickAndWait($this->getElement('profile'));
		// Жмем "Редактировать профиль"
		try {
			$this->assertTrue($this->isElementPresent($this->getElement('profile_edit')));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->clickAndWait($this->getElement('profile_edit'));
		// Изменяем персональные данные
		if ($data['image']) {
			// Фото
			$this->type($this->getElement('profile_image'), $this->data_path.'/avatar.jpg');
			$this->fireEvent($this->getElement('profile_image'), 'blur');
		}
		// Имя
		$this->type($this->getElement('profile_firstname'), $data['firstname']);
		// Фамилия
		if ($data['type'] != 'specJur') {
			$this->type($this->getElement('profile_lastname'), $data['lastname']);
		}
		// E-mail
		$this->type($this->getElement('profile_email'), $this->setContent($data['email'], $id));
		// Пол
		$this->click($this->getElement('profile_gender', $data['gender']));
		// Дата рождения
		$this->select($this->getElement('profile_bDay'), 'label='.$data['b_day']);
		$this->select($this->getElement('profile_bMonth'), 'label='.$data['b_month']);
		$this->select($this->getElement('profile_bYear'), 'label='.$data['b_year']);
		// О себе		
		if ($data['type'] == 'spec') {
			$this->type($this->getElement('profile_specAbout'), $data['about']);
		}
		// Выбираем город
		$this->type($this->getElement('profile_city'), $data['city']);
		$this->typeKeys($this->getElement('profile_city'), $data['city']);
		$this->fireEvent($this->getElement('profile_city'), 'keyup');
		sleep(1);
		$this->mouseOver($this->getElement('profile_cityListItem', '1'));
		$this->click($this->getElement('profile_cityListItem', '1'));
		// Телефон
		if ($data['type'] == 'user') {
			$this->type($this->getElement('profile_phone'), $data['phone']);
		}
		else {
			$this->type($this->getElement('profile_specPhone'), $data['phone']);
		}
		// Скайп
		$this->type($this->getElement('profile_skype'), $data['skype']);
		// ICQ
		$this->type($this->getElement('profile_icq'), $data['icq']);
		// Адрес сайта
		$this->type($this->getElement('profile_site'), $data['site']);
		// Сохраняем
		$this->clickAndWait($this->getElement('submit'));
		// Переходим во вкладку "Аккаунты в социальных сетях"
		$this->clickAndWait($this->getElement('profile_menuItem', '2'));
		// Привязываем аккаунты
		// TBD
		// Добавляем ссылку на профиль
		// Переходим во вкладку "Изменение пароля"
		$this->clickAndWait($this->getElement('profile_menuItem', '3'));
		// Меняем пароль
		$this->type($this->getElement('profile_oldPassword'), $user[1]);
		$this->type($this->getElement('profile_password'), $user[1]);
		$this->type($this->getElement('profile_password2'), $user[1]);
		// Сохраняем
		$this->clickAndWait($this->getElement('submit'));
		// Проверяем сообщение об успешном изменении пароля
		$this->assertEquals('Пароль успешно изменен', $this->getText($this->getElement('profile_passChangeBalloon')));
		// Переходим во вкладку "Настройка уведомлений"
		$this->clickAndWait($this->getElement('profile_menuItem', '4'));
		// Меняем настройки
		$i = 1;
		$b = $data['type'] == 'user' ? 3 : 5 ;
		while ($i <= $b) {
			$this->check($this->getElement('profile_settingsChk', $i));
			$i++;
		}
		// Сохраняем
		$this->clickAndWait($this->getElement('submit'));		
		// Меняем настройки
		while ($i <= $b) {
			$this->uncheck($this->getElement('profile_settingsChk', $i));
			$i++;
		}
		// Сохраняем
		$this->clickAndWait($this->getElement('submit'));		
		// Переходим на главную страницу профиля
		$this->clickAndWait($this->getElement('profile_back'));
		// Выходим
		$this->logout();
		// Авторизуемся под админом
		$this->authorize('logonarium', '1');
		// Заходим в ПА
		$this->clickAndWait($this->getElement('step_1'));
		// Заходим в "Пользователи сайта"
		$this->clickAndWait($this->getElement('step_2'));
		sleep(2);
		// Удаляем за собой
		$this->click($this->getElement('td', array($tr, '11', '/a[@class="delete"]')));
		sleep(4);
	}
}
?>
