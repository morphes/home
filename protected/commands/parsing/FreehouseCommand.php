<?php

class FreehouseCommand extends CConsoleCommand
{
        const URL_STATUS_NOT_PROCESSED = 0; // ссылка не обработана парсером
        const URL_STATUS_PROCESSED = 1; // ссылка обработана парсером

        public $siteUrl = 'http://www.freehouse.ru'; // url обрабатываемого сайта
        public $urls_table = 'freehouse_urls'; // название таблицы, хранящей urls на анкеты пользователей


        public function init()
        {
                parent::init();

                /**
                 * Подключение классов парсера phpQuery
                 */
                Yii::import('application.vendor.phpQuery.*');
        }


        /**
         * Просмотр каталога исполнителей и сохранение ссылок на все страницы исполнителей (пагинатора)
         */
        public function actionParsePaginator()
        {
                /**
                 * Удаление ранее созданной таблицы для сохранения результатов обработки
                 */
                Yii::app()->db->createCommand("DROP TABLE IF EXISTS {$this->urls_table}")->execute();

                /**
                 * Создание таблицы, хранящей результаты работы метода
                 */
                Yii::app()->db->createCommand()->createTable($this->urls_table, array(
                        'id'=>'pk',
                        'url'=>'varchar(500)', // ссылка на анкету исполнителя
                        'status'=>'int(11)', // статус записи self::URL_STATUS_NOT_PROCESSED или self::URL_STATUS_PROCESSED
                ));

                /**
                 * Первая страница каталога исполнителей
                 */
                $url = $this->siteUrl . "/contractors-c-all-d-2870.html";

                /**
                 * Цикл обхода каталога исполнителей по ссылке "Следующая страница" и сохранение ссылок на анкеты исполнителей
                 */
                while(1){

                        /**
                         * Загрузка и создание документа для парсинга
                         */
                        $page = phpQuery::newDocument(Yii::app()->curl->run($url));

                        /**
                         * Поиск ссылок на исполнителей
                         */
                        $specs = $page->find('.utitle > a');

                        /**
                         * Сохранение найденных ссылок на анкеты исполнителей в базу
                         */
                        foreach($specs as $spec) {

                                /**
                                 * Получение значения атрибута href объекта ссылки
                                 */
                                $specUrl = phpQuery::pq($spec)->attr('href');

                                /**
                                 * Сохранение ссылки в базу
                                 */
                                Yii::app()->db->createCommand()->insert($this->urls_table, array(
                                        'url'=>$specUrl,
                                        'status'=>self::URL_STATUS_NOT_PROCESSED,
                                ));
                        }

                        /**
                         * Вывод обработанного URL на экран
                         */
                        echo $url . " was parsed\n";

                        /**
                         * Поиск ссылки "Следующая страница"
                         */
                        $nextLink = $page->find('.nextpage > a');

                        /**
                         * Если нет ссылки "Следующая страница" - каталог пройден полностью
                         */

                        /**
                         * Ссылка на следующую страницу
                         */
                        $nextLinkUrl = phpQuery::pq($nextLink)->attr('href');

                        /**
                         * Если сайт заблокировался от ддос, замираем на время и продолжаем парсить
                         */
                        if(empty($nextLinkUrl)) {
                                echo "Anti-DDoS-protection...\n";
                                sleep(35);
                                continue;
                        }

                        /**
                         * Обновление ссылки, требующей обработки на следующей итерации цикла
                         */
                        $url = $this->siteUrl . '/' . $nextLinkUrl;

                        /**
                         * Прерывание цикла для обхода ддос-защиты сайта
                         */
                        sleep (rand(1,3));
                }
        }

