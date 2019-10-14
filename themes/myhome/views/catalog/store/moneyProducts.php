<?php
$this->pageTitle = 'Товары — ' . $store->name;

/** @var $cs CustomClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerCssFile('css-new/generated/goods.css');
$cs->registerScriptFile('js-new/minisite.js');
?>

<div class="-grid-wrapper page-content">
	<div class="-grid">
		<h2 class="-col-12">Товары</h2>
		<div class="-col-3">


			<script>
				$(function(){
					$('#toggleMenu > li').on('click', 'span', function(){
						var li = $(this).parent();
						li.siblings().add(li).toggleClass('current');
						$('.page-sidebar > div').toggleClass('-hidden');
					});
				});
			</script>
			<?php
			/* -----------------------------------------------------
			 *  Вывод табочек с навигацией
			 *  "Категории" / "Производитель"0
			 * -----------------------------------------------------
			 */
			?>
			<ul class="-menu-inline -tab-menu" id="toggleMenu">
				<li class="-push-left <?php if ($navType == 'category') echo 'current';?>"><span class="-acronym">Категории</span></li>
				<li class="-push-right <?php if ($navType == 'vendor') echo 'current';?>"><span class="-acronym">Производители</span></li>
			</ul>
			<div class="-tinygray-box page-sidebar">
				<div <?php if ($navType == 'vendor') echo 'class="-hidden"';?>>
					<ul class="-menu-block">
						<?php echo $navListCategory; ?>
					</ul>
				</div>
				<div <?php if ($navType == 'category') echo 'class="-hidden"';?>>
					<ul class="-menu-block">
						<?php echo $navListVendor; ?>
					</ul>
				</div>
			</div>


		</div>
		<div class="-col-9">


			<?php echo CHtml::beginForm('', 'post', array('id'=>'refresh-form')); ?>

			<div class="-grid page-controls-top">

				<!--<div class="-col-wrap layout-icons">
					<span data-layout="1" class="-icon-layout-s <?php /*if ($viewType==1) echo 'current'; */?>"></span>
					<span data-layout="2" class="-icon-layout-small-s <?php /*if ($viewType==2) echo 'current'; */?>"></span>
				</div>-->

				<input type="hidden" id="layout" value="1" name="view_type">
				<script>
					$(function(){
						$('.layout-icons span:not(.current)').click(function(){
							var form = $('#refresh-form');
							var layout = form.find('#layout');
							layout.val($(this).data('layout'));
							form.submit();
						});
					});
				</script>

				<div class="-col-wrap -small sorting">
					<span class="-gray">Показать товары:</span>
					<a href="<?php echo Yii::app()->createUrl('catalog/store/moneyProducts', array('show' => 'all') + $_GET);?>" class="-gutter-left-qr -acronym <?php if ($showType != 'discount') echo 'current';?>">все</a>
					<a href="<?php echo Yii::app()->createUrl('catalog/store/moneyProducts', array('show' => 'discount') + $_GET);?>" class="-gutter-left-qr -acronym <?php if ($showType == 'discount') echo 'current';?>">со скидкой</a>
				</div>

				<!-- sorting -->
<!--				<div class="-col-wrap -small sorting">-->-->
<!--					<span class="-gray">Сортировать по </span>-->
<!--					<span class="-sort -sort-desc -sort-active" data-fieldname='1' data-order='asc'><a class="-acronym">дате</a></span>-->
<!--				</div>-->

				<?php
				/* ---------------------------------------------
				 *  Соритровка
				 * ---------------------------------------------
				 */
				?>
				<?php $this->widget('catalog.components.widgets.CatFilterSortGrid', array(
					'cookieName'   => 'product_store_sort',
					'formSelector' => '#refresh-form',
					'items'        => array(
						array('name' => 'date', 'text' => 'дате'),
					),
				)); ?>

				<script>
					//записываем в куки поле и порядок сортировки
					$('.sorting .-sort a').click(function(){
						var data = $(this).data();
						CCommon.setCookie("product_store_sort", data.sort,{"expires":1800,"path":"\/"});
						$('#refresh-form').submit();
						return false;
					});
				</script>
				<!-- eof sorting -->




				<?php
				/* ---------------------------------------------
				 *  Постраничка
				 * ---------------------------------------------
				 */
				?>
				<div class="-col-wrap -push-right">
					<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $dataProvider->pagination,
						'htmlOptions'    => array('class' => '-menu-inline -inline -pager'),
						'maxButtonCount' => 5,
					)); ?>
				</div>
			</div>

			<?php echo CHtml::endForm(); ?>

			<?php
			/* -----------------------------------------------------
			 *  Список товаров
			 * -----------------------------------------------------
			 */
			echo CHtml::openTag('div', array(
				'class' => ($viewType == 2)
					? 'goods-list-s -grid'
					: 'goods-list-xl -grid'
			));

			$data = $dataProvider->getData();

			if (!empty($data)) {
				if ($viewType == 2) {
					// Трех-колоночный вариант
					$qt = count($data);
					foreach ($data as $index => $item) {
						$this->renderPartial('//catalog/category/_product3colGrid', array(
							'data'     => $item,
							'index'    => $index,
							'qt'       => $qt,
							'store_id' => $store->id
						));
					}

				} else {
					// Двух колоночный вариант
					foreach ($data as $item) {
						$this->renderPartial(
							'//catalog/category/_product2colGrid',
							array(
								'data'     => $item,
								'store_id' => $store->id,
								'class'    => '-col-wrap'
							)
						);
					}
				}
			} else {

				?>
				<div class="-col-9"><p class="-large -gutter-top">
					К сожалению в нашем каталоге нет товаров, удовлетворяющих вашему запросу.
					</p></div>
				<?php
			}

			echo CHtml::closeTag('div');

			?>



			<div class="-grid page-controls-bottom">
				<?php
				/* ---------------------------------------------
				 *  Постраничка
				 * ---------------------------------------------
				 */
				?>
				<div class="-col-wrap -push-right">
					<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $dataProvider->pagination,
						'htmlOptions'    => array('class' => '-menu-inline -inline -pager'),
						'maxButtonCount' => 5,
					)); ?>
				</div>
			</div>


		</div>
	</div>
</div>