<?php if (Yii::app()->getUser()->getFlash('tender_create')) : ?>
<div class="guest_hint">
	Ваш заказ находится на модерации и будет опубликован в течение нескольких часов.
</div>
<?php endif; ?>
<div class="tenders_head">
	<div class="name">Наименование</div>
	<div class="cost">Бюджет</div>
	<div class="response">Откликов</div>
	<div class="respond">Откликнуться</div>
	<div class="clear"></div>
</div>

<div class="respond_conteiner">
	<div class="shadow_block tender_respond">
		<span>Комментарий к отклику</span>
		<textarea name="name" class="textInput " maxlength="1000"></textarea>
		<span>Ориентировочная стоимость, руб.</span>
		<input type="text" class="textInput" maxlength="15">
		<div class="btn_conteiner">
			<a class="btn_grey">Откликнуться</a>
		</div>
	</div>
</div>