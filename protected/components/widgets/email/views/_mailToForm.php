<hr>

<div class='clearfix'>
        <?php echo CHtml::label('E-mail', 'email_template_test_to')?>
        <div class="input">
                <?php echo CHtml::textField('email_template_test_to'); ?>
        </div>

</div>

<div class='clearfix'>
	<?php echo CHtml::label('Использовать layout', 'layout'); ?>
	<div class="input">
		<?php echo CHtml::checkBox('layout', true, array('id'=>'email_template_test_layout')); ?>
	</div>
</div>

<div class='clearfix'>
	<div class="input">
		<?php echo CHtml::button('Отправить', array('id'=>'email_template_test_send', 'class'=>'btn')); ?>
	</div>
</div>


<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>

<?php Yii::app()->clientScript->registerScript('email_template_test', '
        $("#email_template_test_send").click(function(){
                var to = $("#email_template_test_to").val();

                if ($("#email_template_test_layout").is(":checked"))
			var layout = 1;
		else
			var layout = 0;


                $.ajax({
                        async: false,
                        dataType: "json",
                        type: "POST",
                        url: "' . $action_url . '",
                        data: {mail_to: to, template_key: "' . $template_key . '", layout: layout},
                        success: function(response) {
                                alert(response.text);
                                $("#email_template_test_to").val("");
                        }
                });
        });
', CClientScript::POS_READY); ?>

<hr>