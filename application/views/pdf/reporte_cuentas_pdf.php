<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 15px; }
        .footer { margin-top: 15px; font-size: 8pt; color: #555; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%" style="border: none; margin-bottom: 10px;">
            <tr>
                <td align="left" width="30%" style="border: none;">
                    <img src="<?= FCPATH ?>application/views/pdf/logo.png" alt="Logo" width="159" height="50">
                </td>
                <td align="right" width="70%" style="border: none; font-size: 10pt;">
                    <strong><?= $this->config->item('company') ?></strong><br>
                    <?= $this->config->item('address') ?><br>
                    Tel: <?= $this->config->item('phone') ?>
                </td>
            </tr>
        </table>
        <div style="text-align: center; margin-top: 10px;">
            <h2><?= $titulo ?></h2>
            <div><strong>Fecha de generación:</strong> <?= $fecha ?></div>
        </div>
    </div>

    <!-- Tabla de datos -->
    <table>
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="12%">Cliente</th>
                <th width="10%">Teléfono</th>
                <th width="15%">Descripción</th>
                <th width="10%" class="text-right">Monto Préstamo</th>
                <th width="10%" class="text-right">Saldo</th>
                <th width="12%">Agente</th>
                <th width="12%">Aprobado por</th>
                <th width="8%">Fecha Aprobación</th>
                <th width="8%">Fecha Pago</th>
                <th width="8%">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($registros)): ?>
                <?php foreach ($registros as $row): ?>
                <tr>
                    <td class="text-center"><?= $row['loan_id'] ?? 'N/A' ?></td>
                    <td><?= htmlspecialchars($row['customer'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['customer_phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                    <td class="text-right"><?= number_format($row['loan_amount'] ?? 0, 2) ?></td>
                    <td class="text-right"><?= number_format($row['loan_balance'] ?? 0, 2) ?></td>
                    <td><?= htmlspecialchars($row['agent'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['approved_by'] ?? '') ?></td>
                    <td class="text-center"><?= $row['formatted_loan_approved_date'] ?? 'N/A' ?></td>
                    <td class="text-center"><?= $row['formatted_payment_date'] ?? 'N/A' ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['loan_status'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="text-center">No se encontraron registros de préstamos aprobados para el mes actual</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Total de registros: <?= count($registros) ?> | 
        Generado el: <?= date('d/m/Y H:i:s') ?>
    </div>
</body>
</html>