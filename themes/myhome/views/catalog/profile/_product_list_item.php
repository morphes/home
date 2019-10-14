<?php
/**
 * @var $data Product
 */
?>

<div class="item <?php if(isset($highlight_id) && $data->id == $highlight_id) echo 'added'; ?>">

    <?php if(isset($highlight_id) && $data->id == $highlight_id) : ?>
            <div class="added_layer">
                <span>i</span>
                <p>Товар добавлен и ожидает проверки</p>
                <i class="close"></i>
            </div>
    <?php endif; ?>

    <div class="photo">
        <?php if($data->status != Product::STATUS_ACTIVE && $data->cover) : ?>
                <a>
                        <?php echo CHtml::image('/'.$data->cover->getPreviewName(Product::$preview['crop_60']), '', array('width'=>60)); ?>
                </a>
        <?php elseif ($data->cover) : ?>
                <a href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>">
                        <?php echo CHtml::image('/'.$data->cover->getPreviewName(Product::$preview['crop_60']), '', array('width'=>60)); ?>
                </a>
        <?php endif; ?>
    </div>

    <div class="name">
        <?php if($data->status != Product::STATUS_ACTIVE) : ?>
                <span class="item_head"><?php echo $data->name; ?></span>
        <?php else : ?>
                <a class="item_head" href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>"><?php echo $data->name; ?></a>
         <?php endif; ?>

        <p><?php echo $data->vendor->name; ?></p>
    </div>

    <div class="category">
            <?php echo $data->category->name; ?>
    </div>

    <div class="date">
            <?php echo date('d.m.Y', $data->create_time);?>
    </div>

    <div class="status">
            <?php if($data->status == Product::STATUS_ACTIVE) echo '<span class="approve">Одобрен</span>'; ?>
            <?php if($data->status == Product::STATUS_MODERATE) echo '<span class="moderation">На модерации <a href="'.Yii::app()->createUrl('/catalog/profile/productUpdate', array('id'=>$data->id)).'"><i title="редактировать"></i></a></span>'; ?>
            <?php if($data->status == Product::STATUS_REJECTED) : ?>
                <span class="deflect">Отклонен
                    <?php if($data->admin_comment) : ?>
                            <div class="notice">
                                <p><?php echo $data->admin_comment; ?></p>
                                <?php echo CHtml::link('редактировать', Yii::app()->createUrl('/catalog/profile/productUpdate', array('id'=>$data->id))); ?>
                            </div>
                    <?php endif; ?>
                    <a href="<?php echo Yii::app()->createUrl('/catalog/profile/productUpdate', array('id'=>$data->id)); ?>"><i title="редактировать"></i>
                </span>
            <?php endif; ?>
    </div>

    <div class="clear"></div>
</div>