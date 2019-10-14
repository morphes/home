<?php $this->pageTitle = 'Услуги — ' . $user->name . ' — MyHome.ru'?>

<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CUser.js'); ?>

<script>
        $(function(){

		// Нажатие на кнопку "ГОТОВО" после завершения выбора города.
                $('.city_list_frame .btn_grey').click(function(){
                        $.ajax({
                                url: "/member/profile/updatecity/action/insert/type/" + $('#reg_1').attr('name') + "/id/" + $('#reg_1').attr("data-location-id"),
                                async: false,
				dataType: "json",
                                success: function( data ){
                                        if(data.success)  {
						$('.my_city_list').html(data.list);
                                        }
                                }
                        });

                        $('.simplemodal-close').trigger('click');
                        $('.city-select-body').css('left',0);
                        $('.city-select-body li').removeClass('current');
                        return false;
                });
                $('.del_price').click(function(){
			var $this = $(this)
                        $this.parent().addClass('hide');

                        // удаление файла
                        if($this.is('#del_and_upload')){
                                $.get('/member/profile/deleteprice/id/'+$(this).attr('data-id'), function(response){

                                        if (response.success) {

                                                        $('#price_form').removeClass('hide');

                                        } else {
                                                alert('Ошибка!');
                                        }

                                }, 'json');
                        }else{
                                if($(this).is('#upload_again')){
                                        $('.upload_price').removeClass('error_conteiner');
                                        $('#price_form').removeClass('hide');
                                }else{
                                        $('.upload_links').removeClass('hide');
                                }
                               // $('.upload_links').removeClass('hide');
                        }

                        return false;
                });

	        $('.drop_down:not(.price_range) ul li').click(function(){
		        var ul = $(this).parent();
		        ul.hide();
		        ul.next().val($(this).attr('data-value'));
	        });

		/*выбор ценового сегмента*/
		$('.price_range.drop_down ul li').click(function(){
			var ul = $(this).parent();
			var td = ul.parent();
			var next = td.next();
			if(td.hasClass('required_field')){

				if(next.find('input').val()!=0){
					next.find('span').html('Не указан<i></i>');
					next.find('input').val(0);
				}
				ul.next().val($(this).attr('data-value'));
				initDropDown(td);
			}
		})

		$('h5.myservices').each(function(){
			var tbl = $(this).next();
			tbl.find('tr').each(function(){
				if($(this).find('.price_range.required_field input').val()!=0){
					initDropDown($(this).find('.price_range.required_field'));
				}

			});
		})

		function initDropDown(td){
			var next = td.next();
			next.find('ul li').show();
			next.find('ul li').each(function(){

				next.find('span.disabled').animate({opacity:1},200,function(){
					$(this).removeClass('disabled');
				});

				if($(this).attr('data-value') == td.find('input').val()){
					$(this).hide();
				}
				if(td.find('input').val() == 1 && $(this).attr('data-value') == 3){
					$(this).hide();
				}
				if(td.find('input').val() == 3 && $(this).attr('data-value') == 1){
					$(this).hide();
				}
			})
		}

		user.selectCity();
        });
</script>

