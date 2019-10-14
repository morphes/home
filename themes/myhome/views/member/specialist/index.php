<?php $this->pageTitle = 'Специалисты — MyHome.ru'?>
<?php Yii::app()->getClientScript()->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js'); ?>
<?php Yii::app()->getClientScript()->registerCssFile('/css/jquery-ui-1.8.18.custom.css'); ?>
<?php Yii::app()->getClientScript()->registerScript('initial', 'js.initSpecList();', CClientScript::POS_READY)?>

<?php $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags'); ?>


<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Специалисты';
Yii::app()->openGraph->description = 'База помощников в благоустройстве дома: ведущие дизайнеры, архитекторы и прорабы со всей страны';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';

Yii::app()->openGraph->renderTags();
?>


<div class="pathBar">
        <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
                'links'=>array(),
        ));?>
	<div class="-grid">
		<div class="-col-8">
			<?php echo CHtml::tag('h1', array(), 'Специалисты' . (($city instanceof City)
					? ' в ' . ((empty($city->prepositionalCase))
						? 'городе ' . $city->name
						: $city->prepositionalCase)
					: '') . '<span  class="section_items_count">' . User::getSpecialistsQuantity($city
					? $city->id
					: null) . '</span>', true); ?>
		</div>

		<?php // <div class="-col-4"> зашит в виджете
		$this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
			'city'       => $city,
			'cityUrlPos' => 2,
			'cookieName' => Geoip::COOKIE_GEO_SELECTED,
			'renderHtml' => true,
			'changeText' => 'Будут показаны специалисты вашего города',
		));
		?>
	</div>
    <div class="spacer"></div>
</div>

<div class="services_list">

        <div class="specialist_search">
            <div class="search_containder">
                <input class="textInput textInput-placeholder" id="spec_autocomplete" value="Быстрый поиск услуг и специалистов" data-placeholder="Быстрый поиск услуг и специалистов"/>
                <input type="submit" value="&nbsp;" >
            </div>

            <?php // кешированный блок популярных услуг ?>
            <?php if($this->beginCache('specialist_service_list_popular_' . (!is_null($city) ? ($city->id) : ('all')), array('duration'=>1*60*60))) : ?>
                <?php $popServices = Yii::app()->db->createCommand()->select('id, name, url')
                          ->from('service')->order('position desc, id asc')->where('popular=:pop and parent_id<>0', array(':pop'=>Service::POPULAR_YES))->queryAll();?>
                <div class="popular_words">
                    <span>Популярные услуги:</span>
                    <?php foreach($popServices as $ps) : ?>
                        <?php if($city) : ?>
                                <?php echo CHtml::link($ps['name'], $this->createUrl('/specialist/' . $ps['url'] . '/' . $city->eng_name)); ?>
                        <?php else : ?>
                                <?php echo CHtml::link($ps['name'], $this->createUrl('/specialist/' . $ps['url'])); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php $this->endCache(); endif; ?>

        </div>


        <?php if($this->beginCache('specialist_service_list_' . (!is_null($city) ? ($city->id) : ('all')), array(
                'duration'=>1*5*60, // 5 минут
        ))) : ?>
                <?php $usersQt = ServiceUser::getUserQtByCity($city);?>
                <?php $headServices = Service::model()->findAll(array('condition'=>'parent_id=0', 'order'=>'position desc, id asc'));?>
                <?php $chunked_headServices = array_chunk($headServices, round(count($headServices) / 2)); ?>
                <?php $columnFirst = $chunked_headServices[0]; ?>
		<?php $columnLast = $chunked_headServices[1]; ?>
                <div class="services_content">
                    <div class="services_column first">
                        <?php $this->renderPartial('_serviceBlock', array('headServices'=>$columnFirst, 'usersQt'=>$usersQt, 'city'=>$city)); ?>
                    </div>
                    <div class="services_column">
                        <?php $this->renderPartial('_serviceBlock', array('headServices'=>$columnLast, 'usersQt'=>$usersQt, 'city'=>$city)); ?>
                    </div>
                </div>

        <?php $this->endCache(); endif; ?>



        <div class="search_content hide">
            <h2 class="block_head"></h2>
            <span class="back_to_services top">&larr;<a href="#">Вернуться к списку услуг</a></span>
            <div class="finded_items">
                <div class="search_content_specs">

                </div>
            </div>
            <div class="spacer-30"></div>
        </div>

</div>

<div class="-push-right specialist-right-column">

	<div class="find_spec">
		<h2>Не нашли специалиста?</h2>
		<i></i>
		<div class="find_spec_text">
			<a href="/tenders/create">Разместите заказ</a>,
			<p>и специалисты сами найдут вас!</p>
		</div>
	</div>

	<div class="-gutter-top -gutter-bottom -relative specialist-banner"><?php $this->widget('application.components.widgets.banner.BannerWidget', array(
		'section'=>Config::SECTION_SPECIALIST,
		'type'=>2
	)); ?></div>

	<?php $experts = User::getRandomExpert(5); ?>
	<?php if ($experts) { ?>
		<div class="best_specialists experts">
			<i></i><h2 class="block_head ">Эксперты</h2>
			<span class="all_elements_link">
				<a href="/experts">Все</a><span>→</span>
			</span>
			<ul class="">
				<?php // Выводим случайных список экспертов ?>
				<?php foreach ($experts as $expert) { ?>
				<li>
					<a title="<?php echo $expert->name;?>" href="<?php echo $expert->getLinkProfile();?>">
						<?php echo CHtml::image('/'.$expert->getPreview( Config::$preview['crop_45'] ), $expert->name, array('width' => 45, 'height' => 45));?>
						<?php echo $expert->name;?>
					</a>
					<span title="<?php echo $expert->data->expert_desc;?>"><?php echo $expert->data->expert_desc;?></span>
					<div class="clear"></div>
				</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>


	<?php // кешированный блок популярных специалистов ?>
	<?php if($this->beginCache('specialist_service_list_popular_users_' . (!is_null($city) ? ($city->id) : ('all')), array('duration'=>1*60*60))) : ?>
		<?php $bestSpecs = ServiceUser::getBestSpecs($city); ?>
		<?php $bestSpecs = array_slice($bestSpecs, 0, 5); ?>
		<div class="best_specialists">
		    <h2 class="block_head">Лучшие специалисты</h2>
		    <ul class="">
			<?php foreach($bestSpecs as $bspec) : ?>
			    <?php $user = User::model()->findByPk($bspec['uid']);
			    if (is_null($user))
				    continue;
			    ?>
			    <li>
				<a title="<?php echo $user->name; ?>" href="<?php echo $user->linkProfile;?>">
				    <?php echo CHtml::image('/'.$user->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45));?>
					<?php
						if (mb_strlen($user->name, 'utf-8') > 16)
							echo mb_substr($user->name, 0, 15, 'utf-8') . '...';
						else
							echo $user->name;
					?>
				</a>
				<span title="<?php echo $bspec['service_name']; ?>">
					<?php
						if (mb_strlen($bspec['service_name'], 'utf-8') > 21)
							echo mb_substr($bspec['service_name'], 0, 20, 'utf-8') . '...';
						else
							echo $bspec['service_name'];
					?>
				</span>
				<span class="city"><?php echo $user->city->name; ?></span>
				<div class="clear"></div>
			    </li>
			<?php endforeach; ?>
		    </ul>
		</div>
	<?php $this->endCache(); endif; ?>

</div>

<div class="clear"></div>