<?php foreach($servProvider->getData() as $data) : ?>
        <div class="item">
            <img src="/img/service_icon.png">
            <div class="item_desc">
                <p>
                    <?php $name = preg_replace("#($term)#iu", '<span class="search_word">\1</span>', $data->name);?>
                    <a class="item_head"  href="<?php echo $this->createUrl('/specialist/' . $data->url); ?>"><?php echo $name; ?></a>
                    <span></span>
                </p>
                <?php if(!$data->founded_by_name && $data->synonym_id) : ?>
                        <?php $synonym = Yii::app()->db->createCommand()->select('synonym')->from('service_synonym')->where('id=:id', array(':id'=>$data->synonym_id))->queryScalar();?>
                        <?php $synonym = preg_replace("#($term)#iu", '<span class="search_word">\1</span>', $synonym);?>
                        <?php echo CHtml::tag('span', array(), $synonym); ?>
                <?php endif; ?>
                <div class="item_counter"></div>
            </div>
        </div>
<?php endforeach; ?>