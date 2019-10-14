<?php
class RegistrationTest extends WebTestCase
{
	protected function setUp(){
		parent::setUp();
		$this->setElements();
	}
	
	public function setElements(){
		return array_merge(parent::setElements(), array(
				'user_login'				=>	'id=User_login',
				'user_pass'					=>	'id=User_password',
				'user_passConfirm'			=>	'id=User_password2',
				'user_email'				=>	'id=User_email',
				'user_roleSelect'			=>	'id=User_role',
				'user_roleLabelUser'		=>	'label=Владелец квартиры',
				'user_roleLabelSpec'		=>	'label=Специалист (физ. лицо)',
				'user_roleLabelSpecJur'		=>	'label=Специалист (юр. лицо)',
				'user_firstname'			=>	'id=User_firstname',
				'user_lastname'				=>	'id=User_lastname',
				'user_city'					=>	'id=User_city_id',
				'user_cityListItem'			=>	'//body/ul[@role="listbox"]/li[%d]/a',
				'user_phone'				=>	'id=User_phone',
				'user_contactPerson'		=>	'id=UserData_contact_face',
				'user_agreement'			=>	'id=User_agreement',
				'error_message'				=>	'css=div.error-message',
				'submit'					=>	'css=button.button3',
				'step_1'					=>	'link=Центр управления',
				'step_2'					=>	'link=Пользователи сайта',
				'td'						=>	'//div[@id="user-grid"]/table/tbody/tr/td[%d]%s',
				'admin_submit'				=>	'name=yt1',
				'status_select'				=>	'id=User_status'
			)
		);
	}

