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
            if (in_array($field, ['_data', '_options'])) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(', ', array_filter($value, function ($i) {
                    return $i !== '';
                }));
            }
?>
        <tr>
            <td><?php echo ucfirst($field) ?></td>
            <td><?php echo $value ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
