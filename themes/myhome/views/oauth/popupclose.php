<script type="text/javascript">
	<?php if (!empty($redirectUrl)) : ?>
		window.opener.document.location.href="<?php echo $redirectUrl; ?>";
		if (window.opener.document.location.hash != '') {
			window.opener.location.reload();
		}
		window.close();
	<?php else : ?>
		window.opener.location.reload();
		window.close();
	<?php endif; ?>
</script>