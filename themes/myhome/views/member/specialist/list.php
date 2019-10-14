<?php
/**
 * @var $city City
 */
// SEO параметры
// title = Услуга — Город — MyHome.ru
// keywords =

$serviceName = ($service instanceof Service) ? $service->name : 'Специалисты';
if ( ($city instanceof City) )
{
	$serviceName = ($service instanceof Service) ? $service->name : 'Специалисты';
	$this->pageTitle = $serviceName.' — '.$city->name.' — MyHome.ru';
	$this->keywords = mb_strtolower($serviceName.' '.$city->name.', '.$serviceName.', ремонт и благоустройство, '.$city->name.', майхоум, myhome, май хоум, myhome.ru', 'UTF-8');
	$this->description = $serviceName.' в '.$city->prepositionalCase.': онлайн каталог фирм и специалистов '.$city->genitiveCase.' с рейтингом, отзывами, примерами работ. Сортировка специалистов по параметрам';
}
else
{
	$serviceName = ($service instanceof Service) ? $service->name : 'Специалисты';
	$this->pageTitle = $serviceName.' — Каталог фирм и специалистов — MyHome.ru';
	$this->description = $serviceName.' — лучшие специалисты и компании России, Украины и стран ближнего и дальнего зарубежья на MyHome.ru';

	$service_name = mb_strtolower($serviceName, 'UTF-8');
	$this->keywords = $service_name.' специалисты, '.$service_name.' фирмы, '.$service_name.' компании, '.$service_name.', каталог специалистов, каталог фирм, майхоум, myhome, май хоум, myhome.ru';
}

/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerCssFile('/css-new/generated/spec.css');
$cs->registerScriptFile('/js-new/spec.js');
$cs->registerScriptFile('/js-new/scroll.js');

// SEO

//3 538 специалистов по ремонту квартиры, коттеджа под ключ в Новосибирске

	$nameService = 'Специалисты';
	if ( $service !== null ) {
		$nameService = $service->name;

	}

	if ( ($city instanceof City)) {
		$prepCase = $city->prepositionalCase;
		if ($prepCase)
			$nameService .= ' в '.$prepCase;
	}

	$wSeo = $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
		'renderH1'  => false,
		'defaultH1' => $nameService,
	));

// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

if ($service instanceof Service) {
	Yii::app()->openGraph->title = $service->name;
	Yii::app()->openGraph->description = $service->desc;
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';
	$serviceId = $service->id;
} else {
	Yii::app()->openGraph->title = 'Специалисты';
	Yii::app()->openGraph->description = 'База помощников в благоустройстве дома: ведущие дизайнеры, архитекторы и прорабы со всей страны';
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';
	$serviceId = null;
}
Yii::app()->openGraph->renderTags();
?>

<div class="-grid-wrapper page-content -gutter-top">
	<div class="-grid">
		<!-- Right sidebar //-->
		<div class="-col-3">
			<div class="-tinygray-box">
				<ul class="-menu-tree -menu-block">
					<li class="level1 <?php if ($service===null) echo 'current'; ?>"><a href="/specialist">Все услуги</a></li>
					<?php
					$lastId = null;
					/** @var $item Service */
					foreach ($services as $key => $item) {
						$htmlOptions = array('class'=>'');
						if ($item->parent_id==0) {
							$htmlOptions['class'] .= 'level1';
//							$htmlOptions['onclick'] = 'return false;';
							$url = '#';
						} else {
							$htmlOptions['class'] .= 'level2';

							$options = array('service'=>$item->url);
							if ($city instanceof City) {
								$options['city'] = $city->eng_name;
							}
							$url = $this->createUrl('/member/specialist/list', $options);
						}

						if ($service !== null && $item->id == $service->parent_id) {
							$htmlOptions['class'] .= ' current';
						}


						echo CHtml::openTag('li', $htmlOptions);

                        $title = $item->name . (isset($city->prepositionalCase) ?  ' в ' . $city->prepositionalCase : '');
						echo CHtml::link($item->name, $url, array('title'=>$title));
						// для 2го уровня
						if ($item->parent_id != 0) {
							echo CHtml::closeTag('li');
							if ( !isset($services[$key+1]) || $services[$key+1]->parent_id==0 ) {
								echo CHtml::closeTag('ul');
								echo CHtml::closeTag('li');
							}
						} else { // 1 level
							if ( !isset($services[$key+1]) || $services[$key+1]->parent_id!=0 ) {
								echo CHtml::openTag('ul');
							}

							if ( !isset($services[$key+1]) || $services[$key+1]->parent_id==0 ) {
								echo CHtml::closeTag('li');
							}
						}

						echo CHtml::closeTag('li');
					}
					?>
				</ul>

			</div>

			<div class="-gutter-top -gutter-bottom -relative specialist-banner">
				<?php $this->widget('application.components.widgets.banner.BannerWidget', array(
					'section'=>Config::SECTION_SPECIALIST,
					'type'=>2
				)); ?>
			</div>

			<div class="-gutter-top-dbl -gutter-right">
                <?php Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_vertical'); ?>
			</div>

			<div class="-gutter-top-dbl -gutter-right">
				<?php
				// Google adsense
				$this->renderPartial('//widget/google/adsense_120x600_specialist_list');
				?>
			</div>

			<script>
				spec.toggleServices();
			</script>
		</div>
		<!-- EOF Right sidebar //-->
	<!--	--><?php

