<?php
class PortfolioTest extends WebTestCase
{
	protected function setUp(){
		parent::setUp();
		$this->setElements();
	}

	public function setElements()
	{
		return array_merge(parent::setElements(), array(
				'add_new_project'					=>	'css=span.btn_grey.service_choice',
				'new_project_item'					=>	'//div[@class="servise_choice_list"]/ul/li[%d]/a',
				'type_selector'						=>	'//div[@class="build_type drop_down"]/span',
				'type_list_item'					=>	'//div[@class="build_type drop_down"]/ul/li[%d]',
				'type_input'						=>	'id=build_type',
				'project_type_selector'				=>	'//div[@class="build_type drop_down"]/ul',
				'title'								=>	'id=%sname',
				'desc'								=>	'id=%sdesc',
				'cover'								=>	'css=.img_input.cover',
				'coauthor_toggle'					=>	'//div[@class="add_coautor_link"]/a',
				'coauthor_name'						=>	'//div[@class="input_row project_add_coautor"]/div[@class="add_coautor"][%d]/input',
				'coauthor_role'						=>	'//div[@class="input_row project_add_coautor"]/div[@class="add_coautor"][%d]/div[@class="coautor_role"]/input',
				'coauthor_site'						=>	'//div[@class="input_row project_add_coautor"]/div[@class="add_coautor"][%d]/div[@class="coautor_site"]/input',
				'plan'								=>	'//div[@class="image_to_upload"]/div/div/div/input[@class="img_input"]',
				'plan_to_upload'					=>	'//div[@class="image_uploaded"]/div[%d]',
				'plan_desc'							=>	'//div[@class="image_uploaded"]/div[%d]/div/div/textarea',
				'room_selector'						=>	'//div[@class="build_type drop_down  room_selector"]/span',
				'room_list_item'					=>	'//div[@class="build_type drop_down  room_selector"]/ul/li[%u]',
				'room_wrapper'						=>	'//div[@class="project_rooms"][%d]',
				'room_style_toggle'					=>	'//div[@class="project_rooms"][%d]/div[2]/div[1]/div/div/span',
				'room_style_item'					=>	'//div[@class="project_rooms"][%d]/div[2]/div[1]/div/div/ul/li[%d]',
				'room_color_toggle'					=>	'//div[@class="project_rooms"][%d]/div[3]/div/div[1]/div/span',
				'room_color_item'					=>	'//div[@class="project_rooms"][%d]/div[3]/div/div[1]/div/ul/li[%d]',
				'room_color1_toggle'				=>	'//div[@class="project_rooms"][%d]/div[3]/div/div[2]/div/span',
				'room_color1_item'					=>	'//div[@class="project_rooms"][%d]/div[3]/div/div[2]/div/ul/li[%d]',
				'room_color2_toggle'				=>	'//div[@class="project_rooms"][%d]/div[3]/div/div[3]/div/span',
				'room_color2_item'					=>	'//div[@class="project_rooms"][%d]/div[3]/div/div[3]/div/ul/li[%d]',
				'room_image'						=>	'//div[@class="project_rooms"][%d]/div[@class="image_to_upload"]/div/div/div/input',
				'room_image_wrapper'				=>	'//div[@class="project_rooms"][%d]/div[@class="image_uploaded"]/div[%d]',
				'room_image_desc'					=>	'//div[@class="project_rooms"][%d]/div[@class="image_uploaded"]/div[%d]/div/div/textarea',

				'interior_style_toggle'				=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[1]/div/div/span',
				'interior_style_item'				=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[1]/div/div/ul/li[%d]',
				'interior_color_toggle'				=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[2]/div/div[1]/div/span',
				'interior_color_item'				=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[2]/div/div[1]/div/ul/li[%d]',
				'interior_color1_toggle'			=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[2]/div/div[2]/div/span',
				'interior_color1_item'				=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[2]/div/div[2]/div/ul/li[%d]',
				'interior_color2_toggle'			=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[2]/div/div[3]/div/span',
				'interior_color2_item'				=>	'//div[@class="shadow_block padding-18 project_add"][2]/div[2]/div/div[3]/div/ul/li[%d]',

				'submit'							=> 	'id=architecture-submit',
				'draft'								=> 	'link=Сохранить и продолжить позже',
				'delete'							=> 	'link=Удалить',
				'drafts'							=>	'link=Черновики',
				'list_project_name'					=>	'css=a.item_autor'

			)
		);
	}

