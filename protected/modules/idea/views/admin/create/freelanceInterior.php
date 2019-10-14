<?php Yii::app()->clientScript->registerScriptFile('/js/simple.lightbox.admin.js', CClientScript::POS_HEAD); ?>
<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'idea-making-form-step-2',
	'enableAjaxValidation'=>false,
        'htmlOptions' => array('class' => 'form-project-add'),
        'stacked'=>true,
)); ?>


<?php // TODO: refactor
	$rooms = IdeaHeap::getRooms(Config::INTERIOR, $interior->object_id);
	$colors = IdeaHeap::getColors(Config::INTERIOR, $interior->object_id);
	$styles = IdeaHeap::getStyles(Config::INTERIOR, $interior->object_id);

	$rooms = array(''=>'Выберите помещение') + CHtml::listData($rooms, 'id', 'option_value');
	$colors = array('' => 'Выберите цвет') + CHtml::listData($colors, 'id', 'option_value');
	$styles = array('' => 'Выберите стиль') + CHtml::listData($styles, 'id', 'option_value');
	$tabs = array();
                
	if(!$interiorContents)
		$interiorContents = array();

	foreach($interiorContents as $content) {
		$tabs[] = array('title'=>$rooms[$content->room_id], 'id'=>$content->id);
	}
	Yii::app()->getClientScript()->registerScriptFile('/js/bootstrap-tabs.js');
?>
<div class="well" style="background-color: #F9F9F9;">
<?php // interior
echo CHtml::label($interior->getAttributeLabel('name'), false);
echo CHtml::tag('div', array('class'=>'span12'), $interior->name);
echo CHtml::label($interior->getAttributeLabel('desc'), false);
echo CHtml::tag('div', array('class'=>'span12', 'style'=>'min-height:100px;'), $interior->desc);
?>
</div>
<?php
// tabs		
echo CHtml::openTag('div', array('class'=>'tab-content'));
Yii::app()->controller->renderPartial('application.modules.idea.views.admin.create._interiorContentTabs', array(
                            'tabs' => $tabs,
                        ));
// interior content
foreach ($interiorContents as $interiorContent) :
	echo CHtml::openTag('div', array('id'=>'interior_content_id_'.$interiorContent->id, 'class'=>'fpa-space form-stacked'));
		
		$ufProvider = new CActiveDataProvider('UploadedFile', array(
		    'criteria' => array(
			'join' => 'INNER JOIN idea_uploaded_file ON idea_uploaded_file.uploaded_file_id=t.id',
			'condition' => 'idea_uploaded_file.idea_type_id=:ideaType AND idea_uploaded_file.item_id=:itemId',
			'params' => array(':ideaType' => Config::INTERIOR, ':itemId' => $interiorContent->id),
		    ),
		));
		
		?>
		<div class="well" style="background-color: #F9F9F9;">
			<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Помещения', true); ?>
			<?php
			$this->widget('ext.bootstrap.widgets.BootThumbnails', array(
			    'dataProvider'=>$ufProvider,
			    'template'=>"{items}\n{pager}",
			    'itemView'=>'_freelanceItemImage',
			    // Remove the existing tooltips and rebind the plugin after each ajax-call.
			    /*'afterAjaxUpdate'=>"js:function() {
				jQuery('.tooltip').remove();
				jQuery('a[rel=tooltip]').tooltip();
			    }",*/
			));
			?>	
			<div class="clearfix">
				<?php echo CHtml::activeLabel($interiorContent, "room_id");?>
				<div class="input">
					<?php echo CHtml::tag('div', array('class'=>'rooms span8', 'data-id'=>$interiorContent->id), $rooms[$interiorContent->room_id]); ?>
				</div>  
			</div>
			<div class="clearfix">
				<?php echo CHtml::activeLabel($interiorContent, "style_id");?>
				<div class="input">
					<?php echo CHtml::tag('div', array('class'=>'rooms span8', 'data-id'=>$interiorContent->id), $styles[$interiorContent->style_id]); ?>
				</div>  
			</div>
			<div class="clearfix">
				<?php echo CHtml::activeLabel($interiorContent, "color_id");?>
				<div class="input">
					<?php echo CHtml::tag('div', array('class'=>'rooms span8', 'data-id'=>$interiorContent->id), $colors[$interiorContent->color_id]); ?>
				</div>  
			</div>
			<div class="clearfix">      
				<?php echo CHtml::label('Метки от автора', ''); ?>
				<div class="input">
					<?php echo CHtml::activeTextArea($interiorContent, "[{$interiorContent->id}]tag", array('class'=>'span8', 'style'=>'min-height:150px; width: 600px;')); ?>
				</div>  
			</div>

		</div>
	<?php echo CHtml::closeTag('div'); ?>
<?php endforeach; ?>
<?php echo CHtml::closeTag('div'); ?>
<?php // buttons ?>
        <div class="actions">
                <?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
                <?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => "document.location = '".$this->createUrl('/'.$this->module->id.'/admin/interior/view', array('interior_id' => $interior->id))."'"));?>
        </div>
<?php $this->endWidget(); ?>