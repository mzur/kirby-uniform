<table>
	<thead>
		<tr>
			<th>Field</th>
			<th>Value</th>
		</tr>
	</thead>
	<tbody>
<?php
		foreach ($data as $field => $value):
			if (str::startsWith($field, '_')) continue;
?>
		<tr>
			<td><?php echo ucfirst($field) ?></td>
			<td><?php echo $value ?></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>