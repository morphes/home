<div id="role_admin" class="-col-2 -inset-bottom-dbl">
	<p class="-gutter-bottom-hf -medium -semibold">Смена роли</p>
	<?php echo CHtml::dropDownList('role', $role, User::$roleNames, array('class'=>'role_change')); ?>
</div>
<?php
$domain = Config::getCookieDomain();
$domain = !empty($domain) ? '\''.$domain.'\'' : 'window.location.hostname';
?>
<script type="text/javascript">
	$('#role_admin .role_change').change(function(){
		CCommon.setCookie('role', this.value, {'path':'/', 'domain':<?php echo $domain; ?>});
		window.location.reload();
	});
</script>