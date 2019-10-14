<?php
/**
 * @var $cs CClientScript
 * @var $dataProvider
 */
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile('/js/CUser.js');
$cs->registerCssFile('/css/user.css');

?>

<div class="gallery-210 bm_promo_list">
	<?php /** @var $item MallPromo */
	foreach ($dataProvider->getData() as $item) : ?>
		<?php $class = ($item->status==MallPromo::STATUS_DISABLED) ? ' off' : ''; ?>
		<div class="item<?php echo $class; ?>" data-id="<?php echo $item->id; ?>">
			<div class="autor_functions">
				<span class="-inline"><?php echo CHtml::checkBox('', $item->status == MallPromo::STATUS_ACTIVE, array('class'=>'chk_banner')); ?></span>
			<span class="-inline -push-right -gray">
				<a href="javascript:void(0);" onclick="user.bannerEditForm(<?php echo $item->id; ?>);" class="-icon-pencil-xs -icon-only"></a>
				<a href="javascript:void(0);" class="-icon-cross-circle-xs -icon-only"></a>
			</span>
			</div>
			<div class="item_photo">
				<a href="<?php echo $item->url; ?>">
					<?php echo CHtml::image('/'.$item->getPreview(MallPromo::$preview['crop_220']),
							$item->name,
							array('class'=>'-quad-220', 'width'=>200, 'height'=>220)
					); ?>
				</a>
			</div>
			<div class="item_descript">
				<a href="<?php echo $item->url; ?>" class="item_name"><?php echo $item->name; ?></a>
				<span class="-block -small -gray"><?php echo $item->url; ?></span>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="item new_item">
		<div class="item_photo">
			<a href="javascript:void(0);" onclick="user.bannerEditForm();"><span class="-acronym -skyblue">Добавить баннер</span></a>
		</div>
	</div>
	<div class="clear"></div>
</div>

<div class="forms">
	<div id="popup"></div>
	<form class="-col-9 -form-inline banner-edit-form -hidden" enctype="multipart/form-data" method="post">
		<h2 class="-giant">Добавить баннер</h2>
		<div class="-col-2 -semibold -large -text-align-right -inset-right-hf">Изображение</div>
		<div class="-col-4 -gutter-bottom-dbl">
			<span class="show-image"></span>
			<a class="-file-input -button -button-skyblue -relative -gutter-bottom-hf">
				Выбрать файл
				<input type="file" onchange="changeInput(this);" class="picture_for_banner" name="MallPromo[file]">
			</a>
		</div>
		<div class="-col-2 -gray -small -inset-left">Рекомендуемый размер изображения 990&#215;420px</div>
		<div class="-col-2 -semibold -large -text-align-right -inset-right-hf">Название</div>
		<div class="-col-6 -gutter-bottom-dbl">
			<input class="-col-6" type="text" maxlength="255" name="MallPromo[name]">
		</div>
		<div class="-col-2 -semibold -large -text-align-right -inset-right-hf">Ссылка на альбом</div>
		<div class="-col-6 -gutter-bottom-dbl">
			<input class="-col-6" type="text" maxlength="255" name="MallPromo[url]">
			<label class="-checkbox -gutter-top-dbl -gutter-bottom"><?php echo CHtml::checkBox('active', false); ?><span>Активен</span></label>
		</div>
		<input type="hidden" name="mall_id" value="<?php echo $mall->id; ?>">
		<div class="-col-6 -skip-2">
			<button type="button" class="-button -button-skyblue -huge -semibold" onclick="submitForm(); return false;">Добавить</button>
			<a href="javascript:void(0);" class="-red -gutter-left">Отмена</a>
		</div>
	</form>
</div>
<script type="text/javascript">
	user.sortPortfolioBanners();

	var File;

	function submitForm()
	{
		if (typeof File == 'undefined') {
			return false;
		}
		var tmpData = $('.banner-edit-form').serializeArray();
		var data = new FormData();
		for (var i in tmpData) {
			data.append(tmpData[i].name, tmpData[i].value);
		}
		data.append('MallPromo[file]', File);

		$.ajax({
			async:false,
			url: '/catalog/mall/update',
			data: data,
			dataType:'json',
			processData: false,
			contentType: false,
			type:'post',
			success:function(response){
				if (response.success) {}
				window.location.reload();
			},
			error: function(response){
				if (response.message) {
					alert(response.message);
				} else {
					window.location.reload();
				}
			}
		});
		return false;
	}

//	$('.banner-edit-form').on('change', '.picture_for_banner', function(){
	function changeInput(context) {
		var form = $(context).parents('form');
		showFile(form, context.files[0], function(){});
	};

	function showFile(form, file, callback) {
		var f = file;

		if (!f.type.match('image.*')){
			alert('Файл ' + f.name + ' не является изображением.');
			if(callback != undefined) callback();
			return;
		}
		File = file;

		var reader = new FileReader();
		reader.onload = (function(theFile)
		{
			return function(e)
			{
				var th = this;
				var img = new Image();
				img.onload = function()
				{
					var span = form.find('.show-image');
					span.empty();
					$img = $(img);

					$img.css({
						'height':'140px',
						'width':'auto',
						'display':'block'
					});
					$img.appendTo(span);
					var width = $img.width();
					var spanWidth = span.width();
					if (width <= spanWidth) {
						$img.css({'margin':'0px auto'});
					} else {
						var dw = (width-spanWidth)/2;
						$img.css({'margin':'0px '+'-'+dw+'px'});
					}

					delete reader;
					if(callback != undefined) callback();
				};
				img.src = e.target.result;
			};
		})(f);
		reader.readAsDataURL(f);
	}

</script>