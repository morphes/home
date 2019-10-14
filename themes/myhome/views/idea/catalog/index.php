<?php
$this->pageTitle = 'Идеи Интерьеров, Архитектурные Решения — MyHome.ru';

$this->description = 'Оригинальные идеи интерьеров жилых и нежилых помещений с фото и описанием в каталоге интерьеров
		      на MyHome.ru. Архитектурные решения для домов, коттеджей, особняков, общественных зданий';

$this->keywords = 'идеи интерьеров, архитектура, идеи жилых интерьеров, жилые интерьеры, общественные интерьеры,
		   каталог интерьеров, оригинальные интерьеры, интерьерные решения, майхоум, myhome, myhome.ru';


Yii::app()->openGraph->title = 'Идеи';
Yii::app()->openGraph->description = 'Самые удачные, любопытные и оригинальные решения интерьеров со всего мира.';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';

Yii::app()->openGraph->renderTags();
?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(),
	));?>
	<h1>Идеи <span class="text_block">интерьеров, архитектурные решения</span></h1>

	<div class="spacer"></div>
</div>

<div class="border_block padding-18 beige">

	<div class="links_block ideas">
		<h1>
			<a class="h1_link" href="<?php echo $this->createUrl('/idea/catalog/interior'); ?>">Интерьеры</a>
			<span class="section_items_count"><?php echo Idea::getQntPhotosInterior() + Idea::getQntPhotosInteriorpublic();?></span>
		</h1>
		<p>В каталоге интерьеров аккуратно по полочкам разложены самые удачные, любопытные и оригинальные решения интерьеров со всего мира. Остается выбрать лишь цвет и стиль помещения.</p>
		<div class="search_link">
			<div><a href="<?php echo $this->createUrl('/idea/catalog/interior'); ?>">Жилые объекты</a><span class="items_count"><?php echo Idea::getQntPhotosInterior();?></span></div>
			<div><a href="<?php echo $this->createUrl('/idea/catalog/interiorpublic'); ?>">Общественные объекты</a><span class="items_count"><?php echo Idea::getQntPhotosInteriorpublic();?></span></div>
		</div>
	</div>
	<div class="popular_objects">
		<p>Популярные интерьеры</p>
		<div>
			<?php
			$name = 'Ванные';
			$img = '/img/tmp/int_bathroom.png';
			$url = $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'bathroom'));
			?>
			<div class="item">
				<a href="<?php echo $url;?>"><img width="150" height="150" src="<?php echo $img;?>"/></a>
				<a href="<?php echo $url;?>"><?php echo $name;?></a>
			</div>



			<?php
			$name = 'Гостиные';
			$img = '/img/tmp/int_hall.png';
			$url = $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'livingroom'));
			?>
			<div class="item">
				<a href="<?php echo $url;?>"><img width="150" height="150" src="<?php echo $img;?>"/></a>
				<a href="<?php echo $url;?>"><?php echo $name;?></a>
			</div>


			<?php
			$name = 'Кухни';
			$img = '/img/tmp/int_kitchen.png';
			$url = $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'kitchen'));
			?>
			<div class="item">
				<a href="<?php echo $url;?>"><img width="150" height="150" src="<?php echo $img;?>"/></a>
				<a href="<?php echo $url;?>"><?php echo $name;?></a>
			</div>
		</div>
	</div>
	<div class = "popular_objects_links">
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'bedroom')); ?>">Спальни</a>
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'nursery')); ?>">Детские</a>
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'study')); ?>">Кабинеты</a>
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'diningroom')); ?>">Столовые</a>
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'apartment')); ?>">Квартиры-студии</a>
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'hall')); ?>">Прихожие</a>
		<a href="<?php echo $this->createUrl('/idea/catalog/interior', array('humanUrl'=>'attic')); ?>">Мансарды</a>
	</div>
	<div class="clear"></div>