	public function registrationDataSet()
	{
		return 
			array(
				// Нормальное течение теста
				// Регистрируем владельца квартиры
				array('1',	'test_user_%d',	'123456',	'123456',	'test_user_%d@mail.ru',	'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'', 				true, ''),
				// Регистрируем специалиста
				array('2',	'test_spec_%d',	'123456',	'123456',	'test_spec_%d@mail.ru',	'Spec',		'TestSpec_%d',	'TestSpec_%d Lastname',	'Новосибирс',	'+79039993429',	'', 				true, ''),
				// Регистрируем организацию
				array('3',	'test_spec_%d',	'123456',	'123456',	'test_spec_%d@mail.ru',	'SpecJur',	'TestSpec_%d',	'TestSpec_%d Lastname',	'Новосибирск',	'+79039993429',	'Contact person',	true, ''),
				// Негативное прохождение теста
				// Пытаемся зарегистрировать владельца 
				// с существующим логином
				// array('4',	'logonarium',	'123456',	'123456',	'logonarium_%d@mail.ru',	'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Этот логин уже занят'),
				// с недопустимым логином
				// array('5',	'дщпщтфкшгь',	'123456',	'123456',	'logonarium_%d@mail.ru',	'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Можно использовать только латинские буквы и цифры'),
				// с неправильным e-mail
				// array('6',	'test_user_%d',	'123456',	'123456',	'logonarium_%d',			'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Некорректный формат адреса электронной почты'),
				// с существующим e-mail
				// array('7',	'test_user_%d',	'123456',	'123456',	'mxgl@mail.ru',				'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Адрес электронной почты уже занят'),
				/*
				// без пароля
				array('8',	'test_user_%d',	'',			'',			'logonarium_%d@mail.ru',	'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Необходимо заполнить поле Пароль.'),			
				// с несовпадающими паролями
				array('9',	'test_user_%d',	'123456',	'12345',	'logonarium_%d@mail.ru',	'User',		'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Пароли не совпадают'),
				// без выбора роли
				array('10',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'',			'TestUser_%d',	'TestUser_%d Lastname',	'',				'',				'',					true, 'Выберите тип пользователя'),			
				// без имени
				array('11',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'User',		'',				'TestUser_%d Lastname',	'',				'',				'',					true, 'Необходимо заполнить поле Имя.'),
				// без фамилии
				array('11',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'User',		'TestUser_%d',	'',						'',				'',				'',					true, 'Необходимо заполнить поле Фамилия.'),
				// без подтверждения соглашения
				array('12',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'User',		'TestUser_%d',	'',						'',				'',				'',					false, 'Необходимо подтвердить ваше согласие с правилами использования сервиса myhome.ru'),
				// Пытаемся зарегистрировать специалиста
				// без города
				array('14',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'Spec',		'TestUser_%d',	'',						'',				'+79039993429',	'',					true, 'Выберите город из списка'),
				// без телефона
				array('15',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'Spec',		'TestUser_%d',	'',						'Новосибирск',	'',				'',					true, 'Необходимо заполнить поле Телефон.'),
				// Пытаемся зарегистрировать организацию
				// без названия
				array('17',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'SpecJur',	'',				'',						'Новосибирск',	'+79039993429', 'Contact person',	true, 'Необходимо заполнить поле Имя.'),
				// без контактного лица
				array('20',	'test_user_%d',	'123456',	'123456',	'logonarium_%d@mail.ru',	'SpecJur',	'TestUser_%d',	'',						'Новосибирск',	'+79039993429', 'Contact person',	false, 'Необходимо заполнить поле Контактное лицо.')	
				*/
			);
	}

	/**
	*	@dataProvider registrationDataSet
	*/
	public function testUserRegistration($iteration, $login, $pass, $pass_confirm, $email, $type, $firstname, $lastname, $city, $phone, $contact_person, $agreement, $errormessage)
	{
		$id = $this->timestamp();
		// Стартуем
		$this->startAction('/');
		// Жмем "Зарегистрироваться"
		$this->clickAndWait($this->getElement('register'));
		// Переходим на страницу регистрации
		// Проверяем наличие заголовка
		$this->verifyText('css=h1', 'Регистрация');
		// Сохраняем пару логин/пароль для последующих тестов
		if (!$errormessage) {
			$content = $this->setContent($login, $id).':'.$pass;
			file_put_contents('/var/www/myhome/protected/tests/functional/data/users.txt', $content.'\n', FILE_APPEND);
		}
		// Указываем логин
		$this->type($this->getElement('user_login'), $this->setContent($login, $id));
		// Указываем пароль
		$this->type($this->getElement('user_pass'), $pass);
		// Указываем подтверждение пароля
		$this->type($this->getElement('user_passConfirm'), $pass_confirm);
		// Указываем e-mail
		$this->type($this->getElement('user_email'), $this->setContent($email, $id));
		// Выбираем роль "Владелец квартиры"
		if ($type) {
			$this->select($this->getElement('user_roleSelect'), $this->getElement('user_roleLabel'.$type));
		}
		// В зависимости от роли пользователя
		// заполняем соответствующие поля
		switch ($type) {
			case 'User':
					$this->type($this->getElement('user_firstname'), $this->setContent($firstname, $iteration));
					$this->type($this->getElement('user_lastname'), $this->setContent($lastname, $iteration));
				break;
			case 'Spec':
					$this->type($this->getElement('user_firstname'), $this->setContent($firstname, $iteration));
					$this->type($this->getElement('user_lastname'), $this->setContent($lastname, $iteration));
					
					$this->type($this->getElement('user_city'), $city);
					$this->typeKeys($this->getElement('user_city'), $city);
					$this->fireEvent($this->getElement('user_city'), 'keyup');
					sleep(1);
					$this->mouseOver($this->getElement('user_cityListItem', '1'));
					$this->click($this->getElement('user_cityListItem', '1'));

					$this->type($this->getElement('user_phone'), $phone);
				break;
			case 'SpecJur':
					$this->type($this->getElement('user_firstname'), $this->setContent($firstname, $iteration).' '.$this->setContent($lastname, $iteration));
					
					$this->type($this->getElement('user_city'), $city);
					$this->typeKeys($this->getElement('user_city'), $city);
					$this->fireEvent($this->getElement('user_city'), 'keyup');
					sleep(1);
					$this->mouseOver($this->getElement('user_cityListItem', '1'));
					$this->click($this->getElement('user_cityListItem', '1'));

					$this->type($this->getElement('user_phone'), $phone);
					$this->type($this->getElement('user_contactPerson'), $contact_person);
				break;
		}
		// Чекаем соглашение об использовании
		if ($agreement) {
			$this->check($this->getElement('user_agreement'));
		}

		if ($errormessage) {
			$this->click($this->getElement('submit'));
			if ($this->assertEquals($errormessage, $this->getText($this->getElement('error_message')))) {
				return true;
			}
		}
		else {
			// Сохраняем форму
			$this->clickAndWait($this->getElement('submit'));
			// Проверяем, что вышло сообщение об успешном добавлении
			$this->assertTrue($this->isTextPresent('До завершения регистрации остался один шаг!'));
			// Авторизуемся под админом
			$this->authorize('logonarium', '1');
			// Заходим в ПА
			$this->clickAndWait($this->getElement('step_1'));
			// Заходим в "Пользователи сайта"
			$this->clickAndWait($this->getElement('step_2'));
			sleep(2);
			// Проверяем, что в первой строке таблицы - данные добавленного пользователя
			$this->assertEquals($this->setContent($login, $id), $this->getText($this->getElement('td', array('3', '/a'))));
			$this->assertEquals($this->setContent($firstname, $iteration).' '.$this->setContent($lastname, $iteration), $this->getText($this->getElement('td', array('4', ''))));
			$this->assertEquals('На подтверждении', $this->getText($this->getElement('td', array('6', '/span'))));
			$this->assertEquals($this->setContent($email, $id), $this->getText($this->getElement('td', array('7', '/a'))));
			// Активируем юзера, чтобы была возможность работать с ним в тестах
			// Заходим в редактирование профиля
			$this->clickAndWait($this->getElement('td', array('11', '/a[@class="update"]')));
			// Ставим статус "Активен"
			$this->select($this->getElement('status_select'), 'label=Активен');
			// Сохраняем
			$this->clickAndWait($this->getElement('admin_submit'));
			// Удаляем юзера за собой в классе UserProfileTest
		}
	}
}
?>