<?php 
	if (!empty($errors))
		$coauthor->addErrors($errors);
?>

<div class="add_coautor">
	<label>ФИО или название компании соавтора проекта</label>
	<?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
		'name'		=> 'Coauthor['.$coauthor->id.'][name]',
		'id'		=> 'coauthor_'.$coauthor->id,
		'value'	=> $coauthor->name,
		'sourceUrl'	=> '/utility/autocompleteuser',
		'options'	=> array(
			'showAnim'	=> 'fold',
			'minLength'	=> 2,
			'change'	=> 'js:function(event, ui){
						if (ui.item == null) {
							$(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][user_id]\"]").val("");
							$(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][specialization]\"]").removeAttr("readonly");
							$(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][url]\"]").removeAttr("readonly");
						}
					}',
			'select'=>'js:function(event, ui) {
						$(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][user_id]\"]").val(ui.item.id);
						$(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][specialization]\"]").attr("readonly", "readonly");
						$(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][url]\"]").attr("readonly", "readonly");
					}'
		),
		'htmlOptions' => array('class' => 'textInput')
	));
	?>

	<div class="clear"></div>
	<div class="coautor_role">
		<label>Роль в проекте</label>
		<?php echo CHtml::activeTextField($coauthor, '['.$coauthor->id.']specialization', array('class' => 'textInput')); ?>
	</div>
	<div class="coautor_site">
		<label>Ссылка на сайт</label>
		<?php echo CHtml::activeTextField($coauthor, '['.$coauthor->id.']url', array('class' => 'textInput')); ?>
	</div>
	<div class="clear"></div>
	<div class="del_cover"><i></i><a class="del_coautor" href="#" onclick="removeCoauthor(this,<?php echo $coauthor->id ?>); return false;">Удалить</a></div>

	<div class="spacer-18"></div>

	<?php echo CHtml::errorSummary($coauthor, '');?>

	<?php echo CHtml::activeHiddenField($coauthor, '['.$coauthor->id.']user_id', array('value' => $coauthor->user_id));?>
</div>