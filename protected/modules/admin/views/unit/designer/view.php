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

                        <?php $this->renderPartial('_sidebar', array('unit'=>$unit, 'settings'=>$settings)); ?>

                </div>
        </div>
        <div class="span-18">	 
                <div class="form">
                                
                                <div class="row">
                                        <?php echo CHtml::label('ID пользователя', 'user_id')?>
                                        <span class="value"><?php echo $id; ?></span>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('ФИО', 'name')?>
                                        <span class="value"><?php echo $data['name']; ?></span>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Анонс', 'desc')?>
                                        <span class="value"><?php echo $data['desc']; ?></span>
                                </div>

				<div class="row">
					<?php echo CHtml::label('Услуга', 'service_id')?>
					<span class="value"><?php echo ($m = Service::model()->findByPk(isset($data['service_id']) ? $data['service_id'] : 0 )) ? $m->name : "&mdash;"; ?></span>
				</div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Статус', 'status')?>
                                        <span class="value"><?php echo Unit::$statusLabelForDesigner[$data['status']] ?></span>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Изображение', 'uploadfile');?>
                                        <?php echo CHtml::image('/'.$image->getPreviewName(Config::$preview['crop_150']));?><br />
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Внутренние комментарии', 'system_message')?>
                                        <?php 
                                                foreach ($data['system_message'] as $message): ?>
                                                        <div class="row">
                                                                <?php echo date("d.m.Y", $message['create_time'])?>                                                        
                                                                <b><?php echo $message['comment']?></b>
                                                                
                                                        </div>
                                                        <?php endforeach; ?>
                                </div> 
                        
                        <div class="row">
                                <?php echo CHtml::button('Редактировать', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/update', array('id'=>$id)).'\''))?>
                                <?php echo CHtml::button('Отмена', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/index').'\''))?>
                        </div>
                </div>
        </div>

</div>