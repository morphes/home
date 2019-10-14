<?php foreach($this->fields as $key=>$name) : ?>

	<?php
	// Массив со значениями времени конретного вида
	$workTimeArray = unserialize($model->{$this->attrTime});
	if (isset($workTimeArray[$key]))
		$curTime = $workTimeArray[$key];
	else
		$curTime = array(
			'work_from'      => 0,
			'work_to'        => 0,
			'dinner_enabled' => false,
			'dinner_from'    => 0,
			'dinner_to'      => 0
		);



	$prefixId = get_class($model).'_'.$key;
	$prefixName = get_class($model).'['.$key.']';
	?>

	<div class="clearfix" id="<?php echo $prefixId.'-working-time';?>">

		<label><?php echo $name; ?></label>

		<div class="input"><div class="inline-inputs">

			<?php // ОСНОВНОЕ ВРЕМЯ
			echo 'c ';
			Yii::app()->controller->widget(
				'CMaskedTextField',
				array(
					'mask'        => '99:99',
					'placeholder' => '_',
					'completed'   => 'function(){$("#'.$prefixId.'_work_to").focus();}',
					'name'        => $prefixName.'[work_from]',
					'value'       => $curTime['work_from'],
					'htmlOptions' => array('class' => 'mini')
				)
			);

			echo ' до ';
			Yii::app()->controller->widget(
				'CMaskedTextField',
				array(
					'mask'        => '99:99',
					'placeholder' => '_',
					'completed'   => 'function(){$("#'.$prefixId.'_dinner_enabled").focus();}',
					'name'        => $prefixName.'[work_to]',
					'value'       => $curTime['work_to'],
					'htmlOptions' => array('class' => 'mini')
				)
			);
			?>

			<?php // ОБЕДЕННОЕ ВРЕМЯ
			echo ' Обед ';
			echo CHtml::hiddenField($prefixName.'[dinner_enabled]', false, array('id' => $prefixId.'_dinner_hidden'));
			echo CHtml::checkBox($prefixName.'[dinner_enabled]', $curTime['dinner_enabled']);

			$display = $curTime['dinner_enabled'] == true ? 'inline' : 'none';

			echo CHtml::openTag('span', array('id' => $prefixId.'_dinner', 'style' => "display: {$display};"));

				echo ' с ';
				Yii::app()->controller->widget(
					'CMaskedTextField',
					array(
						'mask'        => '99:99',
						'placeholder' => '_',
						'completed'   => 'function(){$("#'.$prefixId.'_dinner_to").focus();}',
						'name'        => $prefixName.'[dinner_from]',
						'value'       => $curTime['dinner_from'],
						'htmlOptions' => array('class' => 'mini')
					)
				);

				echo ' до ';
				Yii::app()->controller->widget(
					'CMaskedTextField',
					array(
						'mask'        => '99:99',
						'placeholder' => '_',
						//'completed'   => 'function(){$("#'.$prefixId.'_work_from").focus();}',
						'name'        => $prefixName.'[dinner_to]',
						'value'       => $curTime['dinner_to'],
						'htmlOptions' => array('class' => 'mini')
					)
				);

			echo CHtml::closeTag('span');
			?>
		</div></div>
	</div>

	<?php Yii::app()->clientScript->registerScript($prefixId.'-working-time', '
		$("#'.$prefixId.'_dinner_enabled").click(function(){
			if ($(this).is(":checked"))
				$("#'.$prefixId.'_dinner").show();
			else
				$("#'.$prefixId.'_dinner").hide();
		});
	', CClientScript::POS_READY);?>

<?php endforeach; ?>



