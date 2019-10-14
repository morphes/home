<h2>Копирование товаров завершено</h2>
<div id="right_side">
    <form class="products_copy_form">
        <div class="form">
            <div class="options_section">
                <div class="options_row">
                    <span class="label">из</span>
                    <ul class="copy_result">
                        <li><?php echo CHtml::link($from_store->fullName, Store::getLink($from_store->id)); ?></li>
                    </ul>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="options_section">
                <div class="options_row">
                    <span class="label">в</span>
                    <ul class="copy_result">
                        <?php foreach($to_stores as $to_store) : ?>
                                <li><?php echo CHtml::link($to_store->fullName, Store::getLink($to_store->id)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="buttons_block">
            <?php echo CHtml::link('Копировать еще', $this->createUrl('productCopy')); ?>
        </div>
    </form>

</div>