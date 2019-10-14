<style>
        .switch-status, .group-operations {
                color: #0066CC;
                text-decoration: underline;
                cursor: pointer;
        } 

        .row {
                padding-top: 10px;
        }
</style>

<div class="container">
        <div class="span-6 last">
                <div id="sidebar" style="padding-left: 10px; background-color: #ececec; margin-right: 10px;">

                        <?php $this->renderPartial('_sidebar', array('unit' => $unit, 'settings' => $settings)); ?>

                </div>
        </div>
        <div class="span-18">	 
                <div class="form">

                        <?php echo CHtml::beginForm(); ?>

                        <div class="row">
                                <?php echo CHtml::label('Название', 'name') ?>
                                <?php echo CHtml::activeTextField($unit, 'alias'); ?>
                        </div>

                        <div class="row">
                                <?php echo CHtml::label('Показать блок подписи', 'display') ?>
                                <?php echo CHtml::dropDownList('status', $settings['description']['status'], Unit::$statusLabel); ?>
                        </div>

                        <div class="row">
                                <?php
                                $this->widget('application.extensions.tinymce.ETinyMce', array(
                                        'name'=>'data',
                                        'options'=>array(
                                                'theme'=>'advanced',
                                                'forced_root_block' => false,
                                                'force_br_newlines' => true,
                                                'force_p_newlines' => false,
                                                'width'=>'500px',
                                                'height'=>'100px',
                                                'theme_advanced_toolbar_location'=>'top',
                                                'language'=>'ru',
                                        ),
                                        'value'=>  CHtml::decode($settings['description']['data']),
                                ));
                                ?>
                        </div> 


                        <div class="buttons">
                                <?php echo CHtml::submitButton('Сохранить'); ?>
<?php echo CHtml::button('Отменить'); ?>
                        </div>

<?php echo CHtml::endForm(); ?>     


                </div>
        </div>

</div>