        /**
         * Парсинг пользователей с сайта
         */
        public function actionParseSpecialist()
        {
                /**
                 * Получение ссылок на анкеты пользователей
                 */
                $specUrls = Yii::app()->db->createCommand(array(
                        'select' => array('id', 'url', 'status'),
                        'from' => $this->urls_table,
                        'where'=>'status=:st',
                        'params'=>array(':st'=>self::URL_STATUS_NOT_PROCESSED),
                ))->queryAll();

                foreach($specUrls as $url) {

                        /**
                         * Информационное сообщение
                         */
                        echo "\nParsing url {$url['id']}...";

                        /**
                         * Загрузка и создание документа для парсинга
                         */
                        $page = phpQuery::newDocument(Yii::app()->curl->run($this->siteUrl . '/' . $url['url']));

                        /**
                         * Прерывание цикла для обхода ддос-защиты сайта
                         */
                        sleep (rand(2,4));

                        /**
                         * Установка статуса ссылки "Обработано"
                         */
                        Yii::app()->db->createCommand()->update($this->urls_table, array('status'=>self::URL_STATUS_PROCESSED), 'id=:id', array(':id'=>$url['id']));

                        /**
                         * Поиск объекта в html, содержащего ФИО пользователя
                         */
                        $nameDom = $page->find('div.usertitle > h1');

                        /**
                         * Получение значения имени исполнителя
                         */
                        $name = explode(' ', phpQuery::pq($nameDom)->text());

                        /**
                         * Проверка на заполненность фамилии и имени
                         */
                        if(!isset($name[0]) || !isset($name[1]) || empty($name[0]) || empty($name[1])) {
                                echo "\nAnti-DDoS-protection...";
                                sleep(35);
                                continue;
                        }

                        /**
                         * Поиск объекта в html, содержащего email
                         */
                        $emailDom = $page->find('ul.info > li:first > a');

                        /**
                         * Получение значения email
                         */
                        $email = phpQuery::pq($emailDom)->html();

                        /**
                         * Валидация email
                         */
                        preg_match("/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,4}|museum|travel)/", $email, $validEmail);

                        /**
                         * Если полученный email некорректен - пропускаем анкету
                         */
                        if (empty($validEmail))
                                continue;

                        /**
                         * Сохранение данных пользователя
                         */
                        Yii::app()->db->createCommand()->insert('parsed_users', array(
                                'firstname'=>$name[1],
                                'lastname'=>$name[0],
                                'email'=>$validEmail[0],
                                'source'=>'freehouse',
                                'source_url'=>$url['url'],
                        ));

                        echo "ok";
                }
        }

        /**
         * Поиск дублирующихся пользователей
         */
        public function actionClean()
        {
                /**
                 * Получение всех спарсенных пользователей
                 */
                $parsedUsers = Yii::app()->db->createCommand(array(
                        'from' => 'parsed_users',
                ))->queryAll();

                $inMyHome = 0; // кол-во пользователей, найденных в базе myhome
                $deleted = 0; // кол-во удаленных пользователей

                /**
                 * Поиск дубликатов каждого спарсенного пользователя
                 */
                foreach($parsedUsers as $us) {

                        echo "\nChecking user ${us['id']}... ";

                        /**
                         * Поиск спарсенного пользователя в базе.
                         * Если был найден - удаляем.
                         */
                        if(User::model()->exists('email=:email', array(':email'=>$us['email']))) {
                                Yii::app()->db->createCommand()->delete('parsed_users', 'id=:id', array(':id'=>$us['id']));
                                echo "founded in myhome users. Deleted.";
                                $inMyHome++; $deleted++;
                                continue;
                        }

                        /**
                         * Поиск дубликатов пользователей среди спарсенных
                         */
                        $dublicates = Yii::app()->db->createCommand(array(
                                'from' => 'parsed_users',
                                'where' => 'id<>:id AND email=:email',
                                'params' => array(':id'=>$us['id'], ':email'=>$us['email']),
                        ))->queryAll();

                        /**
                         * Обход и удаление дубликатов
                         */
                        foreach($dublicates as $dub) {
                                Yii::app()->db->createCommand()->delete('parsed_users', 'id=:id', array(':id'=>$dub['id']));
                                echo "\nUser {$us['id']} has dublicate {$dub['id']}. Dublicate deleted.";
                                $deleted++;
                        }
                }

                echo "\n\nDeleted: {$deleted} users. Been in myhome: {$inMyHome}";
        }

}
