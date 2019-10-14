<div class="stat-rows">
	<div class="-col-3">
		<span class="-large">Просмотры профиля</span>
	</div>
	<div class="-col-5">
		<span class="-huge -semibold"><?php echo $statSpecialistModel[StatSpecialist::TYPE_HIT_PROFILE]; ?></span>
		<i class="-gutter-left -icon-question-circle-xs -icon-gray"
		   data-dropdown="profileViews">?</i>

		<div id="profileViews"
		     class="-dropdown">
			<div class="-dropdown-pointer"><i></i></div>
			<div class="-dropdown-content">
				<span class="-small -gray">Общее количество просмотров вашего профиля.</span>
			</div>
		</div>
	</div>
	<div class="-col-3">
		<span class="-block -large">Просмотры контактов</span>
	</div>
	<div class="-col-5">
		<span class="-huge -semibold"><?php echo $statSpecialistModel[StatSpecialist::TYPE_HIT_CONTACTS]; ?></span>
		<i class="-gutter-left -icon-question-circle-xs -icon-gray"
		   data-dropdown="contactsViews">?</i>

		<div id="contactsViews"
		     class="-dropdown">
			<div class="-dropdown-pointer"><i></i></div>
			<div class="-dropdown-content">
				<span class="-small -gray">Количество просмотров раздела «Контакты».</span>
			</div>
		</div>
	</div>
	<div class="-col-3">
		<span class="-block -large">Просмотры проектов</span>
	</div>
	<div class="-col-5">
		<span class="-huge -semibold"><?php echo $statProjectModel[StatProject::TYPE_PROJECT_VIEW]; ?></span>
		<i class="-gutter-left -icon-question-circle-xs -icon-gray"
		   data-dropdown="projectsViews">?</i>

		<div id="projectsViews"
		     class="-dropdown">
			<div class="-dropdown-pointer"><i></i></div>
			<div class="-dropdown-content">
				<span class="-small -gray">Количество просмотров раздела «Портфолио». </span>
			</div>
		</div>
	</div>
</div>

<?php
$userServiceData = $statUserService->getData();
$statProjectModelData = $statProjectModelDp->getData();
?>
<div class="-col-9">
<h2 class="-inline">Список специалистов во всех городах</h2>
<?php echo CHtml::dropDownList('city', $city, array('null' => 'Все города') + array('0' => 'Без города') + $listCity, array('class' => '-gutter-left-hf city-selector')); ?>

<i class="-gutter-left-hf -icon-question-circle-xs"
   data-dropdown="spcCityViews">?</i>

<div id="spcCityViews"
     class="-dropdown">
	<div class="-dropdown-pointer"><i></i></div>
	<div class="-dropdown-content">
					<span class="-small -gray">
						Статистика показов и просмотров вашего профиля в списках
						специалистов по оказываемым услугам и выбранным городам.<br>
						<span class="-strong">Во всех списках</span> — суммарная
						статистика по всем спискам.<br>
						<span class="-strong">Список без города</span> — статистика
						показов профиля в списках, в которых не был выбран город
						(<a href="http://www.myhome.ru/specialist/interior-design">пример</a>).<br>
						<span class="-strong">Город</span> — статистика показов профиля по конкретному городу
						(<a href="http://www.myhome.ru/specialist/interior-design/novosibirsk">пример</a>).
					</span>
	</div>
