<script>
$(function(){
	if ($('.kapitel-list').length) {
		$('.kapitel-list input').unbind('click');

		$('.kapitel-list input').click(function(){
			var input = $(this);
			if (input.is(':checked')) {
				input.parent().addClass('kl-item-checked');
			} else {
				input.parent().removeClass('kl-item-checked');

				if ($('.pathBar .view a.active').find('.view-checked').length)
					input.parent().slideUp(200);
			};
			$('.pathBar .view .view-checked').text($('.kapitel-list .kl-item-checked').length);

			// запрос на смену статуса
			$.get('/idea/galaxytab/select/id/'+input.attr('data-id'), function(response){
				if ( ! response.success)
					alert("Произошла ошибка!\nСкорее всего ваши изменения не применятся.");
			}, 'json');
		});

	}
})
</script>



<div class="pathBar">
	<p class="path"><a href="/">Главная</a> <span class="arr">&rarr;</span></p>
	<h1>Золотая капитель</h1>
	
	<div class="view">
		<a href="#"><span>Выбранные проекты</span> (<em class="view-checked">0</em>)</a>
		<a href="#"><span>Все работы</span> (<em class="view-total">0/0</em>)</a>
		<!--<a href="#"><span>Капитель</span> (<em class="view-capitel">0</em>)</a>-->
	</div>
	<div class="spacer"></div>

</div>


<div class="kapitel-list">
	
	<?php
	// Кол-во выбранных работ.
	$totalSelected = 0;
	$totalAuthors = 0;
	$totalCapitelAuthors = 0;
	$prevAuthor = null;
	foreach($interiors as $val) { ?>

		<?php
		$data = Interior::model()->findByPk($val['interior_id']);
		if ( ! $data)
			continue;

		if ($data->author_id != $prevAuthor) {
			
			if ( ! is_null($prevAuthor))
				echo CHtml::closeTag('ul');
			
			$link = CHtml::link($data->author->name, $data->author->getLinkProfile(), array('target' => '_blank'));
			echo CHtml::tag('h2', array(), $link, true);
			
			echo CHtml::openTag('ul', array('class' => 'userWorks'));
			
			
			$prevAuthor = $data->author_id;
			
			$totalAuthors++;
		}

		$cls = array();
		$chk = '';
		// Если статус "Выбрано", то нужно подсветить работу.
		if (array_key_exists($data->id, $statusIdea)) {
			$cls[] = 'kl-item-checked';
			$chk = 'checked';
			$totalSelected++;
		}

		?>
	
		<li class="<?php echo implode(' ', $cls);?>">
			<input type="checkbox" <?php echo $chk;?> data-id="<?php echo $data->id;?>">
			<?php echo CHtml::image('/'.$data->getPreview(Config::$preview['crop_80']), '', array('class' => 'image', 'width' => '80', 'height' => '80')); ?>
			<div class="info">
				<h3><?php echo CHtml::link(($data->name) ? $data->name : 'без названия', $this->createUrl('/member/profile/interior', array('id' => $data->id)), array('target' => '_blank')); ;?></h3>
				<p class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy', $data->create_time);?></p>
				<p><?php echo implode(', ', array_values($data->roomsNames));?></p>
				<p class="kl-photos"><i></i><?php echo $data->count_photos;?></p>
			</div>
		</li>
		
	<?php } ?>
	
</div>

<?php
Yii::app()->clientScript->registerScript('capitelList', '
	$(function(){
		$divView = $("div.view");
		
		$divView.find(".view-total").trigger("click");

		$divView.find("em.view-checked").text("'.$totalSelected.'");
		$divView.find("em.view-total").text("'.$totalAuthors.' / '.count($interiors).'");
	})
');
?>