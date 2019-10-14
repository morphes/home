<?php 
        $colors = array('' => 'Выберите цвет') + CHtml::listData($colors, 'id', 'option_value');

        echo '<div id="additional-color-'.$content_id.'-'.$counter.'">';
        echo CHtml::activeLabel($color, "[{$content_id}][{$counter}]color_id");
        echo CHtml::activeDropDownList($color, "[{$content_id}][{$counter}]color_id", $colors);
        echo '<span style="cursor:pointer" onclick="remove_scc('.$content_id.','.$counter.')"> Удалить цвет</span></div>';

?>