</div>
<div class="spacer-18"></div>
<div class="border_block beige">

	<div class="links_block ideas">
		<?php

		$objects = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, 0, 'object');
		$counts = array();
		foreach($objects as $obj) {
			$counts[] = array(
				'name' => $obj->option_value,
				'count' => Architecture::model()->countBySql("
					SELECT COUNT(*)
					FROM idea_uploaded_file iuf
					LEFT JOIN architecture a
						ON a.id = iuf.item_id
					WHERE
						(a.status = :st1 OR a.status = :st2)
						AND
						iuf.idea_type_id = 3
						AND
						object_id = '{$obj->id}'
				", array(':st1'=>Architecture::STATUS_ACCEPTED, ':st2'=>Architecture::STATUS_CHANGED)),
				'url' => '/idea/catalog/index?ideatype=2&object_type='.$obj->id
			);
		}
		?>
		<h1>
			<a class="h1_link" href="<?php echo $this->createUrl('/idea/catalog/architecture'); ?>">Архитектура</a>
			<span class="section_items_count"><?php echo Idea::getQntPhotosArchitecture(); ?></span>
		</h1>
		<p>Архитектура вдохновляет. А&nbsp;возвести задуманное архитектурное творение помогает данный раздел.
			Здесь можно подобрать стиль, материалы, различные решения будущей постройки.</p>

		<?php
		if ($counts) {
			echo '<div class="search_link">';
			foreach($counts as $item) {
				echo '<div><a href="'.$item['url'].'">'.$item['name'].'</a><span class="items_count">'.$item['count'].'</span></div>';
			}
			echo '</div>';
		}
		?>
	</div>
	<div class="popular_objects">
		<p>Популярные объекты</p>
		<div>
			<?php
			$name = 'Деревянные коттеджи';
			$img = '/img/tmp/arch_wood2.png';
			$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=118&style=&material=145&floor=&room=&color=&elements_on_page=&sort_elements=';
			?>
			<div class="item">
				<a href="<?php echo $url;?>"><img width="150" height="150" src="<?php echo $img;?>"/></a>
				<a href="<?php echo $url;?>"><?php echo $name;?></a>
			</div>



			<?php
			$name = 'Современные дома';
			$img = '/img/tmp/arch_modern2.png';
			$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=118&style=131%2C+132%2C+133%2C+136%2C+140%2C+&material=&floor=&room=&color=&elements_on_page=&sort_elements=';
			?>
			<div class="item">
				<a href="<?php echo $url;?>"><img width="150" height="150" src="<?php echo $img;?>"/></a>
				<a href="<?php echo $url;?>"><?php echo $name;?></a>
			</div>



			<?php
			$name = 'Дома с мансардой';
			$img = '/img/tmp/arch_mansard2.png';
			$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=&material=&floor=&room=mansard%2C+&color=&elements_on_page=&sort_elements=';
			?>
			<div class="item">
				<a href="<?php echo $url;?>"><img width="150" height="150" src="<?php echo $img;?>"/></a>
				<a href="<?php echo $url;?>"><?php echo $name;?></a>
			</div>
		</div>
	</div>
	<div class = "popular_objects_links">
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=120%2C+122%2C+123%2C+124%2C+126%2C+127%2C+129%2C+130%2C+138%2C+139%2C+141%2C+137%2C+&material=&floor=&room=&color=&elements_on_page=&sort_elements=">Дома в классическом стиле</a>
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=&material=&floor=154&room=&color=&elements_on_page=&sort_elements=">Трехэтажные усадьбы</a>
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=&material=147&floor=&room=&color=&elements_on_page=&sort_elements=">Кирпичные коттеджи</a>
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=118&style=131%2C+&material=&floor=&room=&color=&elements_on_page=&sort_elements=">Необычные дома</a>
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=160&build_type=161%2C+&material=&elements_on_page=&sort_elements=">Бани, бассейны</a>
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=160&build_type=162%2C+&material=&elements_on_page=&sort_elements=">Террасы, беседки</a>
		<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=160&build_type=164%2C+&material=&elements_on_page=&sort_elements=">Гаражи</a>
	</div>
	<div class="clear"></div>
</div>
<div class="spacer-30"></div>