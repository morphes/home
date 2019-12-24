<?php
Yii::import('application.modules.admin.models.IndexProductTab');
Yii::import('application.modules.admin.models.IndexProductPhoto');
Yii::import('application.modules.admin.models.IndexProductBrand');
Yii::import('application.modules.catalog2.models.*');
?>

<?php if (!Yii::app()->request->isAjaxRequest) { ?>

    <!-- promo block controls -->
    <div class="-grid-container index-promo-controls tab-controls" id="main-promo">
        <div class="-grid-wrapper">
            <div class="-grid">
                <div class="-col-9 -gutter-bottom-dbl -inset-bottom-hf">
                    <h1 class="-gutter-null">
                        <?php // --- Включаемая область ---
                        $this->widget('application.components.widgets.WIncludes', array('key' => 'main_products_for_home'));
                        //количество дизайнеров с учетом склонения
                        $countGoods = 0;
                        $countGoods = (int)Product::countAll(true, false, false);
                        //получаем склонение с учетом числа
                        $goodsWordDeclension = CFormatterEx::formatNumeral($countGoods,array('товар','товара','товаров'),true);
                        ?>

                        <div class="-drop-right">
                            <a href="/products"><?php echo number_format($countGoods, 0, '.', ' ');?>
                                <?php echo $goodsWordDeclension;?>
                            </a>
                            <span></span>
                        </div>
                    </h1>
                </div>1
                <div class="-col-12 -gutter-bottom-null">
                    <!-- promo block tab menu -->
                    <ul class="-menu-inline -tab-menu">

                        <?php // --- ВКЛАДКИ ---
                        foreach (IndexProductTab::getActiveTabs() as $index => $tab) { ?>
                            <?php
                            if (!isset($activeTabId) && $index == 0) {
                                $activeTabId = $tab['id'];
                            }
                            $cls = ($index == 0) ? 'class="current"' : '';

                            $index++;
                            ?>
                            <li data-url="<?php echo $tab['url'];?>" <?php echo $cls;?>><span class="-acronym -gray"><?php echo $tab['name'];?></span></li>
                        <?php } ?>
                    </ul>
                    <!-- eof promoblock tab menu -->
                </div>
            </div>
        </div>
    </div>
    <!-- eof promo block controls -->

<?php } ?>



