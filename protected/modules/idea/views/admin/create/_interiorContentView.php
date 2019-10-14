<?php 

        $rooms = array(''=>'Не выбрано') + CHtml::listData($rooms, 'id', 'option_value');
        $colors = array('' => '') + CHtml::listData($colors, 'id', 'option_value');
        $styles = array('' => 'Не выбран') + CHtml::listData($styles, 'id', 'option_value');

?>
<h2>Помещение: <?php echo $rooms[$content->room_id];?> </h2>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
    'data'=>$content,
    'attributes'=>array(
        array(
            'label'=>'Помещение',
            'type'=>'html',
            'value'=>"<b>".$rooms[$content->room_id]."</b>",
        ),
        array(
            'label'=>'Метки от автора',
            'type'=>'raw',
            'value'=> $content->tag,
        ),
        array(
            'label'=>'Стиль',
            'type'=>'raw',
            'value'=> !empty($content->style_id) ? $styles[ $content->style_id ] : '',
        ),
        array(
            'label'=>'Основной цвет',
            'type'=>'raw',
            'value'=> !empty($content->color_id) ? $colors[ $content->color_id ] : '',
        ),
        array(
            'label'=>'Дополнительные цвета',
            'type'=>'html',
            'value'=> IdeaAdditionalColor::formatAdditionalColors($additional_colors, $colors),
        ),
        array(
            'label'=>'Обложка помещения',
            'type'=>'raw',
            'value'=>Interior::imageFormater($mainImage, true),
        ),
        array(
            'label'=>'Изображения',
            'type'=>'raw',
            'value'=>Interior::imageFormater($uploadedFiles, true),
        ),
        array(
            'label'=>'Дата создания',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $content->create_time),
        ),
        array(
            'label'=>'Дата обновления',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $content->update_time),
        ),
    ),
));
?>