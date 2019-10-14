<?php
class ForumTest extends WebTestCase
{

    private $uri = '/forum/';

    protected function setUp(){
        parent::setUp();
        $this->setElements();
    }

    public function setElements(){
        return array_merge(parent::setElements(), array(
                'forum_createNewTopic'			=>	'//div[@class="forum_new_topic"]/div/a',
                'forum_guestHint'				=>	'//div[@class="guest_hint"]',
                'forum_toggleSelectSubdivision'	=>	'//div[@class="forum_topic_add_form"]/form/div[2]/div/span',
                'forum_selectSubdivision'		=>	'//div[@class="forum_topic_add_form"]/form/div[2]/div/ul/li[%d]',
                'forum_topicName'				=>	'id=ForumTopic_name',
                'forum_topicDesc'				=>	'id=ForumTopic_description',
                'forum_topicFile'				=>	'id=ForumTopic_files',
                'forum_topicAnswer'				=>	'id=ForumAnswer_answer',
                'forum_topicTitle'				=>	'css=div.forum_head > h1',
                'forum_formSubmitTopic'			=>	'css=input.btn_grey.add_topic',
                'forum_errorSummary'			=>	'css=div.errorSummary',
                'forum_subListItem'				=>	'//div[@id="left_side"]/div/ul/li[%d]/a',
                'forum_answerEdit'				=>	'css=i.edit',
                'forum_answerField'				=>	'name=ForumAnswer[answer]',
                'forum_ratingSpan'				=>	'css=div.likes > span',
                'forum_ratingSpanGood'			=>	'css=div.likes > span.good',
                'forum_ratingI'					=>	'css=div.likes > i',
                'forum_myTopics'				=>	'link=Мои темы',
                'forum_myAnswers'				=>	'link=Мои ответы',
                'forum_delete'					=>	'css=i.delete',
                'forum_deleteMessage'			=>	'css=div.deleted_message',
                'forum_searchField'				=>	'//div[@class="forum_search"]/form/input[1]',
                'forum_searchSubmit'			=>	'//div[@class="forum_search"]/form/input[2]',
                'forum_searchWordSpan'			=>	'css=span.search_word',
                'forum_searchResult'			=>	'css=span.search_result',
                'forum_fileAPIListItem'			=>	'//div[@id="fileslist"]/div/div[%d]/span',
                'forum_mainTopicsListItem'		=>	'//div[@class="main_topics_list_container"]/div[2]/div[@class="item"][1]/a'

            )
        );
    }

    public function topicsDataSet()
    {
        return array(
            array('1','Тестовая тема 1','Тестовое описание 1','jpg.jpg',''),
            array('15','А','a','jpg.jpg',''),
            array('2','Информационная связь с потребителем индуцирует рекламный клаттер, осознав маркетинг как часть производства. Осведомленность о бренде индуцирует культурный 99-й и селлинг, размещаясь не во всех медиа. Как предсказывают футурологи служба маркетинга компании','тестовое описание','jpg.jpg',''),
            array('6','','Пытаемся создать тему без названия темы','','Необходимо заполнить поле Название'),
            array('4','Пытаемся создать тему без описания','','','Необходимо заполнить поле Описание темы'),
            array('','Тестовая тема без раздела','Добавляем тему не указав раздел форума','','Необходимо заполнить поле Раздел'),
        );
    }

    public function filesDataSet(){
        return array(
            array('jpg', true, ''),
            array('png', true, ''),
            array('zip', true, ''),
            array('doc', false, 'Данный тип файла запрещен к загрузке'),
            array('gif', false, 'Данный тип файла запрещен к загрузке'),
            array('pdf', false, 'Данный тип файла запрещен к загрузке'),
            array('xls', false, 'Данный тип файла запрещен к загрузке'),
            array('docx', false, 'Данный тип файла запрещен к загрузке'),
            array('xlsx', false, 'Данный тип файла запрещен к загрузке')
        );
    }

