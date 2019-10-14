<?php if (!empty($services)) : ?>
<ul>
	<li class="checkall"><input type="checkbox" /><a href="#">Все услуги</a></li>
	<?php
		foreach ($services as $service) {
			echo CHtml::tag('li', array(), 
				CHtml::checkBox('Tender[service]['.$service->id.']', false).CHtml::link($service->name)
				);
		}
	 ?>
</ul>
<div class="clear"></div>
<?php endif; ?>