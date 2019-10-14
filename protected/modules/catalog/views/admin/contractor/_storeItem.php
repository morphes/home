<?php
/**
 * @var $store Store
 */
?>
<tr class="odd">
	<td><?php echo $store->id; ?></td>
	<td><?php echo $store->name.' ('.$store->address.')'; ?></td>
	<td><?php echo $store->site; ?></td>
	<td class="button-column">
		<a class="delete" title="Удалить" data-id="<?php echo $store->id; ?>">
			<img src="/img/admin/delete.png" alt="Удалить">
		</a>
	</td>
</tr>