<div class="catalog_block">
	<div class="catalog_block_wrapper">
		<div class="catalog_category">
			<h2 class="main_page_head"><a href="<?php echo Yii::app()->createUrl('/catalog');?>">Товары</a></h2>
			<span class="headline_counter"><?php echo Product::countAll();?></span>

			<?php if($this->beginCache('MainPage::promoCatalog', array('duration'=>3600))) { ?>
			<span class="catalog_link">
				<a class="main_link" href="<?php echo Yii::app()->createUrl('/catalog');?>">Все категории</a>
				<span>&rarr;</span>
			</span>
			<div class="catalog_subcategory">
				<h3>Мебель</h3>
				<ul class="">
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 9));?>">Диваны</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 11));?>">Кресла</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 13));?>">Шкафы </a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 24));?>">Комоды</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 20));?>">Кровати</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 22));?>">Матрасы </a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 26));?>">Обеденные столы </a></li>
<!--					<li><a href="#">Стулья</a></li>-->
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 65));?>">Детские комнаты</a></li>
				</ul>
				<div class="clear"></div>
			</div>
			<div class="catalog_subcategory">
				<h3>Отделочные материалы</h3>
				<ul class="">
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 44));?>">Линолеум</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 42));?>">Паркет</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 36));?>">Обои</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 59));?>">Керамическая плитка</a></li>
				</ul>
				<div class="clear"></div>
			</div>
			<div class="catalog_subcategory">
				<h3>Освещение</h3>
				<ul class="">
<!--					<li><a href="#">Люстры</a></li>-->
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 50));?>">Настенные светильники</a></li>
					<li><a href="<?php echo $this->createUrl('/catalog/category/list', array('id' => 51));?>">Потолочные светильники</a></li>
				</ul>
				<div class="clear"></div>
			</div>
			<a class="banner" href="<?php echo Yii::app()->params->bmHomeUrl.'/catalog';?>">
				<img src="/img/banners/banner.png" width="302" height="60">
			</a>

			<?php $this->endCache(); } ?>
		</div>
		<script type="text/javascript">
			js.drowTriangle($('.catalog_category'));
		</script>


		<?php
		$randomProd = UnitProduct::getRandomProduct();

		// Данные для блока Товаров
		$arrProducts = array(
			array('catId' => '9', 'name' => 'Диваны', 'img' => '/img/mainProducts/9.jpg'),
			array('catId' => '11', 'name' => 'Кресла', 'img' => '/img/mainProducts/11.jpg'),
			array('catId' => '13', 'name' => 'Шкафы', 'img' => '/img/mainProducts/13.jpg'),
			array('catId' => '16', 'name' => 'Ванны', 'img' => '/img/mainProducts/16.jpg'),
			array('catId' => '17', 'name' => 'Раковины', 'img' => '/img/mainProducts/17.jpg'),
			array('catId' => '18', 'name' => 'Унитазы', 'img' => '/img/mainProducts/18.jpg'),
			array('catId' => '20', 'name' => 'Кровати', 'img' => '/img/mainProducts/20.jpg'),
			array('catId' => '24', 'name' => 'Комоды', 'img' => '/img/mainProducts/24.jpg'),
			array('catId' => '26', 'name' => 'Обеденные столы', 'img' => '/img/mainProducts/26.jpg'),
			array('catId' => '28', 'name' => 'Стулья', 'img' => '/img/mainProducts/28.jpg'),
			array('catId' => '32', 'name' => 'Люстры', 'img' => '/img/mainProducts/32.jpg'),
			array('catId' => '36', 'name' => 'Обои', 'img' => '/img/mainProducts/36.jpg'),
			array('catId' => '40', 'name' => 'Межкомнатные двери', 'img' => '/img/mainProducts/40.jpg'),
			array('catId' => '42', 'name' => 'Паркет', 'img' => '/img/mainProducts/42.jpg'),
			array('catId' => '50', 'name' => 'Настенные светильники', 'img' => '/img/mainProducts/50.jpg'),
			array('catId' => '53', 'name' => 'Настольные светильники', 'img' => '/img/mainProducts/53.jpg'),
			array('catId' => '59', 'name' => 'Керамическая плитка', 'img' => '/img/mainProducts/59.jpg'),
			array('catId' => '61', 'name' => 'Кухонные гарнитуры', 'img' => '/img/mainProducts/61.jpg'),
			array('catId' => '65', 'name' => 'Детские комнаты', 'img' => '/img/mainProducts/65.jpg'),
			array('catId' => '66', 'name' => 'Детские кровати', 'img' => '/img/mainProducts/66.jpg'),
			array('catId' => '72', 'name' => 'Зеркала', 'img' => '/img/mainProducts/72.jpg'),
			array('catId' => '74', 'name' => 'Настенные часы', 'img' => '/img/mainProducts/74.jpg'),
			array('catId' => '81', 'name' => 'Смесители для раковины', 'img' => '/img/mainProducts/81.jpg'),
		);

		$rndKeys = array_rand($arrProducts, 2);
		?>



		<?php if($randomProd['product'] && $this->beginCache('MainPage::promoCatalog_2', array('duration'=>3600, 'varyByExpression' => $randomProd["product"]->id ))) { ?>

		<div class="catalog_items item_list">
			<div class="big item">
				<?php //; ?>
				<?php if ($randomProd['product']) : ?>
				<a href="<?php echo $randomProd['product']->getLink($randomProd['product']->id);?>">
					<img src="<?php echo '/'.$randomProd['unit']->image_path;?>" width="416" />
				</a>
				<div class="item_desc">
					<a class="item_head" href="<?php echo $randomProd['product']->getLink($randomProd['product']->id);?>"><?php echo $randomProd['product']->name;?></a><br>
					<a href="<?php echo $randomProd['product']->vendor->getLink($randomProd['product']->vendor->id);?>"><?php echo $randomProd['product']->vendor->name;?></a>
				</div>
				<?php endif; ?>
			</div>

			<div class="items_column">
				<?php foreach($rndKeys as $key) { ?>
					<div class="item">
						<a href="<?php echo Yii::app()->createUrl('/catalog/category/list', array('id' => $arrProducts[$key]['catId']));?>">
							<img src="<?php echo $arrProducts[$key]['img'];?>" width="160" alt="" />
						</a>
						<div class="item_desc">
							<?php echo CHtml::link(
							$arrProducts[$key]['name'],
							Yii::app()->createUrl('/catalog/category/list', array('id' => $arrProducts[$key]['catId'])),
							array('class' => 'item_head')
						); ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php $this->endCache(); } ?>
		<div class="clear"></div>
	</div>
</div>