<?php echo CHtml::beginForm('', 'POST', array('name'=>'my_service_list'))?>
	<div class="my_city_list shadow_block">
		<?php $this->renderPartial('//member/profile/specialist/_ownCityList', array('locations'=>$locations)); ?>
	</div>
	<div class="clear"></div>

	<?php if ( !empty($errorCode) ) : ?>
	<div class="spacer-18"></div>
	<div class="error_conteiner service_error">
		<?php foreach ($errorCode as $value) : ?>
		<div class="error_content">
			<?php echo $value; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<table class="my_th">
		<tr>
			<td class="service_name">Услуга</td>
			<td class="price_range">Приоритетный ценовой сегмент</td>
			<td class="price_range">Дополнительный ценовой сегмент
				<div class="c-hinter"><i></i>
					<p class="c-hinter-text">
						Вы можете назначать своим услугам только смежные ценовые сегменты.
						К примеру, если вы выбрали сегмент «премиум» в качестве основного,
						выбрать дополнительным «эконом» вы уже не сможете.</p>


				</div>
			</td>
			<td class="service_exp  drop_down">Стаж</td>
		</tr>
	</table>

	<?php
	end($services);
	$last = key($services);

	reset($services);
	$first = key($services);
	?>
	<?php foreach ($services as $key => $service) : ?>



		<?php if ($service->parent_id == 0) : ?>

			<?php echo ($key != $first) ? '</table></div>' : ''; ?>

			<h5 class="myservices">
				<?php echo CHtml::checkBox('User[services][' . $service->id . '][id]', isset($checkedServices[$service->id]),array('class'=>'all_servise_check', 'id'=>'check_' . $service->id, 'value'=>$service->id)); ?>
				<a href="#"><?php echo $service->name; ?></a>
				<span class="list_status open"></span>
				<span class="act_service_count"></span>
			</h5>

			<?php echo '<div class="tbl_conteiner ' . (($key == $last) ? 'last' : ''). '"><table class="my_serv_list" id="list_' . $service->id . '">'; ?>

		<?php else : ?>
				<?php $experience = isset($checkedServices[$service->id]) ? $checkedServices[$service->id]['experience'] : '0'; ?>
				<?php $segment = isset($checkedServices[$service->id]) ? $checkedServices[$service->id]['segment'] : '0'; ?>
				<?php $segment_supp = isset($checkedServices[$service->id]) ? $checkedServices[$service->id]['segment_supp'] : '0'; ?>


				<tr <?php if (isset($checkedServices[$service->id])) echo 'class="active"'; ?> >
					<td class="service_name">
						<?php echo CHtml::checkBox('User[services][' . $service->id . '][id]',
									isset($checkedServices[$service->id]),
									array('class'=>'servise_check', 'value'=>$service->id)); ?>
						<?php echo $service->name; ?>
					</td>
					<?php // segment
						$class = isset($checkedServices[$service->id]['errorSegment']) ? 'validate_error' : '';
					?>
					<td class="service_exp  drop_down price_range required_field <?php echo $class; ?>">
						<span class="exp_current"><?php echo Config::$segmentName[ $segment ]; ?><i></i></span>
						<ul>
							<?php foreach (Config::$segmentName as $key_suka=>$value) :?>
								<li data-value="<?php echo $key_suka;?>"><?php echo $value;?></li>
							<?php endforeach; ?>
						</ul>
						<?php echo CHtml::hiddenField('User[services]['.$service->id.'][segment]', $segment, array('id'=>false)); ?>
					</td>
					<?php // segment_supp
						$class = isset($checkedServices[$service->id]['errorSegmentSupp']) ? 'required_field validate_error' : '';
					?>
					<td class="service_exp  drop_down price_range <?php echo $class; ?>">
						<span class="exp_current disabled"><?php echo Config::$segmentName[ $segment_supp ]; ?><i></i></span>
						<ul>
							<?php foreach (Config::$segmentName as $key_suka=>$value) :?>
								<li data-value="<?php echo $key_suka;?>"><?php echo $value;?></li>
							<?php endforeach; ?>
						</ul>
						<?php echo CHtml::hiddenField('User[services]['.$service->id.'][segment_supp]', $segment_supp, array('id'=>false)); ?>
					</td>
					<?php // experience
						$class = isset($checkedServices[$service->id]['errorExp']) ? 'validate_error' : '';
					?>
					<td class="service_exp drop_down required_field <?php echo $class; ?>">
						<span class="exp_current"><?php echo Config::$experienceType[ $experience ]; ?><i></i></span>

						<ul>
							<?php foreach (Config::$experienceType as $key_suka=>$value) :?>
								<li data-value="<?php echo $key_suka;?>"><?php echo $value;?></li>
							<?php endforeach; ?>
						</ul>
						<input type="hidden" value="<?php echo $experience; ?>" id = "servece_<?php echo $service->id; ?>" name='User[services][<?php echo $service->id; ?>][experience]'>
					</td>
				</tr>
		<?php endif; ?>

		<?php echo ($key == $last) ? '</table></div>' : ''; ?>


	<?php endforeach; ?>

		<div class="spacer-18"></div>
	<div class="btn_conteiner serv_save">
		<input type="submit" value="Сохранить изменения" class="btn_grey"/>
	</div>
