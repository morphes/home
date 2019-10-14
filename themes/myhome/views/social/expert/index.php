<?php Yii::app()->getClientScript()->registerCssFile('/css/forum.css'); ?>
<?php Yii::app()->getClientScript()->registerCssFile('/css/jquery.gritter.css'); ?>
<?php Yii::app()->getClientScript()->registerScriptFile('/js/jquery.gritter.min.js'); ?>

<script type="text/javascript">
	$(document).ready(function(){
		js.initExperts();
	})
</script>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(),
	));?>
	<h1>Эксперты MyHome</h1>
	<div class="expert_link">
		<i></i>
		<span class="expert_rules">Хочу стать экспертом</span>

	</div>
	<div class="spacer"></div>
</div>

<div class="experts_page">
	<?php if (!empty($topExperts)) : ?>
	<h2 class="block_head">Ведущие эксперты</h2>
	<ul class="experts">
		<?php /** @var $expert User */
		foreach ($topExperts as $expert) : ?>
			<li>
				<h3>
					<a href="<?php echo $expert->getLinkProfile(); ?>">
						<?php echo CHtml::image('/'.$expert->getPreview(User::$preview['crop_120']), $expert->name, array('width'=>120, 'height'=>120)) . $expert->name; ?>
					</a>
				</h3>
				<p><?php echo $expert->data->expert_desc; ?></p>
			</li>
		<?php endforeach; ?>
		<div class="clear"></div>
	</ul>
	<div class="clear"></div>
	<?php endif; ?>

	<?php if (!empty($experts)) : ?>
	<h2 class="block_head">Эксперты</h2>
	<ul class="allexperts">
		<?php /** @var $expert User */
		foreach ($experts as $expert) : ?>
			<li>
				<a href="<?php echo $expert->getLinkProfile(); ?>">
					<?php echo CHtml::image('/'.$expert->getPreview(User::$preview['crop_45']), $expert->name, array('width'=>45, 'height'=>45)) . $expert->name; ?>
				</a>
				<p><?php echo $expert->data->expert_desc; ?></p>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="clear"></div>
	<?php endif; ?>
</div>

<div class="spacer-18"></div>






<div class="expert_info  hide">
	<div class="expert_form_head">
		<i></i>
		<h3>Хочу стать экспертом</h3>
		<i class="close"></i>
	</div>
	<div class="expert_rules_content">
		<strong>Эксперт MyHome — это истинный профессионал своего дела</strong>
		<b>Чтобы стать Экспертом, вам необходимо иметь:</b>
		<ul class="">
			<li><span>Желание высказывать свое мнение о товарах и идеях портала, активно общаться на форуме, отвечая на вопросы пользователей MyHome</span></li>
			<li><span>Качественный профиль на MyHome с интересными проектами и заполненной информацией о себе</span></li>
		</ul>
		<b>Что это дает:</b>
		<ul class="">
			<li><span>Приоритетные места в списке специалистов</span></li>
			<li><span>Доверие и лояльность потенциальных клиентов</span></li>
			<li><span>Уважение коллег</span></li>
			<li><span>Возможность доносить свое авторитетное мнение до многотысячной аудитории портала</span></li>
		</ul>
		<i class="">* Подробные условия — при общении с менеджером проекта</i>
		<div class="clear"></div>
		<div class="btn_conteiner">
			<a href="#" class="btn_grey">Подать заявку</a>
		</div>
	</div>



		<?php // ЕСЛИ ГОСТЬ ?>
		<div class="expert_form login hide <?php if (Yii::app()->user->isGuest) echo 'show_first'; ?>">
			<form action="">
				<div class="guest_hint">
					Войдите или <a href="/site/registration">зарегистируйтесь</a> для того, чтобы стать экспертом
				</div>
				<div class="request_form_item">
					<label>Электронная почта <span class="required">*</span><input type="text" name="User[login]" class="textInput"/></label>
				</div>
				<div class="request_form_item">
					<label>Пароль<input type="password" name="User[password]" class="textInput"/></label>
				</div>
				<div class="spacer"></div>
				<p class="error-title" style="display: none; width: 395px; margin-bottom: -56px;">Такого пользователя не существует или пароль введен неверно</p>
				<div class="btn_conteiner">
					<a href="#" class="btn_grey btn_login">Войти</a>
				</div>
			</form>
		</div>


		<?php // ЕСЛИ АВТОРИЗОВАННЫЙ ?>
		<div class="expert_form request hide <?php if ( ! Yii::app()->user->isGuest) echo 'show_first'; ?>">
			<form action="" id="want_be_expert">
				<div class="request_form_item">
					<label>Имя <span class="required">*</span><input type="text" name="Expert[name]"
											 class="textInput"
											 placeholder="Укажите ваше реальное имя"/></label>
				</div>
				<div class="request_form_item">
					<label>Телефон<input type="text" name="Expert[phone]" class="textInput"
							     placeholder="Телефон"/></label>
				</div>
				<div class="request_form_item">
					<label>В какой области вы эксперт? <span class="required">*</span>
						<select name="Expert[service]" id="service_selector" class="textInput">
							<option value="">Выберите из предложенных</option>
							<?php
							foreach ($services as $service) {
								echo CHtml::tag('option', array('value' => $service->name), $service->name);
							}
							?>
						</select>
					</label>
				</div>
				<div class="request_form_item">
					<span class="your_variant">или предложите свой вариант</span>
					<label class="hide">&nbsp;<br><input type="text" name="Expert[serviceCustom]"
									     class="textInput" placeholder="Ваш вариант"/><i
						class="clear_var"></i></label>

				</div>
				<div class="request_form_item long">
					<label>Ссылка на личный сайт
						<input type="text" class="textInput" name="Expert[url]"
						       placeholder="Укажите ссылку на ваш реальный сайт">
					</label>
				</div>
				<div class="spacer"></div>
				<i class="">* После передачи данных с вами свяжутся в течении 2-ух рабочих дней</i>

				<div class="btn_conteiner">
					<a href="#" class="btn_grey">Отправить</a>
				</div>
			</form>
		</div>

</div>