    /**
     * @dataProvider topicsDataSet
     */
    public function testTopicCreation($division, $title, $text, $file, $error_message)
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);
        // Создаем новую тему
        $this->clickAndWait($this->getElement('forum_createNewTopic'));
        // Если не авторизованы - выходит предупреждение
        if ($this->isElementPresent($this->getElement('forum_guestHint')))
        {
            // Авторизуемся
            $this->authorize('logonarium', '1');
        }

        if ($division)
        {
            // Выбираем раздел
            $this->click($this->getElement('forum_toggleSelectSubdivision'));
            $this->click($this->getElement('forum_selectSubdivision', $division));
        }

        // Заполняем тему
        $this->type($this->getElement('forum_topicName'), $title);
        // Заполняем описание
        $this->type($this->getElement('forum_topicDesc'), $text);

        // Сабмитим форму
        $this->clickAndWait($this->getElement('forum_formSubmitTopic'));

        if ($error_message)
        {
            // Ловим ошибку
            $this->waitForElementPresent($this->getElement('forum_errorSummary'));
            // Проверяем текст ошибки
            $this->verifyTextPresent($error_message);
        }
        else
        {
            // Проверяем - добавилось ли
            $this->verifyText($this->getElement('forum_topicTitle'), $title);
        }
    }

    /**
    *   @dataProvider topicsDataSet
    */
    public function testPost($division, $title, $text, $file, $error_message)
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);

        if (!$error_message) {
            // Сохраняем переменную userAnswer
            $this->store('Ответ пользователя в теме "'.$title.'"', 'userAnswer');
            // Заходим в раздел форума
            $this->clickAndWait($this->getElement('forum_subListItem', $division));
            // Заходим в тему форума
            $this->clickAndWait('link='.$title);
            // Если не авторизованы - выходит предупреждение
            if ($this->isElementPresent($this->getElement('forum_guestHint')))
            {
                // Авторизуемся
                $this->authorize('logonarium', '1');
            }
            // Заполняем поле ответа
            $this->type($this->getElement('forum_topicAnswer'), '${userAnswer}');
      		// Сабмитим форму
      		$this->clickAndWait($this->getElement('forum_formSubmitTopic'));
            // Проверяем наличие ответа
            $this->assertTextPresent('${userAnswer}');
        }
    }

    /**
    *   @dataProvider topicsDataSet
    */
    public function testPostEditing($division, $title, $text, $file, $error_message)
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);

        if (!$error_message) {
            // Формируем набор данных
            $data = array(
                'answer'    =>  'Ответ пользователя в теме "'.$title.'"',
                'changed'   =>  'ИСПРАВЛЕННЫЙ ответ пользователя в теме "'.$title.'"'
            );
            // Заходим в раздел форума
            $this->clickAndWait($this->getElement('forum_subListItem', $division));
            // Заходим в тему форума
            $this->clickAndWait('link='.$title);
            // Если не авторизованы - выходит предупреждение
            if ($this->isElementPresent($this->getElement('forum_guestHint')))
            {
                // Авторизуемся
                $this->authorize('logonarium', '1');
            }
            // Кликаем на кнопку редактирования ответа
            $this->click($this->getElement('forum_answerEdit'));
            // Дожидаемся появления поля
            for ($second = 0; ; $second++) {
                if ($second >= 5) {
                    $this->fail('timeout');
                }
                try {
                    if ($this->isElementPresent($this->getElement('forum_answerField'))) {
                        break;
                    }
                } 
                catch (Exception $e) {}
                sleep(1);
            }

            try {
                $this->assertEquals($data['answer'], $this->getValue($this->getElement('forum_answerField')));
            } 
            catch (PHPUnit_Framework_AssertionFailedError $e) {
                array_push($this->verificationErrors, $e->toString());
            }
            // Заполняем поле
            $this->type($this->getElement('forum_answerField'), $data['changed']);
            // Сабмитим форму
            $this->clickAndWait($this->getElement('forum_formSubmitTopic'));
            // Проверяем наличие ответа
            $this->assertTextPresent($data['changed']);
        }
    }

    /**
    *   @dataProvider topicsDataSet
    */
    public function testPostRating($division, $title, $text, $file, $error_message)
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);

        if (!$error_message) {
            // Заходим в раздел форума
            $this->clickAndWait($this->getElement('forum_subListItem', $division));
            // Заходим в тему форума
            $this->clickAndWait('link='.$title);
            // Запоминаем текущий рейтинг
            $count = $this->getText($this->getElement('forum_ratingSpan'));
            // Кликаем по рейтингу
            $this->click($this->getElement('forum_ratingI'));
            // Теперь рейтинг увеличился
            $count_after = ((int)$count) + 1;
            for ($second = 0; ; $second++) {
                if ($second >= 2) {
                    $this->fail('timeout');
                }
                try {
                    if ($this->isElementPresent($this->getElement('forum_ratingSpanGood'))) {
                        break;
                    }
                } 
                catch (Exception $e) {}
                sleep(1);
            }
            // Проверяем текущий рейтинг
            $this->assertEquals('+'.$count_after, $this->getText($this->getElement('forum_ratingSpanGood')));
        }
    }

    public function testUserTopics()
    {
         // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri, array('login' => 'logonarium', 'password' => '1'));
        // Заходим в Мои темы
        $this->clickAndWait($this->getElement('forum_myTopics'));
        // Проверяем что мы действительно там
        $this->assertEquals('Мои темы', $this->getText('css=h1'));
        
        $arr = $this->topicsDataSet();
        // Проверяем, что есть все темы, которые добавлялись
        foreach($arr as $a) {
            if (!$a[4]) {
                $this->assertEquals($a[1], $this->getText('link='.$a[1]));
            }
        }
    }

    public function testUserPosts()
    {
         // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri, array('login' => 'logonarium', 'password' => '1'));
        // Заходим в Мои ответы
        $this->clickAndWait($this->getElement('forum_myAnswers'));
        // Проверяем что мы действительно там
        $this->assertEquals('Мои ответы', $this->getText('css=h1'));
        
        $arr = $this->topicsDataSet();
        // Проверяем, что есть все темы, в которые добавлялись ответы
        foreach($arr as $a) {
            if (!$a[4]) {
                $this->assertEquals($a[1], $this->getText('link='.$a[1]));
            }
        }
    }

    /**
    *   @dataProvider topicsDataSet
    */
    public function testPostDeleting($division, $title, $text, $file, $error_message)
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);

        if (!$error_message) {
            // Заходим в раздел форума
            $this->clickAndWait($this->getElement('forum_subListItem', $division));
            // Заходим в тему форума
            $this->clickAndWait('link='.$title);
            // Если не авторизованы - выходит предупреждение
            if ($this->isElementPresent($this->getElement('forum_guestHint')))
            {
                // Авторизуемся
                $this->authorize('logonarium', '1');
            }
            // Кликаем на кнопку удаления ответа
            $this->click($this->getElement('forum_delete'));
            // Ждем появления подтверждения об удалении
            for ($second = 0; ; $second++) {
                if ($second >= 2) {
                    $this->fail('timeout');
                }
                try {
                    if ($this->isElementPresent($this->getElement('forum_deleteMessage'))) {
                        break;
                    }
                } catch (Exception $e) {}
                sleep(1);
            }
            $this->assertEquals('Сообщение было удалено.', $this->getText($this->getElement('forum_deleteMessage')));
        }
    }
