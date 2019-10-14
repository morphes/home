<?php $this->pageTitle = $news->title . ' — Новости и акции — ' . $store->name; ?>

<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-8">

			<h2>
				<?php echo $news->title;?>
				<span class="controls -gray" data-newsId="<?php echo $news->id;?>">
					<i class="-icon-pencil-xs"></i>
					<i class="-icon-cross-circle-xs detail"></i>
				</span>
			</h2>
			<script>
				minisite.eventActions();
			</script>

			<p class="-large -gray"><?php echo date('d.m.Y', $news->create_time);?></p>
			<div class="article_text">
				<div>
					<?php
					if ($news->preview) {
						echo CHtml::image(
							'/' . $news->preview->getPreviewName(StoreNews::$preview['width_620'], array('width' => 620)),
							$news->title
						);
					}
					?>
				</div>
				<div>
					<?php echo nl2br($news->content);?>
				</div>
			</div>
			<div class="-border-all -border-rounded -inset-left-hf -inset-right-hf -inset-top -inset-bottom">
				<div class="-col-wrap">

					<?php
					/* -------------------------------------
					 *  Избранное
					 * -------------------------------------
					 */
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId'   => $news->id,
						'modelName' => get_class($news),
						'viewHeart' => 'favoriteStoreNews'
					));?>
				</div>
				<div class="-col-wrap -push-right -gray">
					<span class="-gutter-right-hf">Поделиться:</span>
					<?php $this->widget('ext.sharebox.EShareBox', array(
						'view'             => 'folders',
						// url to share, required.
						'url'              => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,

						// A title to describe your link, required.
						'title'            => $store->name,

						// A small message for post
						'message'          => Amputate::getLimb($store->about, 500, '...'),
						'classDefinitions' => array(
							'facebook'  => '-icon-facebook',
							'vkontakte' => '-icon-vkontakte',
							'twitter'   => '-icon-twitter',
							'google+'   => '-icon-google-plus',
							'odkl'      => '-icon-odnoklassniki',
						),
						'exclude'          => array('pinterest', 'livejournal'),
						'htmlOptions'      => array('class' => '-gutter-bottom-dbl -gray'),
					));?>
				</div>
			</div>


			<?php
			/* -----------------------------------------------------
			 *  Комментарии
			 * -----------------------------------------------------
			 */
			$this->widget('application.components.widgets.WComment', array(
				'model'        => $news,
				'hideComments' => !$news->getCommentsVisibility(),
				'showCnt'      => 0,
				'showRating'   => false,
				'guestComment' => true,
				'view'         => '//widget/comment/storeNews/main',
			));?>

		</div>
		<div class="-col-3 -skip-1">
			<?php $this->renderPartial('_moneyRightSidebar', array(
				'store' => $store
			)); ?>
		</div>
	</div>
</div>


<?php
/* -----------------------------------------------------
 *  Форма добавления публикации
 * -----------------------------------------------------
 */
?>
<div class="-col-9 event-add-form -hidden">
	<?php $this->renderPartial('//catalog/store/_moneyNewsPopup', array(
		'data' => new StoreNews()
	)); ?>
</div>

<?php // Скрытый инпут, в котором лежит ID магазина. СЛужебная инфа ?>
<input type="hidden" id="storeId" value="<?php echo $store->id;?>">

<script>
	$(function(){
		function uploadFile(file, url, newsId, callback) {
			var xhr = new XMLHttpRequest();
			var formData = new FormData();
			xhr.onreadystatechange = function(){
				if(this.readyState == 4) {
					delete file;
					delete this;
					if (callback != undefined) {
						callback(this.responseText);
					}
				}
			};
			xhr.open("POST", url);
			formData.append("StoreNews[image]", file);
			formData.append("newsId", newsId);
			xhr.send(formData);
		}

		$('body').on('change', '.picture_for_news', function(){
			var file = this.files[0];
			var form = $(this).parents('form');

			uploadFile(file, "/catalog/store/AjaxMoneyNewsImageUpload", form.attr('data-newsId'), function(response){
				response = $.parseJSON(response);
				if (response.success) {
					var img = $('<img>');
					img.attr('src', response.url);
					form.find('span').html(img);
				} else {
					alert(response.message);
				}
			});
		});
	});
</script>