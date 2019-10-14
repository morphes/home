<div class="modal-header"></div>
<div class="modal-body -steps">
	<div class="step-1">
		<div class="-col-4 -skip-3 -inset-top -inset-bottom -gutter-top-dbl">
			<h3 class="-giant -gutter-top-null">
				<?php if (!isset($hideRegInfo) || $hideRegInfo != true) { ?>
					Регистрация специалиста
				<?php } else { ?>
					Присоединиться к специалистам
				<?php } ?>
			</h3>
			<ul class="-menu-inline spec-type">
				<li data-id="<?php echo User::ROLE_SPEC_FIS;?>" class="current">Специалист</li>
				<li data-id="<?php echo User::ROLE_SPEC_JUR;?>">Организация</li>
			</ul>
		</div>
		<div class="-col-3">
			<div class="step-label current"><span>1</span>Анкета</div>
		</div>
		<div class="-col-4 -inset-bottom -text-align-center">
			<form id="regForm_1" method="post" action="">
				<div>
					<?php echo CHtml::activeTextField($user, 'firstname', array(
						'placeholder' => 'Имя Фамилия',
						'data-alt'    => 'Название компании',
						'class'       => '-huge'
					)) ?>
					<?php echo CHtml::activeHiddenField($user, 'role', array('value' => User::ROLE_SPEC_JUR)); ?>
				</div>
				<div>
					<?php echo CHtml::hiddenField('User[city_id]', $user->city_id, array('id' => 'r_city_id')); ?>
					<input type="text" placeholder="Город" class="-huge city-autocomplete" id="User_city_id" onkeyup="if (this.value == '') $('#city_id').val('');">
					<script>
						$('.city-autocomplete').autocomplete({
							source: '/utility/autocompletecity',
							minLength: 3,
							select: function(event, ui) {
								$("#r_city_id").val(ui.item.id).keyup();
							},
							focus:	function(event, ui){
								$("#r_city_id").val(ui.item.id).keyup();
							},
							change:	function(event, ui){
								if (ui.item == null) {
									$(".input-clear").click();
								}
							}
						});
					</script>
				</div>
				<div>
					<?php echo CHtml::activeTextField($user, 'phone', array('placeholder' => 'Телефон', 'class' => '-huge'));?>
				</div>

				<?php if (!isset($hideRegInfo) || $hideRegInfo != true) { ?>
					<div class="-gutter-top-dbl">
						<?php echo CHtml::activeTextField($user, 'email', array('placeholder' => 'Электронная почта', 'class' => '-huge')); ?>
					</div>
					<div class="-gutter-bottom-dbl">
						<?php echo CHtml::activeTextField($user, 'password', array(
							'placeholder' => 'Пароль (от 4-х символов)',
							'class'       => '-huge'
						)); ?>
					</div>
				<?php } ?>


				<button type="submit" class="-button -button-skyblue -gutter-bottom-dbl -huge -semibold next become-specialist">Далее</button>
			</form>
		</div>
	</div>
	<div class="step-2">
		<div class="-col-4 -skip-3 -pass-3 -inset-top -gutter-top-dbl">
			<h3 class="-giant -gutter-top-null">Регистрация специалиста</h3>
			<p class="-gray -gutter-null">Укажите ваши услуги</p>
		</div>
		<div class="-col-3">
			<div class="step-label"><span>2</span>Услуги</div>
		</div>
		<div class="-col-7 -inset-bottom -hidden">
			<form id="regForm_2" method="post" action="">
				<div class="list-wrapper">
					<div class="list-inner" id="servicesList">
						<div class="scrollbar"><div class="track"><div class="thumb"></div></div></div>
						<div class="viewport" style="height: 320px;">
							<div class="overview">
								<?php
								/** Список всех услуг */
								$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));
								foreach ($services as $serv) {
									if ($serv->parent_id == 0) {
										?>
										<h4 class="-huge"><?php echo $serv->name;?></h4>
										<?php
									} else {
										?>
										<span class="-block">
											<label class="-checkbox">
												<?php echo CHtml::checkBox('User[services][' . $serv->id . '][id]', '', array('value'=>$serv->id)); ?>
												<span><?php echo $serv->name;?></span>
											</label>
										</span>
										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="-inset-top-dbl -gutter-bottom-hf -gray">Регистрируясь, я соглашаюсь с <a href="/agreement" target="_blank" class="-skyblue">правилами</a></div>
				<button type="submit" class="-button -button-skyblue -gutter-bottom-dbl -huge -semibold">Готово</button>
			</form>
		</div>
	</div>

	<!--	<div class="-col-8 -skip-1 -pass-1 -tinygray-box -inset-top-hf -inset-bottom-dbl -gutter-top-dbl -text-align-center">
			<h3>Хотите попасть в каталог специалистов — присоединяйтесь</h3>
			<a href="#popupReg" data-src="/html-new/site/registration/_stepsForm.php" rel="modal" class="-block -button -button-skyblue -huge -semibold">Присоединиться как специалист</a>
		</div> -->
</div>