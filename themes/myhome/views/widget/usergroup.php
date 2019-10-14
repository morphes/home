<?php
Yii::app()->clientScript->registerScript('usergroup-checkboxes',
   '$(".usergroup-checkbox").click(function(){
        $.ajax({
            url: "'.$ajaxUrl.'", 
            data: { group_id: $(this).attr("group_id"), user_id: '.$user->id.', append: $(this).is(":checked") },
            dataType: "json",
            success: function(data) {
                switch(data.result)
                {
                    case "appended":
                        $("#label_group_"+data.gid).css("color", "black");break;
                    case "deleted":
                        $("#label_group_"+data.gid).css("color", "#555555");break;
                    default:
                        alert("error");
                }
            },
        });   
   });'
);
?>
<h4>Группы пользователя</h4>


        
<?php echo CHtml::beginForm();?>

	<ul class="inputs-list">
	<?php foreach($groups as $group): ?>

		<?php $checked = $group->isChecked($user->id); ?>
		<?php $color = $checked ? $checkedColor : $uncheckedColor; ?>
		<li>
			<label style="display: inline-block;">
				<?php echo CHtml::checkBox("group_{$group->id}", $checked, array('class'=>'usergroup-checkbox', 'group_id'=>$group->id));?>
				<?php echo CHtml::tag('span', array('id'=>"label_group_{$group->id}", 'for' => "label_group_{$group->id}"), $group->name, true)?>
			</label>
		</li>

	<?php endforeach;?>
	</ul>

<?php echo CHtml::endForm();?>        