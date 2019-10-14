<?php $this->pageTitle = 'Избранное — ' . $user->name . ' — MyHome.ru'?>

	<script type="text/javascript">
		$(document).ready(function () {

			var current = false;
			// Название редактируемой группы
			var oldGroupName = '';
			// Ссылка с названия группы
			var groupHref = '';
			// Кол-во элементов в группе
			var groupCount = 0;
			/**
			 * редактирование имени списка избранного
			 */
			$('.content_block').on('click', '.fav_options span', function(){
				if($(this).is('.project_tools_del')){
					var $this = $(this);
					doAction({
						yes: function() {
							var elementLi = $this.parents('li');
							var id_group = elementLi.attr('data-id');

							/**
							 * удаление списка
							 */
							$.get(
								'/member/favorite/delete/id/'+id_group,
								function(response){
									if (response.success) {
										elementLi.remove();
										document.location = '<?php echo $this->createUrl('/users', array('login' => Yii::app()->user->model->login, 'action' => 'favorite'));?>';
									}
									else
										alert('Ошибка удаления');
								}, 'json'
							);
						},
						no: function() {

						}
					}, 'Удалить список?');


				}else{
					var parent = $(this).parents('li');
					if(parent.is('.current')){
						current = true;
						parent.removeClass('current');
					}else{
						current = false;
					}
					parent.addClass('edit');

					var width = parent.width()-10;
					oldGroupName = parent.find('a').text();
					groupHref = parent.find('a').attr('href');
					groupCount = parent.find('.fav_options').prev('span').text();

					parent.html('<input maxlength="40" style = "width:'+width+'px" class="textInput" type="text" value="'+oldGroupName+'"/>');
					parent.find('input').focus();
					parent.find('input').select();
				}

			});


			$('.favorite ul ').on({
				focusout: function(){
					favoriteNameEdit('save');
				},
				keydown: function(e){
					if(e.keyCode==13){
						favoriteNameEdit('save');
					}
					if(e.keyCode==27){
						favoriteNameEdit('cancel');
					}
				}
			}, 'li.edit input');

			/**
			 * Вызывается при попытке сохранить отредактированное имя группы
			 * @param action string имя действия. Доступно два варианта
			 * 	'save' - сохранить новое значение
			 * 	'cancel' - отменить изменение
			 */
			function favoriteNameEdit(action){
				var li = $('.favorite ul li.edit');
				var newValue = li.find('input').val();
				var idGroup = li.attr('data-id');

				var newValue = li.find('input').val();
				if(action == 'cancel')
					newValue = oldGroupName;


				if(current)
					li.addClass('current');

				li.html('<a href="'+groupHref+'">'+newValue+'</a> <span>'+groupCount+'</span><div class="fav_options"><span class="project_tools_edit"><i></i></span><span class="project_tools_del"><i></i></span></div>');
				li.removeClass('edit');

				if(action == 'save') {
					$.get(
						'/member/favorite/update/id/'+idGroup+'/name/'+newValue,
						function(response) {
							if ( ! response.success) {
								alert('Ошибка изменения');
							}
						}, 'json'
					);
				}
			}
		})
	</script>

	<div class="portfolio_head dotted favorite content_block">
		<div class="menu_level2 authorized">
			<ul>
				<li data-id="0" class="general <?php if ($this->selectedGroupId == 0) echo 'current';?>">
					<a href="<?php echo $this->createUrl('/users', array('login'=>$user->login, 'action'=>'favorite'));?>">Общий список</a>
					<span><?php if (isset($itemsCnt[0])) echo $itemsCnt[0];?></span>
				</li>
				<?php if ( ! empty($groups)) : ?>
				<?php foreach($groups as $group) : ?>
					<?php if ($group['id'] == 0) continue; ?>

					<li data-id="<?php echo $group['id'];?>" <?php if ($this->selectedGroupId == $group['id']) echo 'class="current"';?>>
						<a href="<?php echo $this->createUrl('/users', array('login'=>$user->login, 'action'=>'favorite', 'subaction' => $group['id']));?>"><?php echo $group['name'];?></a>
						<span><?php echo isset($itemsCnt[ $group['id'] ]) ? $itemsCnt[ $group['id'] ] : '0'; ?></span>

						<div class="fav_options">
							<span class="project_tools_edit"><i></i></span>
							<span class="project_tools_del"><i></i></span>
						</div>
					</li>
				<?php endforeach; ?>
				<?php endif; ?>

				<li><a class="-icon-plus -skyblue -pseudolink -nodecor create-list" href="#"><i>Создать список</i></a></li>

			</ul>
		</div>
		<div class="clear"></div>
	</div>

	<div class="-grid ">
		<div class="-col-9 -gutter-bottom-dbl">
			<a class="-icon-share -button -button-skyblue -push-right share-button" href="#">Поделиться ссылкой на этот список</a>
		</div>
	</div>


	<?php Yii::app()->getClientScript()->registerScriptFile('/js-new/profile.js');?>

	<div class="-hidden">
		<?/*Копирование сслыки*/?>
		<div class="-white-bg -inset-all -col-7" id="popup-copylink">
			<h2>Поделиться ссылкой на список «<?php echo $groupName; ?>»</h2>
			<form>
				<input class="-block" type="text" value="<?php echo $publicUrl; ?>">
			</form>

			<?php Yii::app()->openGraph->title = $groupName; ?>
			<?php Yii::app()->openGraph->description = Config::SHARE_DEFAULT_MESSAGE;?>
			<?php Yii::app()->openGraph->renderTags();?>

			<?php $this->widget('ext.sharebox.EShareBox', array(
				'view' => 'favorite',
				'url' => $publicUrl,
				'title'=> $groupName,
				'message' => Config::SHARE_DEFAULT_MESSAGE,
				'classDefinitions' => array(
					'facebook' => '-icon-facebook',
					'vkontakte' => '-gutter-left-hf -icon-vkontakte',
					'twitter' => '-icon-twitter',
					'google+' => '-icon-google-plus',
					'odkl' => '-icon-odnoklassniki',
				),
				'exclude' => array('livejournal','pinterest'),
				'htmlOptions' => array('class' => '-gutter-top -inset-top-hf'),
			));?>

		</div>
		<?/*Добавление списка*/?>
		<div class="-white-bg -inset-all -col-7" id="popup-create-list">
			<h2>Создать список</h2>
			<form>
				<input class="-block" type="text" placeholder="Введите название списка">
				<button class="-gutter-top -button -button-skyblue">Создать</button>
			</form>

		</div>
	</div>

	<script type="text/javascript">
		profile.favoriteAcrions();
		profile.setOptions({'userLogin':'<?php echo Yii::app()->user->model->login; ?>'});
	</script>

<?php $this->renderFavoriteList('UploadedFile'); ?>

<?php $this->renderFavoriteList('Interior'); ?>

<?php $this->renderFavoriteList('Interiorpublic'); ?>

<?php $this->renderFavoriteList('Architecture'); ?>

<?php $this->renderFavoriteList('User'); ?>

<?php $this->renderFavoriteList('Product'); ?>

<?php $this->renderFavoriteList('Portfolio'); ?>

<?php $this->renderFavoriteList('MediaKnowledge'); ?>

<?php $this->renderFavoriteList('MediaNew'); ?>