	public function privateProjectDataSet()
	{
		return 
			array(
				// Проект #1
				/*
				array('1', '1', 'Проект 1', 'Описание проекта 1', 'cover.jpg', 
					array(
						array('name' => 'ФИО соавтора 1', 'role' => 'роль соавтора в проекте 1', 'site' => ''),
						array('name' => 'ФИО соавтора 2', 'role' => 'роль соавтора в проекте 2', 'site' => '')
					), 
					'2',
					array(
						array('2',	'2',	'3',	'1',	'2',	array('count' => '5', 'desc' => 'Описание фото %d помещения %d')),
						array('3',	'2',	'4',	'2',	'1',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('3',	'3',	'1',	'3',	'4',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('4',	'4',	'2',	'4',	'3',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('5',	'1',	'7',	'5',	'6',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('6',	'1',	'8',	'6',	'5',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('7',	'1',	'5',	'7',	'8',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('8',	'1',	'6',	'8',	'7',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('9',	'1',	'11',	'9',	'10',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('10',	'1',	'12',	'10',	'9',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('11',	'1',	'9',	'11',	'12',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						// array('1',	'1',	'10',	'12',	'11',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d'))
					)
				),
				*/
				// Проект #2
				array('2', '2', 'Проект 2', 'Описание проекта 2', 'cover.jpg', 
					array(
						array('name' => 'ФИО соавтора 1', 'role' => 'роль соавтора в проекте 1', 'site' => ''),
						array('name' => 'ФИО соавтора 2', 'role' => 'роль соавтора в проекте 2', 'site' => '')
					), 
					'2', 
					array('2', '2', '3', '4', array('count' => '3', 'desc' => 'Описание фото %d помещения %d'))
				),
/*
				// Проект #3
				array('3', '1', 'Проект 3', 'Описание проекта 3', 'cover.jpg', 
					array(
						array('name' => 'ФИО соавтора', 'role' => 'роль соавтора в проекте', 'site' => 'http://www.yandex.ru')
					), 
					'1', 
					array(
						array('2',	'3',	'2',	'1',	'4',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('1',	'3',	'1',	'2',	'3',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('4',	'3',	'4',	'3',	'2',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('3',	'3',	'3',	'4',	'1',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('10',	'3',	'6',	'5',	'8',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('9',	'3',	'5',	'6',	'7',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('11',	'3',	'8',	'7',	'6',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('2',	'3',	'7',	'8',	'5',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('5',	'3',	'10',	'9',	'12',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('6',	'3',	'9',	'10',	'11',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('10',	'2',	'13',	'12',	'10',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d'))
					)
				),
				// Проект #4
				array('4', '1', 'Проект 4', 'Описание проекта 4', 'cover.jpg', 
					array(), 
					'2', 
					array(
						array('7',	'3',	'12',	'11',	'10',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('8',	'3',	'11',	'12',	'9',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('2',	'3',	'5',	'13',	'3',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('4',	'4',	'9',	'1',	'5',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('3',	'4',	'5',	'2',	'6',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('1',	'4',	'8',	'3',	'7',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('6',	'4',	'10',	'4',	'8',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('7',	'4',	'11',	'5',	'1',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('5',	'4',	'13',	'6',	'2',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('8',	'4',	'12',	'7',	'3',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('9',	'4',	'1',	'8',	'4',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d')),
						array('11',	'2',	'12',	'13',	'2',	array('count' => '3', 'desc' => 'Описание фото %d помещения %d'))
					)
				)
*/
			);
	}

/*
	public $uiElements = array(
		// private
		array(
			array(
				'title' => 'Общая информация',
				'fields' => array(
					array(
						'label' => 'Тип объекта',
						'locator' => 'css',
						'identifier' => '.exp_current'
					),
					array(
						'label' => 'Название',
						'locator' => 'id',
						'identifier' => 'Interior_name'
					),
					array(
						'label' => 'Описание',
						'locator' => 'id',
						'identifier' => 'Interior_desc'
					),
					array(
						'label' => 'Обложка идеи',
						'locator' => 'css',
						'identifier' => '.img_input.cover'
					),
					array(
						'locator' => 'css',
						'identifier' => '.textInput.img_input_text'
					),
					array(
						'label' => 'Добавить соавтора проекта',
						'locator' => 'css',
						'identifier' => '.add_coautor_link'
					)
				)
			),
			array(
				'title' => 'Планировки',
				'fields' => array(
					array(
						'locator' => 'css',
						'identifier' => '.img_input'
					)
				)			
			),
			array(
				'title' => 'Помещения (стили, цвета, изображения)',
				'fields' => array(
					array(
						'label' => 'Чтобы добавить помещение, выберите его из списка',
						'locator' => 'name',
						'identifier' => 'room'
					),
					array(
						'locator' => 'css',
						'identifier' => '.build_type.drop_down.room_selector'
					)
				)
			),
			array(
				'title' => 'Добавить архитектуру этого объекта (если есть)',
				'fields' => array(
					array(
						'label' => 'Выбрать из моего портфолио',
						'locator' => 'id',
						'identifier' => 'Interior_architecture_id'
					)
				)
			)
		),
		// public
		array(
			array(
				'title' => 'Общая информация',
				'fields' => array(
					array(
						'label' => 'Тип строения',
						'locator' => 'css',
						'identifier' => '.exp_current'
					),
					array(
						'label' => 'Название',
						'locator' => 'id',
						'identifier' => 'Interiorpublic_name'
					),
					array(
						'label' => 'Описание',
						'locator' => 'id',
						'identifier' => 'Interiorpublic_desc'
					)					
				)
			),
			array(
				'title' => 'Характеристики',
				'fields' => array(
					array(
						'label' => 'Стиль объекта',
						'identifier' => '//div[@class="build_type drop_down"][2]'
					),
					array(
						'label' => '',
						'locator' => '',
						'identifier' => ''
					),
					array(
						'label' => '',
						'locator' => '',
						'identifier' => ''
					)
				)
			),
			array(
				'title' => 'Изображения',
				'fields' => array(
					array(
						'label' => '',
						'locator' => '',
						'identifier' => ''
					)
				)
			),
			array(
				'title' => 'Добавить архитектуру этого объекта (если есть)',
				'fields' => array(
					array(
						'label' => '',
						'locator' => '',
						'identifier' => ''
					)
				)
			)
		)
	);

	public function testIndex()
	{
		// Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
		$this->startAction('/', array('login' => 'zotov', 'password' => '1'));
		// Заходим в портфолио
		$this->clickAndWait($this->getElement('portfolio'));
		// Кликаем "Добавить новый проект"
		$this->click($this->getElement('add_new_project'));
		// Кликаем по первому пункту в дропдауне
		$this->clickAndWait('//div[@class="servise_choice_list"]/ul/li/a');
		// Кликаем по селекту типа проекта
		$this->click('//div[@class="build_type drop_down"]/span');
		// Ждем раскрытия селекта типа проекта
		for ($second = 0; ; $second++) {
			if ($second >= 2) {
				$this->fail('timeout');
			}
			try {
				if ($this->isElementPresent('//div[@class="build_type drop_down"]/ul')) {
					break;
				}
			} catch (Exception $e) {}
			sleep(1);
		}
		$i = 1; $n = 0;
		while ($i <= 2) {
			// Кликаем по пункту в селекте
			$this->click('//div[@class="build_type drop_down"]/ul/li['.$i.']');
			// Отлавливаем алерт
			if ($this->isElementPresent($this->getElement('confirm'))) {
				// Если алерт есть, то жмем "Да"
				$this->clickAndWait($this->getElement('confirm_accept'));
			}
			// Ждем перезагрузки
			$this->waitForPageToLoad('4000');

			foreach($this->uiElements[$n] as $a) {
				// Проверяем заголовки блоков в форме
				$this->verifyTextPresent($a['title']);
				// Проверяем поля в каждом блоке
				if (count($a['fields'])) {
					foreach($a['fields'] as $b) {
						// Если label не пустой,
						if (array_key_exists('label', $b)) {
							// то проверяем есть ли он на странице
							$this->verifyTextPresent($b['label']);
						}
						try {
							// Проверяем наличие поля
							$this->assertTrue($this->isElementPresent((array_key_exists('locator', $b) ? $b['locator'].'=' : '').$b['identifier']));
						} catch (PHPUnit_Framework_AssertionFailedError $e) {
							array_push($this->verificationErrors, $e->toString());
						}
					}
				}
			}
			$i++; $n++;
		}
		// Проверили наличие элементов на странице
	}
*/

