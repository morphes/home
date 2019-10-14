
<?php $userServiceData = $statUserService->getData(); ?>

<tr>
	<td> </td>
	<?php
	$sumShowProfile = 0;
	$sumClickProfile = 0;
	$sumCtr = 0;
	foreach($userServiceData as $usd) {
		$sumShowProfile = $sumShowProfile + $usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE);
		$sumClickProfile = $sumClickProfile + $usd->getViewData(StatUserService::TYPE_CLICK_PROFILE_SERVICE);

		if ($usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE) > 0) {
			$sumCtr = $sumCtr + round($usd->getViewData(StatUserService::TYPE_CLICK_PROFILE_SERVICE) / $usd->getViewData(StatUserService::TYPE_SHOW_PROFILE_SERVICE) * 100, 2);
		}
	}
	?>


	<td class="-text-align-center">
		<span class="-huge -semibold"><?php echo $sumShowProfile; ?></span>
		<!--<span class="-gray -small  -semibold">/ 6 798</span>-->
	</td>
	<td class="-text-align-center"><span class="-huge -semibold"><?php echo $sumClickProfile; ?></span></td>
	<td class="-text-align-center"><span class="-huge -semibold"><?php echo $sumCtr; ?></span></td>
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