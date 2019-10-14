<?php
$this->pageTitle = 'Выбор категории — MyHome.ru';
?>

<script type="text/javascript">
    $(document).ready(function(){
        store.initForm();
        store.initCategoryChoise();
    })
</script>

<h2>Шаг 1 из 2. Выберите категорию</h2>


<div id="right_side" class="new_template category_selection">

    <div class="subcategory">
        <ul class="depth2">
            <?php foreach($mcats as $mcat) : ?>
                <?php echo $this->getSubCategoriesListHtml($mcat); ?>
            <?php endforeach; ?>

            <?php reset($mcats); $mcat = null; ?>
        </ul>
    </div>

    <div class="recently_added">
        <?php if(!empty($latest_cats)) : ?>
                <h2 class="block_head">Вы недавно добавляли</h2>
                <ul class="" id="latest_cats">
                    <?php foreach($latest_cats as $lc_id=>$lc_name) : ?>
                        <li><a cid="<?php echo $lc_id; ?>" href="#"><?php echo $lc_name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
        <?php else : ?>
                <p>Добавьте свои товары в каталог. Выберите категорию для добавления товара	и  заполните анкету.</p>
                <p>Если что-то пошло не так найдите решение в разделе <a href="#">помощь</a> или <a class="feedback-handler" href="#">обратитесь</a> к нашим специалистам.</p>
        <?php endif; ?>
    </div>
</div>
<div id="left_side" class="new_template category_selection">
    <ul class="depth1">
        <li cid="<?php echo $root->id; ?>" class="current"><a href="#">Все категории</a></li>
        <?php foreach($mcats as $mcat) : ?>
                <li cid="<?php echo $mcat->id; ?>"><a href="#"><?php echo $mcat->name ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>


<?php echo CHtml::beginForm('', 'post', array('id'=>'product-form')); ?>
        <?php echo CHtml::activeHiddenField($model, 'category_id', array('id'=>'category_id')); ?>
<?php echo CHtml::endForm(); ?>