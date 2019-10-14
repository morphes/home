<div class="services_filter">
	<div class="lb_head">
		Выбрать услугу<?php
		if ($cityId) {
			$city = City::model()->findByPk($cityId);
			if ($city->prepositionalCase)
				echo ' в '.$city->prepositionalCase;
			else
				echo ' в городе '.$city->name;
		}
		?>
	</div>
	<div class="services_list">
		<div class="services_column">
		<?php

		// Вычисляем минимальное количество элементов в каждом столбце
		$minItems = intval($totalQt/3);

		if (count($services) == 3)
			$minItems = 1;

		// Кол-во элементов, выведенных в столбце
		$outputItems = 0;

		foreach($services as $parentId => $srvs)
		{
			if ($outputItems >= $minItems) {
				echo '</div>';
				echo '<div class="services_column">';
				$outputItems = 0;
			}

			$parent = Service::model()->findByPk($parentId);
			echo CHtml::openTag('ul', array('class' => 'services_level1'));
			echo CHtml::openTag('li');

			echo CHtml::tag('h2', array(), $parent->name);


			echo CHtml::openTag('ul', array('class' => 'services_level2'));
			foreach ($srvs as $srv)
			{
				$class = ($serviceId == $srv->id) ? 'current' : '';

				echo CHtml::openTag('li', array('class' => $class));
				echo CHtml::openTag('div');

				if (isset($city))
					echo CHtml::link($srv->name, $this->createUrl('/specialist/' . $srv->url . '/'.$city->eng_name));
				else
					echo CHtml::link($srv->name, $this->createUrl('/specialist/' . $srv->url));

				echo CHtml::tag('span', array(), $servicesQt[$srv->id]);

				echo CHtml::closeTag('div');
				echo Chtml::closeTag('li');

				$outputItems++;
			}
			echo CHtml::closeTag('ul');


			echo CHtml::closeTag('li');
			echo CHtml::closeTag('ul');

		}
        	?>
		</div>
		<div class="clear"></div>
	</div>
</div>