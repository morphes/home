<?php
/**
 * @var $model User
 */
?>
<div class="row">

	<div class="span12">
		<h4>Быстрый поиск по типу пользователей</h4>
		<ul>
			<?php foreach(Config::$rolesUserReg as $role=>$label):?>
			<li>
				<?php echo CHtml::link($label, '#', array(
					'class' => 'user-type',
					'onclick' => '
						$("#search_role").val("'.$role.'");
						$("#filter-form").find("form").submit();
						return false;
					')
				);?>
			</li>
			<?php endforeach;?>
		</ul>

		<h4>Расширенный фильтр</h4>

		<div id="filter-form">
			
			<?php /** @var $form BootActiveForm */
			$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
                                'action'=>$this->createUrl($this->id.'/'.$this->action->id),
                                'method'=>'get',
                        )); ?>

			<?php echo $form->textFieldRow($model,'id',array('class'=>'span6', 'hint'=>'Можно указать несколько ID, разделив их запятой')); ?>

			<div class="clearfix">
				<?php echo CHtml::label('ФИО / Логин', 'login')?>
				<div class="input">
				<?php echo $form->textField($model, 'login', array('class'=>'span6'))?>
				</div>
			</div>

                   	<div class="clearfix">
			    <?php echo CHtml::label('Телефон', 'phone'); ?>

                        	<div class="input">
					<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name'		=> 'phone',
						'sourceUrl'	=> $this->createUrl('/admin/user/autocompletePhone'),
						'value'        	=> $model->phone,
						'options'		=> array(
							'showAnim'	=> 'fold',
							'delay'		=> 300,
							'autoFocus'	=> true,
							'select'	=> 'js:function(event, ui) {$("#User_id").val(ui.item.id);  }',
						),
						'htmlOptions' => array('class'=>'span6')
					));?>

					<?php
					Yii::app()->clientScript->registerScript('loginType', '
					$("#phone").keydown(function(event){
						if (
							event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
							&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
							&& event.keyCode != 35 && event.keyCode != 36
						) {
							$("#MediaNew_author_id").val("");
						}
					});
					', CClientScript::POS_READY);
					?>
                         	</div>
                	</div>

			<?php echo $form->textFieldRow($model, 'email', array('class' => 'span6'));?>

			<div class="clearfix">
				<?php echo CHtml::label('Роль', 'search_role')?>
				<div class="input">
				<?php echo CHtml::dropDownList('search_role', $search_role, array('0'=>'Все') + Config::$rolesUserReg + array('allSpec'=>'Специалист (физ. и юр. лица)'), array('class' => 'span6'))?>
				</div>
			</div>

			<?php echo $form->dropDownListRow($model, 'status', array('0'=>'Все')+Config::$userStatus, array('class' => 'span6'));?>
			
			<?php echo $form->dropDownListRow($model, 'promo_code', array('0'=>'Все')+$promocodes, array('class' => 'span6'));?>

			<div class="clearfix">
				<label>Оказывает услугу</label>
				<div class="input">
				<?php
				// Форимируем вложенный массив списка сервисов, сгруппированных по родителю
				// В $arr[0] лежат все родительские сервисы
				$arr = CHtml::listData( Service::model()->findAll(''), 'id', 'name', 'parent_id') ;
				foreach($arr[0] as $parent_id=>$parent_name) {
					$arr[ $parent_name ] = $arr[ $parent_id ];
					unset( $arr[$parent_id] );
				}
				unset($arr[0]);
				echo CHtml::dropDownList('service_id', $service_id, array('0' => 'Все')+array('none' => 'Не выбрано')+$arr, array('class' => 'span6'));
				?>
				</div>
			</div>

			<div class="clearfix <?php if ($model->getError('service_city')) echo 'error';?>">
				<label>Оказывает услугу в городе</label>
				<div class="input">
					<?php
					$this->widget('application.components.widgets.EAutoComplete', array(
						'valueName'	=> City::getNameById($model->service_city),
						'sourceUrl'	=> '/utility/autocompletecity',
						'value'		=> $model->service_city,
						'options'	=> array(
							'showAnim'	=>'fold',
							'open' => 'js:function(){}',
							'minLength' => 3
						),
						'htmlOptions'	=> array('id'=>'service_city', 'name'=>'User[service_city]', 'class' => 'span6'),
						'cssFile' => null,
					));
					?>
				</div>
			</div>

			<div class="clearfix <?php if ($model->getError('city_id')) echo 'error';?>">
				<label><?php echo $model->getAttributeLabel('city_id'); ?></label>
				<div class="input">
					<?php
					$this->widget('application.components.widgets.EAutoComplete', array(
						'valueName'	=> $model->getCityFull(),
						'sourceUrl'	=> '/utility/autocompletecity',
						'value'		=> $model->city_id,
						'options'	=> array(
							'showAnim'	=>'fold',
							'open' => 'js:function(){}',
							'minLength' => 3
						),
						'htmlOptions'	=> array('id'=>'city_id', 'name'=>'User[city_id]', 'class' => 'span6'),
						'cssFile' => null,
					));
					?>
				</div>
			</div>
                        
                        <div class="clearfix">
				<?php echo CHtml::label('Логин пригласившего', 'referrer')?>
				<div class="input">
				<?php echo CHtml::textField('referrer', Yii::app()->request->getParam('referrer'), array('class'=>'span6'))?>
				</div>
			</div>

			<div class="clearfix">
				<?php echo CHtml::label('Тип эксперта', false)?>
				<div class="input">
					<?php echo $form->dropDownList($model, 'expert_type', array(''=>'')+User::$expertNames); ?>
				</div>
			</div>

			<div class="clearfix">
				<?php echo CHtml::label('Регистрация от', 'reg')?>

				<div class="input">
				<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
					'name'=>'reg_from',
					'value'=> $reg_from,
					'options'=>array('dateFormat'=>'dd.mm.yy'),
					'htmlOptions'=>array(
					'style'=>'width:150px;'
					),
				));?>
				</div>
			</div>
			
			<div class="clearfix">
				<?php echo CHtml::label('Регистрация до', 'reg')?>
				
				<div class="input">
				<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
					'name'=>'reg_to',
					'value' => $reg_to,
					'options'=>array('dateFormat'=>'dd.mm.yy'),
					'htmlOptions'=>array(
					'style'=>'width:150px;'
					),
				));?>
				</div>
			</div>

			<div class="actions">
				<?php echo CHtml::submitButton('Найти', array('class' => 'btn primary'));?>
			</div>

			<?php $this->endWidget(); ?>
		</div>
	</div>
</div>