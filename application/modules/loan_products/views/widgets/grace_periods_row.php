<?php foreach ($grace_periods as $grace_period): ?>
    <tr>
        <td style="text-align: center">
            <?= $grace_period["period"]; ?>
            <input type="hidden" name="period[]" value="<?= $grace_period["period"]; ?>">
        </td>
        <td style="text-align: center">
            <?= $grace_period["qty"]; ?>
            <input type="hidden" name="qty[]" value="<?= $grace_period["qty"]; ?>">
        </td>
        <td style="text-align: center">
            <?= $grace_period["unit"]; ?>
            <input type="hidden" name="unit[]" value="<?= $grace_period["unit"]; ?>">
        </td>
    </tr>
<?php endforeach; ?>