
<div class="-col-9">
	<p class="-large -gutter-bottom-hf">К сожалению, в вашем городе еще нет товаров этой категории.<br>
					    Рекомендуем вам посмотреть товары в ближайших городах:</p>
	<ul class="-menu-block -rarefied">
		<?php foreach ($cities as $cityItem) {
			$cityObj = $cityItem['city'];
			if ($cityObj->id == $city->id)
				continue;
			echo CHtml::tag('li', array(),
				CHtml::link($cityObj->name,
					$this->createUrl('/catalog/category/list', array('eng_name' => $model->eng_name, 'city_name'=>$cityObj->eng_name)),
					array('class'=>'-red')
				).' '.CHtml::tag('span', array('class'=>'-gray'), '('.number_format($cityItem['count'],0,'.',' ').')')
			);
		}
		?>
	</ul>
	<p class="-large -gutter-top">Также вы можете посмотреть <a onclick="CCommon.setUrl('catalog'); return false;">каталог всех товаров</a>, представленных на MyHome.</p>
</div>
