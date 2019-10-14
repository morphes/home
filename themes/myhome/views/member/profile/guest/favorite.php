<?php $this->pageTitle = 'Избранное — MyHome.ru'?>

<script>
	$(document).ready(function(){
		// Удаление из избранного
		$('.add_this_to_favorite').off('click', '.added');

		$('.add_this_to_favorite').on('click', '.added', function () {
			var $this = $(this);

			doAction({
				'yes':function(){
					var parent = $this.parents('.add_this_to_favorite');

					var itemId = parent.attr('data-item-id');
					var itemModel = parent.attr('data-item-model');

					$.get(
						'/member/favorite/removeitem/itemid/'+itemId+'/itemmodel/'+itemModel,
						function(response){
							if (response.success) {
								parent.find('.added').removeClass('added');
								parent.parents('.item').fadeOut(function(){$(this).remove()});

								favoriteLinkCnt($this.parents('.favorite_item'), 'minus', true);
								favoriteLinkCnt($('.myfavorite'), 'minus');
							}
							else
								alert(response.error);
						}, 'json'
					);


				},
				no: function(){

				}
			}, 'Удалить из избранного?');
		});
	});

</script>


<div class="pathBar">
	<p class="path">
		<a href="/">Главная</a>
	</p>

	<h1>Мое избранное</h1>

	<div class="spacer"></div>
</div>

<div id="left_side">
	<div class="shadow_block padding-18 about_service">
		<p>
			Незарегистрированным пользователям доступна только ограниченная версия «Избранного»:
			сохраненные специалисты и идеи хранятся только в памяти вашего интернет-браузера
			и не доступны с других компьютеров и браузеров.
		</p>
		<p>
			Зарегистрируйтесь, чтобы воспользоваться всеми возможностями «Избранного»:
			создавайте тематические списки избранного, просматривайте выбранных специалистов
			и идеи с любого компьютера.
		</p>

		<br>

		<div class="btn_conteiner left_side_button">
			<a class="btn_grey " href="/site/registration">Зарегистрироваться</a>
		</div>
	</div>
</div>

<div id="right_side">
	<div class="spacer-10"></div>
	<div class="content_block">

		<?php $this->renderFavoriteList('UploadedFile'); ?>

		<?php $this->renderFavoriteList('Interior'); ?>

		<?php $this->renderFavoriteList('Interiorpublic'); ?>

		<?php $this->renderFavoriteList('Architecture'); ?>

		<?php $this->renderFavoriteList('User'); ?>

		<?php $this->renderFavoriteList('Product'); ?>

		<?php $this->renderFavoriteList('Portfolio'); ?>

		<?php $this->renderFavoriteList('MediaKnowledge'); ?>

		<?php $this->renderFavoriteList('MediaNew'); ?>

		<div class="spacer-30"></div>
	</div>

</div>
<div class="clear"></div>
<div class="spacer-30"></div>
