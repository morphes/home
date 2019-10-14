

<h1>Акция, архитекторы</h1>

<div class="well">
        <input type="button" class="btn" value="Только победители" onclick="window.location = 'juneArchitect?show_winners=1'">&nbsp;
        <input type="button" class="btn" value="Все"  onclick="window.location = 'juneArchitect?show_winners=0'">
</div>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView',
        array(
                'dataProvider'=>$dataProvider,
                'columns'=>array(
                        array(
                                'name'=>'ID',
                                'value' => '$data["id"]'
                        ),
                        array(
                                'name'=>'Логин',
                                'type'=>'raw',
                                'value' => '$data["login"]',
                        ),
                        array(
                                'name'=>'ФИ',
                                'value' => '$data["firstname"]." ".$data["lastname"]'
                        ),
                        array(
                                'header'=>'Победитель',
                                'class'=>'CCheckBoxColumn',
                                'checkBoxHtmlOptions'=>array('class'=>'winnerCheckbox'),
                                'checked'=>'$data["june_archit_winner"]',
                                'selectableRows'=>2,
                        ),
                )
        )
);

?>

<?php Yii::app()->clientScript->registerScript('winnerCheck', '
        $(".winnerCheckbox").live("change", function(){
                var winnerId = $(this).val();
                var value = 0;
                if($(this).attr("checked"))
                        value = 1;

                $.ajax({
                        url: "juneArchitectWinner",
                        data: {uid : winnerId, value: value},
                        dataType: "json",
                        success: function(response) {
                                if(response.result != true)
                                        alert("Ошибка");
                        }
                });
        });
', CClientScript::POS_READY);?>