	/**
	*	@dataProvider privateProjectDataSet
	*	Тестируем нормальное добавление проекта в портфолио
	*/
	public function testPrivateProject($iteration, $division, $title, $desc, $cover, $coauthor, $plan, $rooms)
	{
		switch ($division) {
			case 1:
				$prefix = 'Interior_';
				break;
			case 2:
				$prefix = 'Interiorpublic_';
				break;
			default:
				$prefix = '';
				break;
		}
		// Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
		$this->startAction('/', array('login' => 'zotov', 'password' => '1'));
		sleep(2);
		// Заходим в портфолио
		$this->clickAndWait($this->getElement('portfolio'));
		// Кликаем "Добавить новый проект"
		$this->click($this->getElement('add_new_project'));
		// Кликаем по первому пункту в дропдауне
		$this->clickAndWait($this->getElement('new_project_item', '1'));
		// Кликаем по селекту типа проекта
		$this->click($this->getElement('type_selector'));
		// Ждем раскрытия селекта типа проекта
		for ($second = 0; ; $second++) {
			if ($second >= 2) {
				$this->fail('Project type selector appearance timeout');
			}
			try {
				if ($this->isElementPresent($this->getElement('project_type_selector'))) {
					break;
				}
			} catch (Exception $e) {}
			sleep(1);
		}
		// Кликаем по пункту в селекте
		$this->click($this->getElement('type_list_item', $division));
		// Ждем перезагрузки
		$this->waitForPageToLoad('4000');
		// Заполняем поля тестовыми данными
		// Название проекта
		$this->type($this->getElement('title', $prefix), $title);
		// Описание проекта
		$this->type($this->getElement('desc', $prefix), $desc);
		// Обложка проекта
		$this->type($this->getElement('cover'), $this->data_path.$iteration.'/cover.jpg');
		$this->fireEvent($this->getElement('cover'), 'blur');
		
		// Соавторы проекта
		$i = 1;
		foreach($coauthor as $a) {
			// Кликаем "Добавить соавтора"
			$this->click($this->getElement('coauthor_toggle'));
			// Ждем появления полей
			for ($second = 0; ; $second++) {
				if ($second >= 4) {
					$this->fail('Coauthor_name appearance timeout');
				}
				try {
					if ($this->isElementPresent($this->getElement('coauthor_name', $i))) {
						break;
					}
				} catch (Exception $e) {}
				sleep(1);
			}
			$this->type($this->getElement('coauthor_name', $i), $a['name']);
			$this->type($this->getElement('coauthor_role', $i), $a['role']);
			$this->type($this->getElement('coauthor_site', $i), $a['site']);
			$i++;
		}
		
		if ($division == 1) {
			// Загружаем планировки
			$i = 1;
			while ($i <= $plan) {
				$this->type($this->getElement('plan'), $this->data_path.$iteration.'/jpg_'.$i.'.jpg');
				$this->fireEvent($this->getElement('plan'), 'blur');
				// Ждем появления превью
				for ($second = 0; ; $second++) {
					if ($second >= 4) {
						$this->fail('Planning appearance timeout');
					}
					try {
						if ($this->isElementPresent($this->getElement('plan_to_upload', $i))) {
							break;
						}
					} catch (Exception $e) {}
					sleep(1);
				}
				$this->type($this->getElement('plan_desc', $i), 'Описание планировки '.$i);
				$i++;
			}
		
			// Помещения
			$i = 1;
			foreach ($rooms as $r) {
				// Инициируем выбор типа помещения
				$this->click($this->getElement('room_selector'));
				// Выбираем тип помещения
				$this->click($this->getElement('room_list_item', $r[0]));
				// Ждем появления полей
				for ($second = 0; ; $second++) {
					if ($second >= 4) {
						$this->fail('Room appearance timeout');
					}
					try {
						if ($this->isElementPresent($this->getElement('room_wrapper', $i))) {
							break;
						}
					} catch (Exception $e) {}
					sleep(1);
				}
				// Выбираем стиль помещения
				$this->click($this->getElement('room_style_toggle', $i));
				$this->click($this->getElement('room_style_item', array($i, $r[1])));
				// Выбираем основной цвет
				$this->click($this->getElement('room_color_toggle', $i));
				$this->click($this->getElement('room_color_item', array($i, $r[2])));
				// Выбираем дополнительный цвет
				$this->click($this->getElement('room_color1_toggle', $i));
				$this->click($this->getElement('room_color1_item', array($i, $r[3])));
				// Выбираем дополнительный цвет
				$this->click($this->getElement('room_color2_toggle', $i));
				$this->click($this->getElement('room_color2_item', array($i, $r[4])));

				// Загружаем фотографии помещения
				$n = 1;
				while ($n <= $r[5]['count']) {
					// Загружаем фотку
					$this->type($this->getElement('room_image', $i), sprintf($this->data_path.'%d/jpg_%d.jpg', $i, $n));
					$this->fireEvent($this->getElement('room_image', $i), 'blur');
					// Ждем появления превью
					for ($second = 0; ; $second++) {
						if ($second >= 10) {
							$this->fail('Preview appearance timeout');
						}
						try {
							if ($this->isElementPresent($this->getElement('room_image_wrapper', array($i, $n)))) {
								break;
							}
						} catch (Exception $e) {}
						sleep(1);
					}
					// Добавляем описание к фото
					$this->type($this->getElement('room_image_desc', array($i, $n)), sprintf($r[5]['desc'], $n, $i));
					$n++;
				}
				$i++;
			}
		}
		if ($division == 2) {
			// Выбираем стиль помещения
			$this->click($this->getElement('interior_style_toggle'));
			$this->click($this->getElement('interior_style_item', $rooms[0]));
			// Выбираем основной цвет
			$this->click($this->getElement('interior_color_toggle'));
			$this->click($this->getElement('interior_color_item', $rooms[1]));
			// Выбираем дополнительный цвет
			$this->click($this->getElement('interior_color1_toggle'));
			$this->click($this->getElement('interior_color1_item', $rooms[2]));
			// Выбираем дополнительный цвет
			$this->click($this->getElement('interior_color2_toggle'));
			$this->click($this->getElement('interior_color2_item', $rooms[3]));
			// Загружаем изображения
		}
		/*
		// Жмем "Сохранить и продолжить позже"
		$this->clickAndWait($this->getElement('draft'));
		// Переходим в "Черновики"
		$this->clickAndWait($this->getElement('drafts'));
		// Проверяем - сохранился ли проект
		$this->assertEquals("Дизайн интерьера", $this->getText($this->getElement('list_project_name')));
		*/
		sleep(10);
	}
}

