<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css')?>

<input type="hidden" id="store_id" value="<?php echo $store->id;?>">

<div class="-grid">

	<div class="-col-4 -gutter-top-dbl -gutter-bottom-dbl">
		<span class="-huge -semibold">Статистика магазина за период</span>
	</div>
	<div class="-col-8 -gutter-top-dbl -gutter-bottom-dbl">
		<div class="stat-period _error">
			<input type="hidden" name="dateFrom" value="">
			<input type="text" class="-col-2 -large -gutter-null first-day"><i class="-icon-calendar -icon-gray -icon-only toggle-calendar"></i>
			<span class="-large -gray -inset-left-hf -inset-right-hf">—</span>
			<input type="hidden" name="dateTo" value="<?php echo time();?>">
			<input type="text" class="-col-2 -large -gutter-null last-day"><i class="-icon-calendar -icon-gray -icon-only toggle-calendar"></i>
		</div>
		<span class="-hidden"><i class="-icon-alert -icon-red"></i>Диапазон дат указан неверно</span>
	</div>

	<?php $this->renderPartial('//catalog2/profile/_statistic', array('stat' => $stat)); ?>

	<script>
		store.statDate();
	</script>

</div>