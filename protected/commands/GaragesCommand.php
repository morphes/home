<?php

class GaragesCommand extends CConsoleCommand
{

        const DESTINATION_URL = 'http://garages.ngs.ru/rent_available/?flat[491]=1&intOption2=24&intOption3Fake=&intOption3[353]=1&streets=&streets[23025]=%D0%93%D0%BE%D1%80%D1%81%D0%BA%D0%B8%D0%B9+%D0%BC%D0%B8%D0%BA%D1%80%D0%BE%D1%80%D0%B0%D0%B9%D0%BE%D0%BD&objectHomeNumber=&intOption5[3]=&intOption5[6]=&intOption1[3]=&intOption1[6]=&objectPlace=&typeOfOwner=allOwner&other=1&_owner=&btn=%D0%9D%D0%B0%D0%B9%D1%82%D0%B8&period=2012-09-03&on_page=50&by=&order=';
        const DESTINATION_EMAIL = 'roman.kuzakov@gmail.com';


        public function init()
        {
                parent::init();

                Yii::import('application.vendor.phpQuery.*');
        }


        public function actionParse()
        {
                $page = phpQuery::newDocument(Yii::app()->curl->run(self::DESTINATION_URL));

                $garages = $page->find('#list-records tr.lines');

                foreach($garages as $garage) {

                        $fields = phpQuery::pq($garage)->find('td');

                        $public_date = '';
                        $garage_url = '';
                        $hash = '';

                        foreach($fields as $key=>$field) {
                                if($key == 0)
                                        $public_date = trim(phpQuery::pq($field)->text());

                                if($key == 2)
                                        $garage_url = phpQuery::pq($field)->find('a')->attr('href');
                        }

                        $hash = md5($public_date.'_'.$garage_url);

                        if(!Yii::app()->cache->get($hash)) {
                                Yii::app()->cache->add($hash, time());
                                mail(self::DESTINATION_EMAIL, 'Новое объявление о парковочном месте', 'http://garages.ngs.ru' . $garage_url);
                        }
                }
        }

}