//				));?>
		<!-- Main block //-->
		<div class="-col-9">
			<div class="page-title -gutter-bottom-dbl">
				<?php
				$linksArr = array(
					'<span class="-text-block">Лучшие компании, профессиональные</span> Специалисты' => array('/specialist'),
				);
				if ( ($city instanceof City) && $service) {
					$linksArr[$service->name] = Yii::app()->controller->createUrl('/member/specialist/list', array('service'=>$service->url));
					$linksArr[0] = $city->name;
				}
				$this->widget('application.components.widgets.GridBreadcrumbs', array(
					'links' => $linksArr,
					'encodeLabel' => false
				));
				?>
				<h1><?php echo $wSeo->getH1(); ?></h1>

				<?php if (isset($service->seo_top_desc) && $service->seo_top_desc) { ?>
					<p><?php echo nl2br($service->seo_top_desc);?></p>
				<?php } ?>
			</div>

			<div class="-gutter-bottom-dbl filter">
				<?php
				echo CHtml::form('/'.Yii::app()->getRequest()->getPathInfo(), 'get', array('class'=>'-form-inline -col-wrap spec-filter')); ?>
					<label>
						<input name="company" type="text" class="-col-wrap" value='<?php echo $name; ?>' placeholder="Имя или название компании">
					</label>
					<span class="-col-wrap">
						сортировать
						<?php echo CHtml::dropDownList('sorttype', $sortType, Config::$specSortNames, array('class'=>'sort')); ?>
					</span>
					<input type="submit" class="-button -button-skyblue" value="Найти">
				<?php echo CHtml::endForm(); ?>
				<?php

				// если в УРЛ услуги есть слеш, то смещаем позицию города в УРЛ
				if ( $service && strpos($service->url, '/') ) {
					$cityUrlPos = 4;
				} elseif ( $service ) {
					$cityUrlPos = 3;
				} else {
					$cityUrlPos = 2;
				}

				$this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
					'city'       => $city,
					'cityUrlPos' => $cityUrlPos,
					'cookieName' => Geoip::COOKIE_GEO_SELECTED,
					'renderHtml' => true,
					'changeText' => 'Будут показаны специалисты вашего города',
					'htmlOptions' => array('class'=>'-push-right -col-wrap city-selector'),
				));
				?>
				<script>
					spec.toggleFilter();
				</script>
			</div>



			<div class="list">
				<?php
				$data = $specProvider->getData();
				$paidData = array();
				if($specProviderPaid) {
					$paidData = $specProviderPaid->getData();
					shuffle($paidData);
				}
				$paidSpecialist = reset($paidData);

				if (!empty($data)) {
					foreach ($data as $spec) {
						$paidStatus = false;
						StatSpecialist::hit($spec->id,StatSpecialist::TYPE_SHOW_PROFILE_IN_LIST);
						StatUserService::hit($spec->id, $serviceId, $cityId, StatUserService::TYPE_SHOW_PROFILE_SERVICE );
						$this->renderPartial('_gridListItem', array(
							'spec'=>$spec,
							'serviceId'=>$serviceId,
							'cityId'=>$cityId,
							'city'=>$city,
							'service'=>$service,
							'paid' =>$paidStatus,
						));
						if($paidSpecialist && $specProvider->pagination->currentPage==0 ) {
							$paidStatus = true;
							StatSpecialist::hit($paidSpecialist->id,StatSpecialist::TYPE_SHOW_PROFILE_IN_LIST);
							StatUserService::hit($paidSpecialist->id, $serviceId, $cityId, StatUserService::TYPE_SHOW_PROFILE_SERVICE );
							$this->renderPartial('_gridListItem', array(
								'spec'=>$paidSpecialist,
								'serviceId'=>$serviceId,
								'cityId'=>$cityId,
								'city'=>$city,
								'service'=>$service,
								'paid' =>$paidStatus,
							));
							$paidSpecialist = next($paidData);
						}
					}
				} else {
					$url = ($service instanceof Service) ? Yii::app()->getController()->createUrl('/member/specialist/list', array('service'=>$service->url)) : '/specialist';
					$emptyText = 'У нас пока что нет специалистов, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="'.$url.'">сбросить параметры фильтра</a>';
					echo CHtml::tag('div', array('class'=>'no_result'), $emptyText);
				}
				?>
                <div class="-gutter-top-dbl">
                    <?php Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under'); ?>
                </div>


            </div>
<!--			<a class="-small" href="#">Узнайте как попасть в этот список бесплатно</a>-->

            <div class="pagination" style="float: right; line-height: 2.5">
                <?php
                $this->widget('application.components.widgets.CustomPager2', array(
                    'pages' => $specProvider->getPagination(),
                    'maxButtonCount' => 7,
                    'prevPageLabel' => '<span class="arr">&larr;</span> Назад',
                    'nextPageLabel' => 'Далее <span class="arr">&rarr;</span>',
                    'htmlOptions' => array('class'=>'-col-wrap -pager'),
                    'newStyle' => true,
                ));
                ?>
            </div>



			<div class="-clear"></div>
			<?php if (isset($service) && $service->seo_bottom_desc) { ?>
				<p class="bottom-desc"><?php echo nl2br($service->seo_bottom_desc);?></p>
			<?php } ?>




		</div>
		<!-- EOF Main block //-->
	</div>
	<script>
		CCommon.initPayment();
	</script>
	<div class="-col-wrap -hidden" id="pay-form">

	</div>

</div>
