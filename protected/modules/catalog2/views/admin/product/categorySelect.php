<?php
$this->breadcrumbs=array(
        'Каталог товаров'=>array('#'),
        'Товары'=>array('index'),
        'Создание',
);
?>

<h1>Выбор категории</h1>


<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
        'id'=>'category-form',
        'action'=>'create',
        'method'=>'get',
        'enableAjaxValidation'=>false,
)); ?>

<div class="clearfix">

        <select name="category_id" size="50" style="height: 400px; width: 300px;">
                <?php echo Category::getGroupedCategoriesList();?>
        </select>

</div>

<div class="actions">
        <?php echo CHtml::submitButton('Далее', array('class'=>'btn primary')); ?>
</div>

<?php $this->endWidget(); ?>