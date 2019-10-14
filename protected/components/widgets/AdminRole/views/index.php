<div id="role_admin">
	<label>Смена роли</label>
	<div><?php echo CHtml::dropDownList('role', $role, User::$roleNames, array('class'=>'role_change')); ?></div>
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