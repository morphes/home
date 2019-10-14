<?php
/**
 * @var $connection CDbConnection
 * @var $model Store
 * @var $this FrontController
 */
?>

<script type="text/javascript">
    $(document).ready(function(){
        store.initForm();
    })
</script>


<?php $this->renderPartial('_storeUpdateMenu', array('model'=>$model)); ?>

<div id="right_side">
        <?php echo CHtml::beginForm('', 'post', array('class'=>'store_adding_form')); ?>
        <div class="form">

            <h2 class="formname">Витрина товаров</h2>

            <div class="options_section">
                    <?php foreach($model->showcase_data as $key=>$pid) : ?>

                            <?php if (in_array($key, $errors)) $class = 'error'; else $class = ''; ?>
                            <div class="options_row">
                                <div class="option_label">
                                    <i class="p1 pr_label"></i>
                                    <label class="<?php echo $class; ?>">Товар <?php echo $key; ?></label>  <span class="required">*</span>
                                </div>
                                <div class="option_value">
                                        <?php
                                                $product_name = $connection->createCommand()->select('name')->from('cat_product')->where('id=:id', array(':id'=>$pid))->queryScalar();
                                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                                        'name'=>'Product['.$key.'][name]',
                                                        'value'=>$product_name,
                                                        'sourceUrl'=>'/utility/acProduct?store_id='.$model->id,
                                                        'options'=>array(
                                                                'minLength'=>'2',
                                                                'showAnim'=>'fold',
                                                                'select'=>'js:function(event, ui) {$("#Product_'.$key.'_pid").val(ui.item.id).keyup();}',
                                                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Product_'.$key.'_pid").val("");}}',
                                                        ),
                                                        'htmlOptions'=>array('class'=>'textInput ' . $class, 'placeholder' => 'Введите название товара')
                                                ));
                                        ?>
                                        <?php echo CHtml::hiddenField('Product['.$key.'][pid]',  $pid); ?>

                                    <span class="del"><i></i></span>
                                </div>
                                <div class="clear"></div>
                            </div>
                    <?php endforeach; ?>
            </div>

        </div>
        <div class="buttons_block">
            <div class="btn_conteiner yellow">
                    <?php echo CHtml::submitButton('Сохранить изменения', array('class'=>'btn_grey')); ?>
            </div>
        </div>
    <?php echo CHtml::endForm(); ?>
</div>

<?php Yii::app()->clientScript->registerScript('showcase', '
        $("span.del").find("i").click(function(){
                $(this).parent().parent().find("input").each(function( index ) {
                        $(this).val("");
                });
        });
', CClientScript::POS_READY); ?>