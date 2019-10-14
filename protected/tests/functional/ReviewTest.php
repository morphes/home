<?php
class ReviewTest extends WebTestCase
{
	private $spec = 'default';

	protected function setUp()
	{
		parent::setUp();
		$this->setElements();
	}

	public function setElements()
	{
		return array_merge(parent::setElements(), array(
				'spec_link'			=>	'//div[@class="spec_right"]/div[@class="item"][%d]/a[@class="name"]',
				'review_tab'		=>	'link=Отзывы',
				'chk'				=>	'//label[@class="%s"]/input[@name="mark"]',
				'recommend'			=>	'css=span.recomended',
				'not_recommend'		=>	'css=span.recomended.disabled',
				'recommend_chk'		=>	'name=recomended',
				'message'			=>	'name=message',
				'submit'			=>	'css=input.btn_grey',
				'review_text'		=>	'css=div.item_text > p',
				'review_input'		=>	'css=textarea.textInput',
				'edit_btn'			=>	'link=Редактировать',
				'delete_btn'		=>	'link=Удалить',
				'submit_new'		=>	'css=input.btn_grey.submit_new_comment',
				'review_added'		=>	'css=span.review_added',
				'review_reply'		=>	'link=Ответить',
				'review_answer'		=>	'//div[@class="item_answer"]/div[2]/div/p',
			)
		);
	}

	/**
	*	Review data provider
	*/
	public function reviewData()
	{
		return array(
			array(
				'1', 
				array(
					'login' 	=>	'logonarium',
					'password'	=>	'1'
				), 
				array(
					'index'		=>	'1',
					'mark'		=>	'good',
					'recommend'	=>	true,
					'text'		=>	'Хороший отзыв о специалисте и рекомендация'
				)
			),
			array(
				'2',
				array(
					'login'		=>	'logonarium',
					'password'	=>	'1'
				),
				array(
					'index'		=>	'4',
					'mark'		=>	'bad',
					'recommend'	=>	false,
					'text'		=>	'Плохой отзыв о специалисте'
				)
			),
			/*
			array(
				'3',
				array(
					'login'		=>	'vegorov',
					'password'	=>	'1'
				),
				array(
					'index'		=>	'2',
					'mark'		=>	'good',
					'recommend'	=>	false,
					'text'		=>	'Хороший отзыв о специалисте'
				)
			)
			*/
		);
	}

