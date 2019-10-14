<?php foreach($specProvider->getData() as $data) : ?>
        <div class="item">
            <?php echo CHtml::image('/'.$data->getPreview( Config::$preview['crop_23'] ), '', array('width' => 23, 'height' => 23));?>
            <div class="item_desc">
                <p>
                    <?php $name = preg_replace("#($term)#iu", '<span class="search_word">\1</span>', $data->name);?>
                    <a class="item_head"  href="<?php echo $data->linkProfile;?>"><?php echo $name; ?></a>
                    <span></span>
                </p>
                <span><?php echo isset($data->city) ? $data->city->name : ''; ?></span>
                <div class="item_counter">
                        <?php echo CFormatterEx::formatNumeral($data->count_interior, array('проект', 'проекта', 'проектов')); ?>
                </div>
            </div>
        </div>
<?php endforeach; ?>

<?php if($specProvider->pagination->currentPage < $specProvider->pagination->pageCount - 1) : ?>
        <?php echo CHtml::hiddenField('next_page_url', $specProvider->pagination->createPageUrl($this, $specProvider->pagination->currentPage + 1)); ?>
<?php else : ?>
        <?php echo CHtml::hiddenField('next_page_url', 0); ?>
<?php endif;?>