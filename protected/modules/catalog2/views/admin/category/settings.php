<?php
/**
 * @var $model Category
 */

$this->breadcrumbs=array(
	'Каталог товаров'=>array('#'),
	'Категории'=>array('index'),
	'Настройки категории "'.$model->name.'"',
);
?>

<h1>Настройки категории "<?php echo $model->name; ?>"</h1>

<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'category-form',
	'enableAjaxValidation'=>false,
)); ?>

<div class="clearfix">
	<label>Иконка категории</label>
	<div class="input">
		<?php echo CHtml::image('/'.$model->getPreview(Category::$preview['crop_120']), '', array('width'=>120, 'height'=>120, 'id'=>'image')); ?>
	</div>
</div>

<div class="cleafix">
	<label>Загрузить иконку</label>
	<div class="input">
		<?php $this->widget('ext.FileUpload.FileUpload', array(
			'url'=> $this->createUrl('upload', array('cid'=>$model->id)),
			'postParams'=>array(),
			'config'=> array(
				'fileName' => 'Category[file]',
				'onSuccess'=>'js:function(response){ $("#image").attr("src", response.src); }',
			),
			'htmlOptions'=>array('size'=>61, 'accept'=>'image', 'class'=>'img_input', 'multiple'=>false),
		)); ?>
	</div>
</div>
<?php if ($model->isLeaf()) : ?>
<div class="clearfix"></div>
<div class="clearfix">
	<label>Помещения</label>
	<div class="input">
		<ul><?php foreach ($mainRooms as $room) {
			echo CHtml::openTag('li');
			$checked = isset($sRooms[$room->id]);
			echo CHtml::checkBox('CategoryRoom['.$room->id.']', $checked, array()) . $room->name;
			echo CHtml::closeTag('li');
		} ?></ul>
	</div>
</div>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
	<?php
	if ( $parent !== null)
		$url = $this->createUrl('/catalog2/admin/category/index', array('cid'=>$parent->id));
	else
		$url = $this->createUrl('/catalog2/admin/category/index');
	echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => "document.location = '".$url."'"));
	?>
</div>
<?php endif; ?>

<?php $this->endWidget(); ?>