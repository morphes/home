<script type="text/javascript">
	function changeBuildingType()
	{
		$('#filter_form').append('<input type="hidden" name="change_build_type" value="true">');
		$('#filter_form').submit();
	}
</script>

<div class="ideas_showed">
	<?php echo CFormatterEx::formatNumeral($ideaCount, array('Показан', 'Показано', 'Показано'), true);?>
	<span><?php echo $ideaCount;?></span>
	<?php echo CFormatterEx::formatNumeral($ideaCount, array('вариант', 'варианта', 'вариантов'), true);?>
</div>

<?php echo CHtml::form('/idea/catalog/index', 'get', array('id' => 'filter_form')); ?>
<div class="shadow_block padding-18 ideas_filter">

	<?php
	echo CHtml::hiddenField('filter', '0');
	echo CHtml::hiddenField('ideatype', Config::ARCHITECTURE);
	echo CHtml::hiddenField('sortby', $selected['sortType'], array('id'=>'sort_elements'));
	echo CHtml::hiddenField('pagesize', $selected['pageSize'], array('id'=>'elements_on_page'));
	echo CHtml::hiddenField('page', Yii::app()->request->getParam('page'));
	?>

	<div class="filter_hint">
		<i></i>
		Показать <a class="" href="#" onclick="$('#filter_form').submit(); return false;"><span>22</span> варианта</a>
	</div>

	<div class="drop_down filter_item">
		<p>Тип объекта</p>
		<?php
		$objectsLi = '';
		$currentObject = '&nbsp;';
		if ($objects) {
			foreach($objects as $obj)
			{
				if ($obj->id == $selected['object_type']) {
					$currentObject = $obj->option_value;
					$cls = 'active';
				} else {
					$cls = '';
				}

				$objectsLi .= CHtml::tag('li', array('data-value' => $obj->id, 'class' => $cls), $obj->option_value, true);
			}
		}
		?>
		<span class="exp_current"><?php echo $currentObject;?><i></i></span>
		<ul class="set_input" data-callback="changeBuildingType">
			<?php echo $objectsLi; ?>
		</ul>
		<?php echo CHtml::hiddenField('object_type', $selected['object_type'], array('id' => 'object_type')); ?>
	</div>

	<div class="room_type filter_item">
		<?php
		$buildingsLi = '';
		$currentBuilding = '&nbsp;';
		if ($buildings) {
			foreach($buildings as $build) {
				$buildingsLi .= '<li><input value="'.$build->id.'" type="checkbox"><a href="#">'.$build->option_value.'</a></li>';
			}
		}
		?>
		<ul class="visible_types">
			<?php echo $buildingsLi; ?>
		</ul>
		<?php echo CHtml::hiddenField('build_type', $selected['build_type'], array('id' => 'build_type')); ?>
	</div>

	<div class="drop_down filter_item">
		<p>Материал</p>
		<?php
		$materialLi = '';
		$currentMaterial = '&nbsp;';
		if ($materials) {
			foreach($materials as $item)
			{
				if ($selected['material'] == $item->id) {
					$currentMaterial = $item->option_value;
					$cls = 'active';
				} else {
					$cls = '';
				}

				$materialLi .= CHtml::tag('li', array('data-value' => $item->id, 'class' => $cls), $item->option_value, true);
			}
		}
		?>
		<span class="exp_current"><?php echo $currentMaterial;?><i></i></span>
		<ul class="set_input" data-callback="getCount">
			<li data-value="" <?php if ($currentMaterial == '&nbsp;') echo 'class="active"';?>>&nbsp;</li>
			<?php echo $materialLi; ?>
		</ul>
		<?php echo CHtml::hiddenField('material', $selected['material'], array('id' => 'object_material'));?>
	</div>

	<input type="hidden" name="elements_on_page" id= "elements_on_page" value=""/>
	<input type="hidden" name="sort_elements" id= "sort_elements" value=""/>
	<div class="btn_conteiner yellow">
		<a class="btn_grey" onclick="return formSend();" href="#">Показать<span><?php echo $ideaCount;?></span><?php echo CFormatterEx::formatNumeral($ideaCount, array('идею', 'идеи', 'идей'), true); ?></a>
	</div>
</div>
</form>