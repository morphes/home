<?php $this->pageTitle = 'Новости и акции — ' . $store->name; ?>

<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-8">


			<h2>Новости и акции</h2>
			<div class="-grid">
				<!-- pager -->
				<?php
				/* Делаем явный вызов getData() для того, чтобы
				 * вывести постраничку. Иначе он ее не выводит
				 */
				$newsProvider->getData();
				?>
				<div class="-col-8 -text-align-right -inset-bottom -gutter-bottom-dbl -border-dotted-bottom">
					<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $newsProvider->getPagination(),
						'htmlOptions'    => array('class' => '-menu-inline -pager'),
						'maxButtonCount' => 5,
					)); ?>
				</div>
				<!-- eof pager -->
			</div>
			<div class="-grid -inset-top-hf">

				<?php
				/* ---------------------------------------------
				 *  Список новостей
				 * ---------------------------------------------
				 */
				$this->widget('zii.widgets.CListView', array(
					'dataProvider'     => $newsProvider,
					'itemView'         => '_moneyNewsItem',
					'viewData'         => array('store' => $store),
					'enablePagination' => false
				));
				?>

			</div>
			<div class="-grid">
				<!-- pager -->
				<?php
				$cls = ($newsProvider->getTotalItemCount() > 0)
					? '-border-dotted-top'
					: '';
				?>
				<div class="-col-8 -text-align-right -inset-top -gutter-top-dbl <?php echo $cls;?>">
					<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $newsProvider->pagination,
						'htmlOptions'    => array('class' => '-menu-inline -pager'),
						'maxButtonCount' => 5,
					)); ?>
				</div>
				<!-- eof pager -->
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

					$('body')
						.on('change', '.picture_for_news', function() {
							// This starts
							if (this.files != undefined) {
								createImage(this.files[0]);
							}
							else {
								showMessage('Ваш браузер не поддреживает HTML5. Предпросмотр фотографии невозможен.');
							}
						})
						.on('click', '.delete_photo', function(){
							var data = {
								newsId: $(this).parents('form').attr('data-newsId'),
								photoId: $(this).attr('data-photoId')
							};
							$.post(
								'/catalog/store/ajaxMoneyNewsDeletePhoto',
								data,
								function(resp){
									if (resp.success) {
										$('.pic_new').html('');
									} else {
										alert(resp.message);
									}
								}, 'json'
							);
						});

					function createImage(file) {

						var 	image = new Image()
							, preview = $('.pic_new');

						var reader;
						try
						{
							reader = new FileReader();
							var max_file_size = 1048576 * 2;
							if (file.size > max_file_size) {
								showMessage('Размер файла слишком большоий, максимум 2MB');
								return;
							}
						} catch (err) {
							showMessage('Ваш браузер не поддерживает HTML5. Предпросмотр невозможен.');
							return;
						}


						reader.onload = function(e){

							image.src = e.target.result;
							setTimeout(function(){
								var perc, offset;

								if (image.naturalWidth > image.naturalHeight) {
									image.setAttribute('height', 140);

									// Вычисляем смещение фотографии, чтобы она встала по центру
									if (image.naturalHeight > 0) {
										perc = image.naturalHeight / image.naturalWidth * 100;
										offset = parseInt((140 * perc / 100) / 4);
										image.style.marginLeft = '-' + offset + 'px';
									}

								} else {
									image.setAttribute('width', 140);

									// Вычисляем смещение фотографии, чтобы она встала по центру
									if (image.naturalWidth > 0) {
										perc = image.naturalWidth / image.naturalHeight * 100;
										offset = parseInt((140 * perc / 100) / 4);
										image.style.marginTop = '-' + offset + 'px';
									}
								}

								preview.removeClass('-loading').append(image);
							}, 300);
						};

						preview.html('');
						preview.addClass('-loading');

						// Reading the file as a DataURL. When finished,
						// this will trigger the onload function above:
						reader.readAsDataURL(file);

					}

					function showMessage(msg) {
						alert(msg);
					}
				});
			</script>

		</div>
		<div class="-col-3 -skip-1">
			<?php $this->renderPartial('_moneyRightSidebar', array(
				'store'       => $store,
				'showAddNews' => true
			)); ?>
		</div>
		<script>
			minisite.eventActions();
		</script>
	</div>
</div>