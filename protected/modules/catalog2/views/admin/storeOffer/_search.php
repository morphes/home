<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>


	<?php echo $form->textFieldRow($model,'company',array('class'=>'span5','maxlength'=>50)); ?>

	<div class="clearfix">
		<label>Город</label>
		<div class="input">
			<?php
			$htmlOptions = array('size'=>'20');

			$htmlOptions['id'] = 'StoreOffer_city_name';
			$htmlOptions['class'] = 'span5';

			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'		=> 'StoreOffer[city_name]',
				'sourceUrl'	=> '/utility/autocompletecity',
				'value'		=> '',
				'options'	=> array(
					'showAnim'  => 'fold',
					'minLength' => 1
				),
				'htmlOptions'	=> $htmlOptions
			));
			?>
		</div>
	</div>


	<?php echo $form->textFieldRow($model,'status',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'create_time',array('class'=>'span5')); ?>


	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
