<?php

Yii::import('application.modules.admin.models.*');
class BannerTest extends CDbTestCase
{

        public $fixtures = array(
		'banner_item'=>':banner_item',
		'banner_item_section'=>':banner_item_section',
		'banner_item_section_geo'=>':banner_item_section_geo',
		'banner_rotation'=>':banner_rotation',
        );


	/**
	 * Тест создания баннера и публикации его в различных секциях и городах/странах
	 */
	public function testCreateBanner()
        {
		// проверка создания баннера
		$banner1 = new BannerItem();
		$banner1->setAttributes(array(
			'user_id'=>2,
			'type_id'=>BannerItem::TYPE_HORIZONTAL,
			'customer'=>'Test 1 Customer LLC',
			'status'=>BannerItem::STATUS_ACTIVE,
			'file_id'=>1,
		));
		$this->assertTrue($banner1->save());

		// проверка создания связки баннер-раздел сайта
		$section1 = $banner1->createInSection(Config::SECTION_ALL, BannerItemSection::TARIFF_100, time(), time() + 3600 * 24 * 10);
		$this->assertInstanceOf('BannerItemSection', $section1);
		// проверка создания связки баннер-раздел сайта-гео ориентированность баннера
		$this->assertTrue($section1->assignToGeo(BannerItemSectionGeo::GEO_TYPE_COUNTRY, 3159));

		// проверка валидации
		// можно создать любую связку баннера и секции, пока для этой связки не указана гео-направленность
		$banner2 = new BannerItem();
		$banner2->setAttributes(array(
			'user_id'=>2,
			'type_id'=>BannerItem::TYPE_HORIZONTAL,
			'customer'=>'Test 2 Customer LLC',
			'status'=>BannerItem::STATUS_ACTIVE,
			'file_id'=>1,
		));
		$this->assertTrue($banner2->save());
		$section2 = $banner2->createInSection(Config::SECTION_ALL, BannerItemSection::TARIFF_100, time(), time() + 3600 * 24 * 10);
		$this->assertInstanceOf('BannerItemSection', $section2);

		// проверка валидации баннера
		// (нельзя создавать баннер в указанной секции по тому же региону, что и баннер,
		// который уже занимает в нем 100% трафика)
		$this->assertFalse($section2->assignToGeo(BannerItemSectionGeo::GEO_TYPE_COUNTRY, 3159));

		// проверка валидации баннера
		// (можно создать баннер в указанной секции, но в другом регионе)
		$this->assertTrue($section2->assignToGeo(BannerItemSectionGeo::GEO_TYPE_COUNTRY, 248));

		// проверка валидации
		// можно создать баннер со 100% трафиком по региону, в котором транслируется баннер со 100% трафика
		// но только после окончания срока действия уже существующего баннера
		$section2->start_time = $section1->end_time;
		$section2->end_time = $section1->end_time + 3600 * 24 * 10;
		$this->assertTrue($section2->save());
		$this->assertTrue($section2->assignToGeo(BannerItemSectionGeo::GEO_TYPE_COUNTRY, 3159));

		// проверка валидации
		// нельзя изменить время начала показов баннера так, чтобы оно пересекалось с временем другого баннера,
		// который транслируется на 100% трафика в этом же регионе на этом же разделе
		$section2->start_time = $section1->start_time;
		$this->assertFalse($section2->save());

		// проверка валидации
		// нельзя сохранить баннер с временем показов не кратным 10 дням
		$section2->start_time = $section1->end_time + 1;
		$section2->end_time = $section1->end_time + 3600 * 24 * 10;
		$this->assertFalse($section2->save());

		// проверка валидации
		// можно запустить баннер в разделе сайта по тому же региону, что и другой баннер,
		// если суммарный объем их трафика не превышает 100%
		$section1->tariff_id = BannerItemSection::TARIFF_33;
		$section2->tariff_id = BannerItemSection::TARIFF_33;
		$section2->start_time = $section1->start_time;
		$section2->end_time = $section1->end_time;
		$this->assertTrue($section1->save(false));
		$this->assertTrue($section2->save());

		// проверка валидации
		// нельзя создать баннер в том же регионе, где крутятся два других баннера, суммарный процент показов
		// которых не представляет возможным "впихнуть" еще один баннер с указанным процентом
		$banner3 = new BannerItem();
		$banner3->setAttributes(array(
			'user_id'=>2,
			'type_id'=>BannerItem::TYPE_HORIZONTAL,
			'customer'=>'Test 3 Customer LLC',
			'status'=>BannerItem::STATUS_ACTIVE,
			'file_id'=>1,
		));
		$this->assertTrue($banner3->save());
		$section3 = $banner3->createInSection(Config::SECTION_ALL, BannerItemSection::TARIFF_100, time(), time() + 3600 * 24 * 10);
		$this->assertInstanceOf('BannerItemSection', $section3);
		$this->assertFalse($section3->assignToGeo(BannerItemSectionGeo::GEO_TYPE_COUNTRY, 3159));
		// обратная ситуация (можно создать, если никому не мешает)
		$section3->tariff_id = BannerItemSection::TARIFF_33;
		$section3->save();
		$this->assertTrue($section3->assignToGeo(BannerItemSectionGeo::GEO_TYPE_COUNTRY, 3159));
        }
}