	/**
	*	@dataProvider reviewData
	*/
	public function testReview($iteration, $user, $data)
	{
		// Стартуем, логинимся
		$this->startAction('/', array('login' => $user['login'], 'password' => $user['password']));
		// На главной кликаем по ссылке на профиль специалиста
		$this->clickAndWait($this->getElement('spec_link', $data['index']));
		// Сохраняем логин спеца из URL
		$this->specId = substr($this->getLocation(), 28);
		// Кликаем по табу "Отзывы"
		$this->clickAndWait($this->getElement('review_tab'));
		// Проверяем - привязан ли хоть один аккаунт соц. сети
		try {
			$this->assertEquals('Оставить отзыв', $this->getText($this->getElement('review_added')));
		}
		catch (Exception $e)
		{
			// Если выводится сообщение - значит не привязан
			$this->fail('Возможно, аккаунт пользователя не привязан ни к одной из социальных сетей');
		}
		// Если привязан
		// Чекаем радио "Плохо"
		$this->click($this->getElement('chk', 'bad'));
		// Проверяем задизейблился ли чекбокс "Рекомендую"
		try {
			$this->assertTrue($this->isElementPresent($this->getElement('not_recommend')));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		// Чекаем радио "Хорошо"
		$this->click($this->getElement('chk', 'good'));
		// Проверяем раздизейблился ли чек "Рекомендую"
		try {
			$this->assertTrue($this->isElementPresent($this->getElement('recommend')));
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
		$this->click($this->getElement('chk', $data['mark']));
		// Если отзыв хороший и $data['recommend'] == true, то еще и рекомендуем
		if ($data['recommend'] && $data['mark'] == 'good') {
			$this->click($this->getElement('recommend_chk'));
		}
		// Вводим текст отзыва
		$this->type($this->getElement('message'), $data['text']);
		sleep(2);
		// Сабмитим форму
		$this->click($this->getElement('submit'));
		// Ждем, пока сохранится отзыв (AJAX)
		for ($second = 0; ; $second++) {
			if ($second >= 2) {
				$this->fail('Review adding failed');
			}
			try {
				if ($this->isElementPresent($this->getElement('review_text'))) {
					// Проверяем, что отзыв добавился
					$this->assertEquals('Ваш отзыв добавлен!', $this->getText($this->getElement('review_added')));
					$this->assertEquals($data['text'], $this->getText($this->getElement('review_text')));
					break;
				}
			} catch (Exception $e) {}
			sleep(1);
		}
		// Жмем "Редактировать"
		$this->click($this->getElement('edit_btn'));
		// Меняем текст отзыва
		$this->type($this->getElement('review_input'), 'Измененный отзыв "'.$data['text'].'"');
		// Сабмитим новый отзыв
		$this->click($this->getElement('submit_new'));
		// Ждем, пока сохранится измененный отзыв
		for ($second = 0; ; $second++) {
			if ($second >= 2) {
				$this->fail('Review change failed');
			}
			try {
				if ($this->isElementPresent($this->getElement('review_text'))) {
					// Проверяем, что отзыв добавился
					$this->assertEquals('Ваш отзыв добавлен!', $this->getText($this->getElement('review_added')));
					// Проверяем, что отзыв изменился
					$this->assertEquals('Измененный отзыв "'.$data['text'].'"', $this->getText($this->getElement('review_text')));
					break;
				}
			} catch (Exception $e) {}
			sleep(1);
		}

		// Ответ на отзыв
		// Разлогиниваемся
		$this->clickAndWait($this->getElement('logout'));
		// Авторизуемся под тем, кому адресован отзыв
		$this->authorize($this->specId, '1');
		// Кликаем "Мой профиль"
		$this->clickAndWait($this->getElement('profile'));
		// Кликаем по табу "Отзывы"
		$this->clickAndWait($this->getElement('review_tab'));
		// Проверяем, есть ли отзыв и соответствует ли выводимый текст отзыв добавленному
		$this->assertEquals('Измененный отзыв "'.$data['text'].'"', $this->getText($this->getElement('review_text')));
		// Кликаем "Ответить"
		$this->click($this->getElement('review_reply'));
		// Заполняем форму
		$this->type($this->getElement('review_input'), 'Ответ на "Измененный отзыв '.$data['text'].'"');
		// Сабмитим
		$this->click($this->getElement('submit'));
		// Проверяем, что ответ добавился и он соответствует тому, что мы вводили
		$this->assertEquals('Ответ на "Измененный отзыв '.$data['text'].'"', $this->getText($this->getElement('review_answer')));
		// Кликаем "Редактировать"
		$this->click($this->getElement('edit_btn'));
		// Изменяем содержимое
		$this->type($this->getElement('review_input'), 'Измененный ответ на "Измененный отзыв '.$data['text'].'"');
		// Сабмитим
		$this->click($this->getElement('submit'));
		// Проверяем сохранилось ли измененное содержание ответа
		$this->assertEquals('Измененный ответ на "Измененный отзыв '.$data['text'].'"', $this->getText($this->getElement('review_answer')));
		// Удаляем ответ
		$this->click("link=Удалить");

		// Удаление отзыва
		// Разлогиниваемся
		$this->clickAndWait($this->getElement('logout'));
		// Авторизуемся как автор отзыва
		$this->authorize($user['login'], $user['password']);
		// Заходим на страницу спеца
		$this->open('/users/'.$this->specId);
		// Кликаем по табу "Отзывы"
		$this->clickAndWait($this->getElement('review_tab'));
		// Жмем "Удалить"
		$this->clickAndWait($this->getElement('delete_btn'));
		// Проверяем, что отзыв удален
		$this->assertEquals('Оставить отзыв', $this->getText($this->getElement('review_added')));
	}
}
?>