<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Imprimir reporte — <?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></title>
  <style>
    :root {
      --ink: #0f172a;
      --muted: #64748b;
      --border: #e2e8f0;
      --header-bg: #14532d;
      --header-ink: #ecfdf5;
      --row-alt: #f8fafc;
      --accent: #15803d;
    }

    * { box-sizing: border-box; }

    body {
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
      margin: 0;
      padding: 1.5rem 1.25rem 2rem;
      color: var(--ink);
      line-height: 1.45;
      background: #f1f5f9;
    }

    .noprint {
      max-width: 1100px;
      margin: 0 auto 1.25rem;
      padding: 1rem 1.25rem;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1rem;
    }

    .btn-print {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.65rem 1.15rem;
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--header-ink);
      background: linear-gradient(135deg, #14532d, var(--accent));
      border: none;
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(20, 83, 45, 0.35);
      transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .btn-print:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(20, 83, 45, 0.4);
    }

    .btn-print:active {
      transform: translateY(0);
    }

    .btn-print:focus-visible {
      outline: 3px solid rgba(21, 128, 61, 0.45);
      outline-offset: 2px;
    }

    .hint {
      flex: 1;
      min-width: 200px;
      font-size: 0.875rem;
      color: var(--muted);
    }

    .doc {
      max-width: 1100px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.5rem 1.5rem 1.75rem;
      box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
    }

    .doc-header {
      border-bottom: 2px solid var(--header-bg);
      padding-bottom: 1rem;
      margin-bottom: 1.25rem;
    }

    .doc-header h1 {
      margin: 0 0 0.35rem;
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: var(--ink);
    }

    .doc-sub {
      font-size: 1rem;
      color: var(--muted);
      font-weight: 600;
    }

    .doc-meta {
      margin-top: 0.5rem;
      font-size: 0.8rem;
      color: var(--muted);
    }

    .table-wrap {
      overflow-x: auto;
      border: 1px solid var(--border);
      border-radius: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.875rem;
    }

    caption {
      caption-side: top;
      text-align: left;
      font-weight: 700;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      padding: 0 0 0.65rem;
    }

    thead th {
      background: var(--header-bg);
      color: var(--header-ink);
      font-weight: 700;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 0.75rem 0.85rem;
      text-align: left;
      border-bottom: 2px solid #0f3d22;
      white-space: nowrap;
    }

    tbody td {
      padding: 0.7rem 0.85rem;
      border-bottom: 1px solid var(--border);
      vertical-align: top;
    }

    tbody tr:nth-child(even) {
      background: var(--row-alt);
    }

    tbody tr:hover {
      background: #f0fdf4;
    }

    .cell-name strong {
      display: block;
      font-weight: 700;
      color: var(--ink);
    }

    .cell-name .ced {
      font-size: 0.8rem;
      color: var(--muted);
      margin-top: 0.2rem;
    }

    .badge-estado {
      display: inline-block;
      padding: 0.2rem 0.5rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 700;
      background: #f1f5f9;
      border: 1px solid var(--border);
    }

    .num {
      font-variant-numeric: tabular-nums;
      text-align: right;
    }

    .empty {
      text-align: center;
      padding: 2rem 1rem;
      color: var(--muted);
      font-size: 0.95rem;
    }

    @media print {
      body {
        background: #fff;
        padding: 0;
      }

      .noprint {
        display: none !important;
      }

      .doc {
        border: none;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
        max-width: none;
      }

      .doc-header {
        margin-bottom: 1rem;
      }

      tbody tr {
        background: transparent !important;
      }

      tbody tr:hover {
        background: transparent !important;
      }

      thead {
        display: table-header-group;
      }

      tr {
        page-break-inside: avoid;
      }

      table {
        font-size: 8.5pt;
      }

      thead th {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>
  <div class="noprint">
    <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
    <p class="hint">En el cuadro de impresión del navegador elige la opción <strong>Guardar como PDF</strong> o una impresora. Esta barra no aparecerá en el documento impreso.</p>
  </div>

  <div class="doc">
    <header class="doc-header">
      <h1>Reporte del curso</h1>
      <div class="doc-sub"><?php echo htmlspecialchars((string) ($curso['nombre_curso'] ?? '')); ?></div>
      <div class="doc-meta">Generado para impresión / archivo PDF · <?php echo date('Y-m-d H:i'); ?></div>
    </header>

    <?php if (empty($filas)): ?>
      <p class="empty">No hay asesores inscritos en este curso.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <caption>Listado de asesores y progreso</caption>
          <thead>
            <tr>
              <th>Asesor</th>
              <th>Estado</th>
              <th class="num">Progreso</th>
              <th class="num">Módulos</th>
              <th class="num">Quiz</th>
              <th>Evaluación final</th>
              <th>Completado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($filas ?? []) as $f): ?>
              <tr>
                <td class="cell-name">
                  <strong><?php echo htmlspecialchars((string) (($f['nombre_asesor'] ?? '') ?: '(Sin nombre)')); ?></strong>
                  <span class="ced">CC <?php echo htmlspecialchars((string) ($f['cedula_asesor'] ?? '')); ?></span>
                </td>
                <td><span class="badge-estado"><?php echo htmlspecialchars((string) ($f['estado_capacitacion'] ?? '—')); ?></span></td>
                <td class="num"><?php echo (int) ($f['progreso_porcentaje'] ?? 0); ?>%</td>
                <td class="num"><?php echo (int) ($f['modulos_completos'] ?? 0); ?> / <?php echo (int) ($f['modulos_total'] ?? 0); ?></td>
                <td class="num"><?php echo (int) ($f['quices_aprobados'] ?? 0); ?> / <?php echo (int) ($f['quices_activos'] ?? 0); ?></td>
                <td>
                  <?php if (!empty($f['evaluacion_resultado'])): ?>
                    <?php echo htmlspecialchars((string) $f['evaluacion_resultado']); ?> · <?php echo htmlspecialchars((string) $f['evaluacion_puntaje']); ?>/10
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars((string) ($f['fecha_completado'] ?? '—')); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
