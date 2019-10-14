<div class="row">

	<div class="span12">
		<h4>Быстрый поиск по типу пользователей</h4>
		<ul>
			<?php foreach(Config::$rolesAdmin as $role=>$label):?>
			<li>
				<?php echo CHtml::link($label, '#', array(
					'class' => 'user-type',
					'onclick' => '
						$("#group").val("'.$role.'");
						$("#filter-form").find("form").submit();
						return false;
					')
				);?>
			</li>
			<?php endforeach;?>
		</ul>


		<h4>Расширенный фильтр</h4>

		<div id="filter-form">
			<?php echo CHtml::beginForm($this->createUrl($this->id.'/'.$this->action->id), 'get');?>

			<div class="clearfix">
				<?php echo CHtml::label(User::model()->getAttributeLabel('login'), 'login')?>
				<div class="input">
				<?php echo CHtml::textField('login', '', array('size'=>15))?>
				</div>
			</div>

			<div class="clearfix">
				<?php echo CHtml::label(User::model()->getAttributeLabel('email'), 'email')?>
				<div class="input">
				<?php echo CHtml::textField('email', '', array('size'=>15))?>
				</div>
			</div>

			<div class="clearfix">
				<?php echo CHtml::label('Группа', 'group')?>
				<div class="input">
				<?php echo CHtml::dropDownList('group', '0', array('0'=>'Все')+Config::$rolesAdmin)?>
				</div>
			</div>

			<div class="clearfix">
				<?php echo CHtml::label('Статус', 'status')?>
				<div class="input">
				<?php echo CHtml::dropDownList('status', '0', array('0'=>'Все')+Config::$userStatus)?>
				</div>
			</div>

			<div class="clearfix">
				<?php echo CHtml::label('Регистрация от', 'reg')?>
				<div class="input">
				<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
					'name'=>'reg_from',
					'options'=>array('dateFormat'=>'dd.mm.yy'),
					'htmlOptions'=>array(
					'style'=>'width:150px;'
					),
				));?>
				</div>
			</div>
			<div class="clearfix">
				<?php echo CHtml::label('Регистрация до', 'reg_to')?>
				<div class="input">
				<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
					'name'=>'reg_to',
					'options'=>array('dateFormat'=>'dd.mm.yy'),
					'htmlOptions'=>array(
					'style'=>'width:150px;'
					),
				));?>
				</div>
			</div>

			<div class="actions">
				<?php echo CHtml::submitButton('Найти', array('class' => 'btn'))?>
			</div>

			<?php echo CHtml::endForm();?>
		</div>
	</div>
</div>