<?php
/**
 * @var $vendor Vendor
 */
?>
<tr class="odd">
	<td><?php echo $vendor->id; ?></td>
	<td><?php echo $vendor->name; ?></td>
	<td><?php echo $vendor->site; ?></td>
	<td class="button-column">
		<a class="delete" title="Удалить" data-id="<?php echo $vendor->id; ?>">
			<img src="/img/admin/delete.png" alt="Удалить">
		</a>
	</td>
</tr>