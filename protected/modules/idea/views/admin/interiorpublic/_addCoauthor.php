<?php 
	if (!empty($errors))
		$coauthor->addErrors($errors);
?>



<div class="add_coautor">
	<label>ФИО или название компании соавтора проекта</label>
	<?php
	$htmlOptions = array('class' => 'textInput');
	if (isset($errors['name']))
		$htmlOptions['class'] = $htmlOptions['class'].' error';
	$this->widget('application.components.widgets.CAjaxAutoComplete', array(
		'name'		=> 'Coauthor['.$coauthor->id.'][name]',
		'id'		=> 'coauthor_'.$coauthor->id,
		'value'		=> $coauthor->name,
		'sourceUrl'	=> '/utility/autocompleteuser',
		'options'	=> array(
			'showAnim'	=> 'fold',
			'minLength'	=> 2,
			'change'	=> 'js:function(event, ui){
				if (ui.item == null) {
					$(".add_coautor [name=\"Coauthor['.$coauthor->id.'][user_id]\"]").val("");
				}
			}',
			'select'=>'js:function(event, ui) {
				$(".add_coautor [name=\"Coauthor['.$coauthor->id.'][user_id]\"]").val(ui.item.id);
				$(".add_coautor [name=\"Coauthor['.$coauthor->id.'][url]\"]").val(ui.item.profileLink);
			}',
			'type'		=> 'js:function(){ }',
		),
		'htmlOptions' => $htmlOptions
	));?>

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
	<div class="del_cover"><i></i><a class="del_coautor" data-value="<?php echo $coauthor->id;?>" href="#">Удалить</a></div>

	<?php echo CHtml::activeHiddenField($coauthor, '['.$coauthor->id.']user_id', array('value' => $coauthor->user_id));?>

	<div class="spacer-18"></div>
</div>