<div class="-grid-container index-promo tab-content">
    <div class="-grid-wrapper -gutter-top-dbl">
        <div class="-grid -inset-top-dbl -inset-bottom">
            <!-- slider -->
            <div class="-col-7 -slider">
                <ul class="-slider-content">
                    <?php if (isset($activeTabId)) { ?>

                        <?php // --- БОЛЬШИЕ ФОТОГРАФИИ --- ?>
                        
                        <?php foreach (IndexProductPhoto::getBigPhotos($activeTabId) as $item) {
                            if($item['url']) {
                                $link = $item['url'];
                            } else {
                                $link = Product::getLink($item->product_id);
                            }
                            ?>
                            <!-- slide 1 -->
                            <li class="-slide">
                                <a href="<?php echo $link?>">
                                    <img width="540"
                                                                  height="390"
                                                                  src="<?php echo $item->getImageFullPath();?>"
                                                                  alt="<?php echo $item->name;?>"
                                                                  class="-rect-title"></a>

                                <div class="thumb-overlay">
                                    <a class="-giant -block -gutter-bottom-hf"
                                       href="<?php echo $link; ?>"><?php echo $item->name;?></a>
                                    <?php if ($item->price > 0) { ?>
                                        <span class="-large -inline -strong -gutter-null"><?php echo number_format($item->price, 0, '.', ' ');?> руб.</span>
                                    <?php } ?>
                                </div>
                            </li>
                            <!-- eof slide 1 -->
                        <?php } ?>

                    <?php } ?>
                </ul>
                <!-- slider controls -->
                <?php if (isset($activeTabId)) { ?>
                    <div class="-slider-controls">
                        <span class="-slider-prev -disabled"></span>
                        <span class="-slider-next <?php if (count(IndexProductPhoto::getBigPhotos($activeTabId)) == 1) echo '-disabled';?>"></span>
                    </div>
                <?php } ?>
                <!-- eof slider controls -->
            </div>
            <!-- eof slider -->
            <div class="-col-2 -gutter-bottom-null">
                <!-- promo block menu -->
                <?php if (isset($activeTabId)) { ?>
                    <ul class="-menu-block">

                        <?php // --- РУБРИКИ --- ?>
                        <?php foreach (IndexProductTab::getActiveRubrics2($activeTabId) as $rubric) { ?>
                            <li><a href="<?php echo $rubric['url']; ?>"><?php echo $rubric['name'];?></a></li>
                        <?php } ?>

                        <li><a class="-pointer-right -red" href="<?php echo IndexProductTab::model()->findByPk($activeTabId)->url;?>">Все категории</a></li>
                    </ul>
                <?php } ?>
                <!-- eof promo block menu -->
            </div>
            <!-- small goods thumbnails -->
            <div class="-col-3">
                <?php if (isset($activeTabId)) { ?>

                    <?php // --- МАЛЫЕ ФОТОГРАФИИ --- ?>
                    <?php $i = 0; ?>
                    <?php foreach (IndexProductPhoto::getSmallPhotos($activeTabId) as $photo) { ?>
                        <?php ( $i === 0 ) ? $addClass = ' -gutter-bottom-dbl' : $addClass = ''; ?>
                        <div class="-relative">
                            <a class="-block" href="<?php echo Product::getLink($photo->product_id);?>">
                                <img src="<?php echo $photo->getImageFullPath();?>" class="-rect-huge<?php echo $addClass; ?>">
                                <div class="-gutter-null thumb-overlay">
                                    <span class="-block -hidden -underline"><?php echo $photo->name;?></span>
                                    <?php if ($photo->price > 0) { ?>
                                        <span class="-large -strong"><?php echo number_format($photo->price, 0, '.', ' '); ?> руб.</span>
                                    <?php } ?>
                                </div>
                            </a>
                        </div>
                        <?php $i++; ?>
                    <?php } ?>

                <?php } ?>
            </div>
            <!-- eof small goods thumbnails -->


            <?php
            /*
            Определяем текущий город по гео.
              */
            $geoCity = Yii::app()->user->getSelectedCity();
            if ( !$geoCity ) {
                $geoCity = Yii::app()->user->getDetectedCity();
            }

            if ($activeTabId) {
                $brands = IndexProductBrand::getActiveBrands($activeTabId, true, $geoCity->id, 5);
            }
            ?>

            <?php if (isset($brands) && !empty($brands)) { ?>

                <!-- vendor logos -->
                <!--				<div class="-col-7 -gutter-bottom-dbl -inset-top -gutter-top">-->
                <!--					<ul class="-menu-inline vendor-logos">-->
                <!--						--><?php //// --- ЛОГОТИПЫ --- ?>
                <!--						--><?php //foreach ($brands as $brand) { ?>
                <!--							<li><a class="-relative" href="--><?php //echo $brand['url'];?><!--" title="--><?php //echo $brand['name'];?><!--"><img alt="--><?php //echo $brand['name'];?><!--" src="--><?php //echo $brand['srcImage'];?><!--"></a></li>-->
                <!--						--><?php //} ?>
                <!--					</ul>-->
                <!--				</div>-->
                <!--				<div class="-col-2 -inset-top-dbl -gutter-top">-->
                <!--					<a class="-pointer-right" href="--><?php //echo $this->createUrl('/catalog/stores');?><!--">Все магазины</a>-->
                <!--				</div>-->
                <!--				<div class="-col-3 -gutter-top -inset-top-hf">-->
                <!--					--><?php //if ($geoCity->id == City::ID_NSK) : ?>
                <!--						<!-- banner -->-->
                <!--						<a href="--><?php //echo Yii::app()->params['bmHomeUrl'];?><!--"><img src="/img-new/banner/index-bm-products.png" width="220" height="54" alt="ТВК «Большая Медведица»"></a>-->
                <!--						<!-- eof banner -->-->
                <!--					--><?php //endif; ?>
                <!--				</div>-->

                <!-- eof vendor logos -->

            <?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    CCommon.tabs($('#main-promo'));
    CCommon.grayscale($('.vendor-logos a'));
    CCommon.slider($('.-slider'));
</script>