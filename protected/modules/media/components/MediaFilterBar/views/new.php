<div class="ideas_showed">
	<?php
	if ($this->totalItemCount == 0) {
		echo 'Новости не найдены';
	} else {
		echo CFormatterEx::formatNumeral($this->totalItemCount, array('Показана', 'Показано', 'Показано'), true);
		echo ' ';
		echo CFormatterEx::formatNumeral($this->totalItemCount, array('новость', 'новости', 'новостей'));
	}
	?>
</div>
<form id="filter_form" class='architecture_filter' method="" action="<?php echo MediaNew::getSectionLink();?>">
	<div class="shadow_block padding-18 knowledge_filter">
		<div class="filter_item">
			<h3 class="block_head">Темы</h3>
			<?php
			if ( ! empty($this->themes)) {
				$html = '';
				$html = CHtml::openTag('ul', array('class' => 'filter_items f_theme set_input'));
				foreach ($this->themes as $theme) {

					$liOptions = array('data-id' => $theme->id);
					if ($theme->id == $this->filter['f_theme'])
						$liOptions['class'] = 'checked';

					$html .= CHtml::openTag('li', $liOptions);
					$html .= CHtml::link($theme->name);
					$html .= CHtml::tag('i', array('class' => 'clear_item'), '', true);
					$html .= CHtml::closeTag('li');
				}
				$html .= CHtml::closeTag('ul');

				echo $html;
			}
			?>
			<input type="hidden" name="f_theme" value="<?php echo $this->filter['f_theme'];?>" />
		</div>
		<div class="filter_item last">
			<h3 class="block_head">Кому это интересно</h3>
			<ul class="filter_items set_input">
				<?php
				$cls = ($this->filter['f_whom'] == MediaNew::WHOM_USER) ? 'class="checked"' : '';
				?>
				<li data-id="<?php echo MediaNew::WHOM_USER;?>" <?php echo $cls;?>><a href="#">Владельцам квартир</a><i class="clear_item"></i></li>

				<?php
				$cls = ($this->filter['f_whom'] == MediaNew::WHOM_SPEC) ? 'class="checked"' : '';
				?>
				<li data-id="<?php echo MediaNew::WHOM_SPEC;?>" <?php echo $cls;?>><a href="#">Специалистам</a><i class="clear_item"></i></li>
			</ul>
			<input type="hidden" name="f_whom" value="<?php echo $this->filter['f_whom'];?>" />
		</div>



		<input type="hidden" name="pagesize" id="elements_on_page" value="<?php echo $this->pageSize;?>">

            	<input type="hidden" name="f_viewtype" value="<?php echo $this->filter['f_viewtype'];?>">

		<div class="clear_filter">
			<i></i><a class="#" href="#" title="Отменить выбор">Сбросить фильтр</a>
		</div>
	</div>
</form>

<script type="text/javascript">
	// Клик на свойство в фильтре
	$('ul.set_input li').click(function(){
		var $this = $(this);

		var id = $this.data('id');

		$this.parents('ul').next('input').val(id);
		$this.parents('form').submit();

		return false;
	});

	// Нажатие на крестик в фильтре
	$('ul.set_input li i.clear_item').click(function(){
		var $this = $(this);

		$this.parents('ul').next('input').val('');
		$this.parents('form').submit();

		return false;
	});

	// Сброс фильтра
	$('.clear_filter a').click(function(){
		var $form = $(this).parents('form');
		$form.find('input').each(function(index, element){
			$(element).val('');
		});
		$form.submit();

		return false;
	});
</script>