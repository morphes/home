<div class="border_block padding-18 main_block gray">

	<div class="links_block">
		<h1>
			<a class="h1_link" href="/idea/architecture/catalog">Архитектура</a>
			<span class="section_items_count"><?php echo Architecture::model()->count('status=:s1 OR status=:s2', array(':s1'=>Architecture::STATUS_ACCEPTED, ':s2'=>Architecture::STATUS_CHANGED));?></span>
		</h1>
		<p>Архитектура вдохновляет. Начиная с идеи и первых эскизов и заканчивая последним кирпичиком или шпилем.
			Все это можно и нужно обсуждать. Для этого и создан данный раздел. Выбирайте стиль, материалы,
			проектируйте и стройте с личным архитектором свое неповторимое здание.</p>
		<div class="search_link">
			<a href="/idea/architecture/catalog">Поиск экстерьеров</a>
			<span>&rarr;</span>
		</div>

		<div class="clear"></div>
		<div class="item">
			<?php
			/* ------------------------- */
			$name = 'Деревянные коттеджи';
			$img = '/img/tmp/arch_wood.png';
			$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=118&style=&material=145&floor=&room=&color=&elements_on_page=&sort_elements=';
			?>
			<a href="<?php echo $url;?>">
				<img align="left" width="160" height="160"  src="<?php echo $img;?>" alt="<?php echo $name;?>" />
			</a>
			<div class="item_link"><a class="" href="<?php echo $url;?>"><?php echo $name;?></a></div>
		</div>
		<div class="item no-margin">
			<?php
			/* ------------------------- */
			$name = 'Современные дома';
			$img = '/img/tmp/arch_modern.png';
			$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=131%2C+132%2C+133%2C+136%2C+140%2C+&material=&floor=&room=&color=&elements_on_page=&sort_elements=';
			?>
			<a href="<?php echo $url;?>">
				<img align="left" width="160" height="160"  src="<?php echo $img;?>" alt="<?php echo $name;?>" />
			</a>
			<div class="item_link"><a class="" href="<?php echo $url;?>"><?php echo $name;?></a></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="items_block">
		<div class="items_container">
			<div class="item item_main">
				<?php
				/* --- БОЛЬШАЯ ФОТКА --- */
				$idea = Architecture::model()->findByPk(28);
				if ( ! $idea) {
					$idea = new Architecture();
				}
				$ideaUrl = '/idea/architecture/'.$idea->id;
				?>
				<a href="<?php echo $ideaUrl;?>">
					<img align="left" width="398" height="344"  src="/<?php echo $idea->getPreview( Config::$preview['crop_398x344'] );?>" alt="<?php echo $idea->name;?>" />
				</a>
				<div class="item_link">
					<a class="" href="<?php echo $ideaUrl;?>"><?php echo $idea->name;?></a><br/>
					<?php /* <a class="autor_link" href="<?php echo $idea->author->getLinkProfile();?>"><?php echo $idea->author->name;?></a> */?>
					<span>Редакция MyHome</span>
				</div>

			</div>
			<div class="item">
				<?php
				/* ------------------------- */
				$name = 'Дома с мансардой';
				$img = '/img/tmp/arch_mansard.png';
				$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=&material=&floor=&room=mansard%2C+&color=&elements_on_page=&sort_elements=';
				?>
				<a href="<?php echo $url;?>">
					<img align="left" width="160" height="160"  src="<?php echo $img;?>" alt="<?php echo $name;?>" />
				</a>
				<div class="item_link"><a class="" href="<?php echo $url;?>"><?php echo $name;?></a></div>
			</div>
			<div class="item no-margin">
				<?php
				/* ------------------------- */
				$name = 'Трехэтажные усадьбы';
				$img = '/img/tmp/arch_3floor.png';
				$url = '/idea/catalog/index?filter=1&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=&material=&floor=154&room=&color=&elements_on_page=&sort_elements=';
				?>
				<a href="<?php echo $url;?>">
					<img align="left" width="160" height="160"  src="<?php echo $img;?>" alt="<?php echo $name;?>" />
				</a>
				<div class="item_link"><a class="" href="<?php echo $url;?>"><?php echo $name;?></a></div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div class="spacer-30"></div>