</div>
<table class="stat-table">
	<col width=185
	     align="left">
	<col width=160
	     align="center">
	<col width=210
	     align="center">
	<col width=185
	     align="center">
	<thead>
	<tr>
		<td><span class="-white">Услуга</span></td>
		<td class="-text-align-center">
			<span class="-white">Показов профиля</span>
			<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
			   data-dropdown="spcProfileViews">?</i>

			<div id="spcProfileViews"
			     class="-dropdown">
				<div class="-dropdown-pointer"><i></i></div>
				<div class="-dropdown-content">
								<span class="-small -gray">
									Количество открытий страниц списков, в которых присутствует ваш профиль (<a href="http://www.myhome.ru/specialist/interior-design/moskva">пример</a>).
									Формат цифры: <span class="-strong">количество показов вашего профиля / количество показов первой страницы списка</span>.
									Самую высокую посещаемость имеет первая страница списка специалистов.
								</span>
				</div>
			</div>
		</td>
		<td class="-text-align-center">
			<span class="-white">Кликов по профилю</span>
			<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
			   data-dropdown="spcListViews">?</i>

			<div id="spcListViews"
			     class="-dropdown">
				<div class="-dropdown-pointer"><i></i></div>
				<div class="-dropdown-content">
					<span class="-small -gray">Количество кликов на ваш профиль из списков специалистов.</span>
				</div>
			</div>
		</td>
		<td class="-text-align-center">
			<span class="-white">CTR, %</span>
			<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
			   data-dropdown="spcCTR">?</i>

			<div id="spcCTR"
			     class="-dropdown">
				<div class="-dropdown-pointer"><i></i></div>
				<div class="-dropdown-content">
								<span class="-small -gray">Отношение количества кликов на ваш профиль
									к количеству показов. Показывает долю посетителей списка, заинтересовавшихся
									вашим профилем. На кликабельность профиля влияет наличие фотографии
									или логотипа, наличие проектов, количество отзывов, средняя оценка,
									положение на странице.
								</span>
				</div>
			</div>
		</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td></td>
		<?php
		$sumShowProfile = 0;
		$sumClickProfile = 0;
		$sumCtr = 0;
		foreach ($userServiceData as $usd) {
			$sumShowProfile = $sumShowProfile + $usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE);
			$sumClickProfile = $sumClickProfile + $usd->getViewData(StatUserService::TYPE_CLICK_PROFILE_SERVICE);
		}
		if ($sumShowProfile > 0) {
			$sumCtr = $sumCtr + round($sumClickProfile / $sumShowProfile * 100, 2);
		}
		?>


		<td class="-text-align-center">
			<span class="-huge -semibold"><?php echo $sumShowProfile; ?></span>
			<!--<span class="-gray -small  -semibold">/ 6 798</span>-->
		</td>
		<td class="-text-align-center">
			<span class="-huge -semibold"><?php echo $sumClickProfile; ?></span>
		</td>
		<td class="-text-align-center">
			<span class="-huge -semibold"><?php echo $sumCtr; ?></span>
		</td>
	</tr>
	<?php foreach ($userServiceData as $usd) { ?>
		<tr>
			<td class="-large"><?php echo Service::model()->findByPk($usd->service_id)->name; ?></td>
			<td class="-text-align-center">
				<span class="-large"><?php echo $usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE); ?></span>
				<!--<span class="-gray -small">/ 6 798</span>-->
			</td>
			<td class="-text-align-center">
				<span class="-large"><?php echo $usd->getViewData(StatUserService::TYPE_CLICK_PROFILE_SERVICE) ?></span>
			</td>
			<td class="-text-align-center"><span class="-large"><?php
					if ($usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE) > 0) {
						echo round($usd->getViewData(StatUserService::TYPE_CLICK_PROFILE_SERVICE) / $usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE) * 100, 2);
					} else {
						echo 0;
					}
					?>

				</span></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php if ($userServiceData) { ?>
	<h2 class="-inline">Проекты в разделе «Идеи»</h2>
	<i class="-gutter-left-hf -icon-question-circle-xs"
	   data-dropdown="prjIdeasViews">?</i>
	<div id="prjIdeasViews"
	     class="-dropdown">
		<div class="-dropdown-pointer"><i></i></div>
		<div class="-dropdown-content">
					<span class="-small -gray">
						Статистика показов и просмотров проектов в разделе «Идеи».
					</span>
		</div>
	</div>
	<table class="stat-table">
		<col width=100
		     align="left">
		<col width=150
		     align="center">
		<col width=150
		     align="center">
		<col width=130
		     align="center">
		<col width=170
		     align="center">
		<thead>
		<tr>
			<td><span class="-white">Проект</span></td>
			<td class="-text-align-center">
				<span class="-white">Показов проектов</span>
				<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
				   data-dropdown="prjViews">?</i>

				<div id="prjViews"
				     class="-dropdown">
					<div class="-dropdown-pointer"><i></i>
					</div>
					<div class="-dropdown-content">
								<span class="-small -gray">
									Количество открытий страниц списков изображений,
									в которых присутствуют ваши проекты (<a href="http://www.myhome.ru/idea/interior/livingroom">пример</a>). Чем больше
									ваших проектов опубликовано в разделе «Идеи», тем выше этот показатель.
								</span>
					</div>
				</div>
			</td>
			<td class="-text-align-center">
				<span class="-white">Кликов на проект</span>
				<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
				   data-dropdown="prjClick">?</i>

				<div id="prjClick"
				     class="-dropdown">
					<div class="-dropdown-pointer"><i></i>
					</div>
					<div class="-dropdown-content">
								<span class="-small -gray">
									Количество кликов на ваш проект из списка проектов.
								</span>
					</div>
				</div>
			</td>
			<td class="-text-align-center">
				<span class="-white">CTR, %</span>
				<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
				   data-dropdown="prjCTR">?</i>

				<div id="prjCTR"
				     class="-dropdown">
					<div class="-dropdown-pointer"><i></i>
					</div>
					<div class="-dropdown-content">
								<span class="-small -gray">
									Отношение количества кликов на ваш проект к количеству показов. Показывает долю посетителей списка, заинтересовавшихся вашим проектом. На кликабельность влияет фотореалистичность, насыщенность и цветовое решение изображения.
								</span>
					</div>
				</div>
			</td>
			<td class="-text-align-center">
				<span class="-white">Добавили в избранное</span>
				<i class="-gutter-left-hf -icon-question-circle-xs -icon-orange"
				   data-dropdown="prjFavorite">?</i>

				<div id="prjFavorite"
				     class="-dropdown">
					<div class="-dropdown-pointer"><i></i>
					</div>
					<div class="-dropdown-content">
								<span class="-small -gray">
									Количество добавлений вашего проекта в избранное посетителями сайта.
								</span>
					</div>
				</div>
			</td>
		</tr>
		</thead>
		<tbody>
		<?php
		$sumShowProject = 0;
		$sumProjectClick = 0;
		$sumCtrProject = 0;
		$sumFavoriteProject = 0;
		foreach ($statProjectModelData as $std) {
			$sumShowProject = $sumShowProject + $std->getViewData(StatProject::TYPE_SHOW_PROJECT_IN_LIST);
			$sumProjectClick = $sumProjectClick + $std->getViewData(StatProject::TYPE_CLICK_PROJECT_IN_LIST);
			$sumFavoriteProject = $sumFavoriteProject + $std->getViewData(StatProject::TYPE_PROJECT_TO_FAVORITES);
		}
		if ($sumShowProject > 0) {
			$sumCtrProject = round($sumProjectClick / $sumShowProject * 100, 2);
		}
		?>
		<tr>
			<td></td>
			<td class="-text-align-center">
				<span class="-huge -semibold"><?php echo $sumShowProject; ?></span>
			</td>
			<td class="-text-align-center">
				<span class="-huge -semibold"><?php echo $sumProjectClick; ?></span>
			</td>
			<td class="-text-align-center">
				<span class="-huge -semibold"><?php echo $sumCtrProject ?></span>
			</td>
			<td class="-text-align-center">
				<span class="-huge -semibold"><?php echo $sumFavoriteProject ?></span>
			</td>
		</tr>
		<?php foreach ($statProjectModelData as $std) { ?>
			<?php if ($std->getProjectStatus()) { ?>

				<tr>
					<td>
						<div class="-relative">
							<?php if ($std->model == 'Architecture') {
								echo CHtml::image($std->getImage(Config::$preview['crop_60']), '', array('class' => '-quad-70'));
							} else {
								echo CHtml::image('/' . $std->getImage(Config::$preview['crop_60']), '', array('class' => '-quad-70'));
							} ?>
							<span>
								<a target="_blank"
								   class="-small"
								   href="<?php echo $std->getLink(); ?>"><?php echo CHtml::encode($std->model_id) ?></a>
							</span>
						</div>
					</td>
					<td class="-text-align-center">
						<span class="-large"><?php echo $std->getViewData(StatProject::TYPE_SHOW_PROJECT_IN_LIST) ?></span>
					</td>
					<td class="-text-align-center">
						<span class="-large"><?php echo $std->getViewData(StatProject::TYPE_CLICK_PROJECT_IN_LIST) ?></span>
					</td>
					<td class="-text-align-center"><span class="-large"><?php
							if ($std->getViewData(StatProject::TYPE_SHOW_PROJECT_IN_LIST) > 0) {
								echo round($std->getViewData(StatProject::TYPE_CLICK_PROJECT_IN_LIST) / $std->getViewData(StatProject::TYPE_SHOW_PROJECT_IN_LIST) * 100, 2);
							} else {
								echo 0;
							}
							?>
				</span></td>
					<td class="-text-align-center">
						<span class="-large"><?php echo $std->getViewData(StatProject::TYPE_PROJECT_TO_FAVORITES); ?></span>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>

		</tbody>
	</table>
<?php } ?>
</div>
