<?php

class RemontnikCommand extends CConsoleCommand
{
        public function init()
        {
                parent::init();
                Yii::import('application.vendor.phpQuery.*');
        }


        public function actionParseCatalogLinks()
        {
                $start = time();

                $command = Yii::app()->db->createCommand();
                $command->createTable('remontnik_urls', array('id'=>'pk', 'url'=>'varchar(500)'));

                $services = $this->parseServices();

                echo "\n\nTotal time: " . (time() - $start) . " sec.\n\n";
        }


        public function actionParseSpecialists()
        {
                $start = time();

                $command = Yii::app()->db->createCommand();
                $command->createTable('parsed_users', array(
                        'id'=>'pk',
                        'firstname'=>'varchar(500)',
                        'lastname'=>'varchar(500)',
                        'email'=>'varchar(500)',
                        'source'=>'varchar(500)',
                        'source_url'=>'varchar(500)',
                ));

                $services = Yii::app()->db->createCommand(array(
                        'select' => array('id', 'url'),
                        'from' => 'remontnik_urls',
                ))->queryAll();

                foreach ($services as $srv) {
                        $this->getSpecialists($srv['url']);
                        echo("Parsed {$srv['id']} service link\n");
                }

                echo "\n\nTotal time: " . (time() - $start) . " sec.\n\n";
        }

        /**
         * Получение списка ссылок на каталоги мастеров по каждой услуге
         */
        private function parseServices($limit = null)
        {
                $html = Yii::app()->curl->run('http://www.remontnik.ru/services/');
                $srvPage = phpQuery::newDocument($html);
                $services = $srvPage->find('.data-table-no.hover-table tbody tr');
                $result = array();
                $i = 0;
                foreach ($services as $srv) {
                        $link = phpQuery::pq($srv)->find('td a');
                        $href = phpQuery::pq($link)->attr('href');
                        $this->parseSpecialistPaginate($href);
                        $i++;
                        echo("Service {$i} parsed with paginate\n");
                        if (!is_null($limit) && $i >= $limit)
                                break;
                }
                unset($html);
                unset($srvPage);
                unset($services);
                return $result;
        }

        /**
         * Получение списка страниц каталога мастеров (по каждой услуге)
         */
        private function parseSpecialistPaginate($url = null)
        {
                if (is_null($url))
                        return array();

                $html = Yii::app()->curl->run('http://www.remontnik.ru/' . $url);
                $specPage = phpQuery::newDocument($html);
                $pages = $specPage->find('.pages-nav .number li');
                $result = array();
                foreach ($pages as $page) {
                        $link = phpQuery::pq($page)->find('a');
                        $href = $url . phpQuery::pq($link)->attr('href');
                        Yii::app()->db->createCommand()->insert('remontnik_urls', array('url'=>$href));
                }
                unset($html);
                unset($specPage);
                unset($pages);
                return $result;
        }

        private function getSpecialists($url = null)
        {
                if (is_null($url))
                        return array();

                Yii::app()->db->createCommand()->delete('remontnik_urls', 'url=:url', array(':url'=>$url));

                $html = Yii::app()->curl->run('http://www.remontnik.ru' . $url);
                $specPage = phpQuery::newDocument($html);
                $specialists = $specPage->find('.data-table-no.list-masters.f13 tbody tr');
                $result = array();

                foreach ($specialists as $spec) {
                        $last = phpQuery::pq($spec)->find('td.last > .contacts');
                        $contacts = phpQuery::pq($last)->html();
                        preg_match("/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,4}|museum|travel)/", $contacts, $matches);
                        if (empty($matches))
                                continue;

                        $first = phpQuery::pq($spec)->find('td.first ul.info-line > li.first > a.red > strong');
                        $name = phpQuery::pq($first)->html();
                        $fio = explode(' ', $name);

                        if(empty($fio[0]) || empty($fio[1]))
                                continue;

                        Yii::app()->db->createCommand()->insert('parsed_users', array(
                                'firstname'=>$fio[0],
                                'lastname'=>$fio[1],
                                'email'=>$matches[0],
                                'source'=>'remontnik',
                                'source_url'=>$url,
                        ));

                }

        }


}
