<div class="-hidden">
	<div class="popup popup-feedback" id="popup-feedback">
		<div class="popup-header">
			<div class="popup-header-wrapper">
				Обратная связь
			</div>
		</div>
		<div class="popup-body">
			<form id="feedback-form" action="#">

				<p class='hint'>
					Ответы на многие вопросы и подробное описание сайта вы можете найти самостоятельно в разделе <a href="<?php echo $this->createUrl('/help/help/index'); ?>">Помощь по сайту</a>. Если вы не нашли ответ на свой вопрос, задайте его с помощью этой формы. Специалист поддержки ответит вам в течение 24 часов.
				</p>
				<p class="f-userdata">
					<label for="p-feedback-name">
						Ваше имя
					</label>
					<input id="p-feedback-name" class="textInput" value="<?php if (!Yii::app()->user->isGuest) echo Yii::app()->user->model->name;?>">
				</p>
				<p  class="f-userdata f-mail">
					<label for="p-feedback-email">
						Адрес электронной почты
					</label>
					<input id="p-feedback-email" class="textInput" value="<?php if (!Yii::app()->user->isGuest) echo Yii::app()->user->model->email;?>">
				</p>
				<p class="p-feedback-message">
					<label for="p-feedback-message">
						Ваш вопрос
					</label>
					<br>
					<textarea id="p-feedback-message" class="textInput"></textarea>
				</p>
				<p class="p-feedback-files">
					<label for="p-feedback-message">
						Прикрепить файл
					</label>
				<div class="file_list">
					<div><input class="" size=40 type="file"/></div>
				</div>

				<input id="p-feedback-page_url" type="hidden" value="<?php echo Yii::app()->request->hostInfo . Yii::app()->request->url; ?>">

				</p>
				<button type="submit" class="-button -button-skyblue">Отправить</button>
				<p class="error-title"></p>
				<p class="good-title" style="display: none;"></p>
			</form>
		</div>
	</div>
</div>