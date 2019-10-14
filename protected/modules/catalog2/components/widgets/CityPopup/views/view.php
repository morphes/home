<?php
/**
 * @var $city City
 * @var $geoCity City
 * @var $cityUrlPos integer
 */
$jsOptions = array(
	'cityUrlPos' => $cityUrlPos,
);
if ($city instanceof City) {
	$jsOptions['citySet'] = true;
}

$domain = Config::getCookieDomain();
if (!empty($domain))
	$jsOptions['domain'] = $domain;

$htmlOptions['id'] = 'city_selector';
?>
<?php echo CHtml::openTag('div', $htmlOptions); ?>
	<i class="-icon-location-s -red"></i>
	<?php if ($city instanceof City) : ?>
		<span class="-acronym -gutter-right-qr -large -strong"
		      data-dropdown="citySelector"
		      data-dropdown-right="true"
		      onclick="_gaq.push(['_trackEvent','catCityChange','выбор города']); return true;">
							<?php echo $city->name; ?>
						</span>
		<a href="#" class="-icon-cross-circle-xs -icon-medium -icon-only -icon-gray"></a>
	<?php else: ?>
		<span class="-acronym -gutter-right-qr -large -strong"
		      data-dropdown="citySelector"
		      data-dropdown-right="true"
		      onclick="_gaq.push(['_trackEvent','catCityChange','выбор города']); return true;">
							Выбрать город
						</span>
	<?php endif; ?>

	<div id="citySelector" class="-dropdown">
		<div class="-dropdown-pointer"><i></i></div>
		<div class="-dropdown-content">
			<?php if ($city instanceof City) { ?>
				<div>
					<input id="cityInput" type="text" value="<?php echo $city->name; ?>"><i class="-icon-cross-circle-xs -gray clear-autocomplete"></i>
					<span class="-block -inset-top-hf -small -gray"><?php echo $changeText; ?></span>
				</div>
			<?php } elseif ($geoCity instanceof City) { ?>
				<div>
					<span class="-block -large -strong">Вы из <?php echo $geoCity->genitiveCase; ?>?</span>
					<a href="#" class="-button -button-skyblue" onclick="CCommon.changeUrl('<?php echo $geoCity->eng_name; ?>'); return false;">Да, все верно</a><span class="-acronym -gutter-left toggle-next">Выбрать другой город</span>
				</div>
				<div class="-hidden">
					<input id="cityInput" type="text" class="textInput"><i class="icon-cross clear-autocomplete"></i>
					<span class="-block -inset-top-hf -small -gray"><?php echo $changeText; ?></span>
				</div>
			<?php } else { ?>
				<div>
					<input id="cityInput" type="text" class="textInput"><i class="icon-cross clear-autocomplete"></i>
					<span class="-block -inset-top-hf -small -gray"><?php echo $changeText; ?></span>
				</div>
			<?php } ?>
		</div>
	</div>
<?php echo CHtml::closeTag('div'); ?>

<script type="text/javascript">
	$(function(){
		CCommon.setOptions(<?php echo json_encode($jsOptions, JSON_NUMERIC_CHECK); ?>);
		CCommon.citySelector();
		CCommon.geoIp('<?php echo $cookieName; ?>');
	});
</script>