<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <style>
        body { font-family: Arial; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f2f2f2; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .text-right { text-align: right; }
        .header { margin-bottom: 15px; }
        .footer { margin-top: 15px; font-size: 8pt; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h2><?= $titulo ?></h2>
        <p>Generado el: <?= $fecha ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Sucursal</th>
                <th>Dirección</th>
                <!-- Agrega más columnas según tu tabla -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $row): ?>
            <tr>
                <td><?= $row['branch_id'] ?? '' ?></td>
                <td><?= htmlspecialchars($row['branch_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['branch_address'] ?? '') ?></td>
                <!-- Ajusta según las columnas de tu tabla -->
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Total registros: <?= count($registros) ?>
    </div>
</body>
</html>