<?php echo CHtml::endForm(); ?>

<a name="price"></a>
<div class="spacer-30"></div>

<div class="upload_price shadow_block <?php echo !empty($uploadErrors['price_list']) ? 'error_conteiner' : ''; ?>">

	<?php if (Yii::app()->user->model->data->price_list) : ?>
		<?php
			$uf = UploadedFile::model()->findByPk(Yii::app()->user->model->data->price_list);
			$priceDate = date('d.m.Y', $uf->create_time);
		?>
		<div class="del_links" >
			<span class=""><i></i></span><a class="pricelist uploaded" href="<?php echo UserData::getUrlDownloadPrice($uf->id); ?>">Прайс-лист от <?php echo $priceDate;?></a>
			<a href="#" class="del_price" data-id="<?php echo $uf->id;?>" id="del_and_upload">Удалить и загрузить другой</a>
			<a href="#" class="del_price" data-id="<?php echo $uf->id;?>">Удалить</a>
			<div class="clear"></div>
		</div>
	<?php endif; ?>

	<div class="upload_links <?php echo (Yii::app()->user->model->data->price_list) ? 'hide' : ''; ?> <?php echo !empty($uploadErrors['price_list']) ? 'hide' : ''; ?>">
		<span class=""><i></i></span>
		<a class="pricelist not_uploaded" href="#">Загрузить прайс-лист</a>
		<div class="c-hinter" data-index="0">
			<i></i>
			<p class="c-hinter-text">
				Добавьте прайс-лист, чтобы потенциальные клиенты могли ознакомиться с расценками на ваши услуги. Допускаются файлы размером не более 3 Мб в форматах xls, doc, pdf, rtf или zip.
			</p>
		</div>
	</div>

	<div id="price_form" class="hide">
		<form action="#price" method="POST" enctype="multipart/form-data">
			<?php echo CHtml::activeFileField(UserData::model(), 'price_list', array('id' => 'file_input', 'size' => '49')); ?>
			<div id="file_mask">
				<input type="text" class="textInput" id="file_input_text" />
			</div>
			<div class="btn_conteiner small disabled">
				<input type="submit" value="Загрузить" disabled="disabled" class="btn_grey"/>
			</div>
		</form>
	</div>


	<?php if ( ! empty($uploadErrors['price_list'])) : ?>

		<div class="error_content">
			<?php foreach($uploadErrors['price_list'] as $item) : ?>
				<?php echo $item; ?>
			<?php endforeach; ?>
			<a id="upload_again" class="del_price" href="#">Попробовать еще раз</a>
		</div>

	<?php endif; ?>

	<div class="clear"></div>
</div>


<?php // Попап выбора города для услуг ?>
<div class="-hidden">
        <div class="popup popup-city-select" id="city-select">
                <div class="city-select-header">Выберите <span class="area">страну</span></div>
                <div class="city-select-body">
                        <div class="city_list_frame">
                                <div class="city_list" id="country">

                                        <ul class="important_reg">
                                                <?php foreach(Country::getImportantList() as $country) : ?>
                                                        <li data-location-id='<?php echo $country->id; ?>'><?php echo $country->name; ?></li>
                                                <?php endforeach; ?>
                                        </ul>

                                        <ul>
                                                <?php foreach(Country::getList(false) as $country) : ?>
                                                        <li data-location-id='<?php echo $country->id; ?>'><?php echo $country->name; ?></li>
                                                <?php endforeach; ?>
                                        </ul>
                                </div>
                        </div>
                        <div class="city_list_frame">
                                <div class="city_list" id="region">

                                </div>
                                <span>&larr;</span><a class="back_to_change" id="change_country" href="#">Сменить страну</a>
                        </div>
                        <div class="city_list_frame">
                                <div class="city_list" id="city">

                                </div>
                                <span>&larr;</span><a class="back_to_change" id="change_country" href="#">Сменить регион</a>
                                <a class="btn_grey" href="#">Готово</a>
                        </div>
                        <div class="clear"></div>
                </div>
        </div>
</div>