/*
    public function testPostQuoting()
    {
        $this->setSpeed('200');
        // Открываем страницу
        $this->open('/social/forum/');

        if (!$error_message) {
            # code...
        }
    }

    // TBD. Подписка на раздел форума
    public function testDivisionSubscribe()
    {
        // Открываем главную страницу
        $this->open('/social/forum/');
        // Кликаем по ссылке "Подписаться на новые темы"
        $this->click('css=div.forum_subscribe > span');
        // Проверяем наличие лайтбокса
        $this->waitForElementPresent('id=popup-subscribe');

        $i = 0;
        while ($i < 2) {
            // Чекаем первый чекбокс
            $this->click('css=li > label > input[type="checkbox"]');
            $i++;
        }

        // Чекаем лейбл первого чекбокса
        $this->click('css=li > label');
        // Закрываем лайтбокс без сохранения
        $this->click('link=Отменить');
        // Открываем лайтбокс
        // Первый чекбокс должен быть не зачекнут
        $this->click('css=div.forum_subscribe > span');

        // Сохраняем
        $this->click('css=.popup-body .btn-grey');
        // Переходим в рубрику на которую подписались
        $this->clickAndWait('link=Дизайн интерьера');
        // Проверяем наличие маркера у подписаннойго раздела
        $this->verifyTextPresent('');

    }

    // TBD. Подписка на тему форума
    public function testTopicSubscribe()
    {

    }

    // TBD. Добавление в избранное
    public function testAddToFavorites()
    {

    }
*/

    /**
     * @dataProvider filesDataSet
     */
    public function testFileAttach($file, $approved, $error_message)
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);
        // Создаем новую тему
        $this->clickAndWait($this->getElement('forum_createNewTopic'));
        // Если не авторизованы - выходит предупреждение
        if($this->isElementPresent($this->getElement('forum_guestHint')))
        {
            // Авторизуемся
            $this->authorize('logonarium', '1');
        }
        // Выбираем первый раздел форума
        $this->click($this->getElement('forum_toggleSelectSubdivision'));
        $this->click($this->getElement('forum_selectSubdivision', '1'));
        // Заполняем тему
        $this->type($this->getElement('forum_topicName'), 'Добавление файла: '.$file.'.'.$file);
        // Заполняем описание
        $this->type($this->getElement('forum_topicDesc'), 'Добавление файлов различных форматов');
        // Заполняем инпут
        $this->type($this->getElement('forum_topicFile'), '/home/gmv/tmp/normal/'.$file.'.'.$file);
        // $this->fireEvent($this->getElement('forum_topicFile'), 'blur');
        // Ловим alert
        if ($this->assertTrue($this->isAlertPresent())) {
            $this->assertEquals($error_message, $this->getAlert());
        }
        else {
            // Сабмитим форму
            $this->clickAndWait($this->getElement('forum_formSubmitTopic'));
        }

        // if ($this->isAlertPresent() == true) {
        // 	$this->assertEquals($this->getAlert(), $error_message);
        // }
        // else {
        //     // Сабмитим форму
	       //  $this->clickAndWait($this->getElement('forum_formSubmitTopic'));
        // }
    }

    public function testFileAPI()
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);
        // Создаем новую тему
        $this->clickAndWait($this->getElement('forum_createNewTopic'));
        // Если не авторизованы - выходит предупреждение
        if($this->isElementPresent($this->getElement('forum_guestHint')))
        {
            // Авторизуемся
            $this->authorize('logonarium', '1');
        }
        // Выбираем первый раздел форума
        $this->click($this->getElement('forum_toggleSelectSubdivision'));
        $this->click($this->getElement('forum_selectSubdivision', '1'));
        // Заполняем тему
        $this->type($this->getElement('forum_topicName'), 'Добавление множества допустимых файлов');
        // Заполняем описание
        $this->type($this->getElement('forum_topicDesc'), 'Добавление множества файлов различных форматов');
        // Добавляем файлы
        $arr = $this->filesDataSet();
        $i = 0;
        foreach($arr as $a)
        {
            if($a[1])
            {
                $mod = ($i > 0) ? '_F'.$i : '';
                $this->type($this->getElement('forum_topicFile').$mod, '/home/gmv/tmp/normal/'.$a[0].'.'.$a[0]);
                //$this->fireEvent($this->getElement('forum_topicFile').$mod, 'blur');

				// Ждем появления ссылки на удаление
				for ($second = 0; ; $second++) {
					if ($second >= 3) {
						$this->fail('FileAPI file link appearance timeout');
					}
					try {
						if ($this->isElementPresent($this->getElement('forum_fileAPIListItem', ($i + 1)))) {
							break;
						}
					} catch (Exception $e) {}
					sleep(1);
				}
            }
            $i++;
        }
        // Сабмитим форму
        $this->clickAndWait($this->getElement('forum_formSubmitTopic'));
        // Проверяем загрузились ли файлы разрешенных типов
        foreach($arr as $a)
        {
            if($a[1])
            {
                $this->assertText('link='.$a[0].'.'.$a[0], $a[0].'.'.$a[0]);
            }
        }
    }

    public function testForumSearch()
    {
        // Устанавливаем скорость, открываем страницу, авторизуемся, если нужно
        $this->startAction($this->uri);
        // Выбираем строку для поиска
        $str = $this->getText($this->getElement('forum_mainTopicsListItem'));
        $arr = explode(' ', $str); 
        array_push($arr, $str);
        foreach ($arr as $s) {
	        // Заполняем поле поиска
	        $this->type($this->getElement('forum_searchField'), $s);
	        // Сабмитим форму
	        $this->clickAndWait($this->getElement('forum_searchSubmit'));
	        // Если найдено, то
	        if (!$this->isTextPresent('По вашему запросу не найдено результатов')) 
	        {
	            // Сравниваем выделенные слова в теле страницы с поисковым запросом
	            $this->assertEquals($s, $this->getText($this->getElement('forum_searchWordSpan')));
	            // Проверяем, что выводится количество найденных
	            $this->assertRegExp('/(Найден\S*)\s([0-9]+)\s(тем\S* по запросу)\s(«'.$s.'»)/', $this->getText($this->getElement('forum_searchResult')));
	        }
        }
    }
}
?>