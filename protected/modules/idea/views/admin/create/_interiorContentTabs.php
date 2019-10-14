<?php Yii::app()->clientScript->registerScript('interior-content-tabs', '
        $(".tabs").tabs();
        $("ul.tabs").find("a:first").click();
', CClientScript::POS_READY);?>

<ul class="tabs">
        <?php foreach($tabs as $tab): ?>
                <li id="tab_<?php echo $tab['id']; ?>"><a href="#interior_content_id_<?php echo $tab['id']; ?>"><?php echo $tab['title']; ?></a></li>
        <?php endforeach; ?>
</ul>