<tr class="place-item">
	<td class="clearfix">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> $place->getCityName(),
			'sourceUrl'	=> '/utility/autocompletecity',
			'value'		=> $place->city_id,
			'options'	=> array(
				'showAnim'  =>'fold',
				'minLength' => 3
			),
			'htmlOptions'	=> array('id'=>'place-id'.$place->id, 'name'=>'MediaEventPlace['.$place->id.'][city_id]')
		));
		?>
	</td>
	<td class="clearfix">
		<?php echo CHtml::activeTextField($place, 'name', array('maxlength'=>512, 'name'=>'MediaEventPlace['.$place->id.'][name]')); ?>
	</td>
	<td class="clearfix">
		<?php echo CHtml::activeTextField($place, 'address', array('maxlength'=>512, 'name'=>'MediaEventPlace['.$place->id.'][address]')); ?>
	</td>
	<td class="clearfix">
		<?php echo CHtml::activeTextField($place, 'event_time', array('maxlength'=>255, 'name'=>'MediaEventPlace['.$place->id.'][event_time]')); ?>
	</td>
	<td class="clearfix">
		<?php echo CHtml::button('Удалить', array('class'=>'btn danger', 'onclick'=>"removePlace(this, {$place->id})"));?>
	</td>
</tr>