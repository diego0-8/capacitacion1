<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificado — <?php echo htmlspecialchars((string) ($nombreCurso ?? '')); ?></title>
  <style>
    :root {
      --ink: #0f172a;
      --muted: #64748b;
      --border: #e2e8f0;
      --green: #15803d;
      --green-dark: #14532d;
      --cream: #ecfdf5;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
      margin: 0;
      padding: 1.5rem 1.25rem 2rem;
      color: var(--ink);
      line-height: 1.5;
      background: #f1f5f9;
    }
    .noprint {
      max-width: 720px;
      margin: 0 auto 1.25rem;
      padding: 1rem 1.25rem;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 12px;
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
      color: #ecfdf5;
      background: linear-gradient(135deg, var(--green-dark), var(--green));
      border: none;
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(20, 83, 45, 0.35);
    }
    .btn-print:hover { filter: brightness(1.05); }
    .hint {
      flex: 1;
      min-width: 200px;
      font-size: 0.875rem;
      color: var(--muted);
    }
    .sheet {
      max-width: 720px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2.5rem 2rem;
      box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    }
    .badge-wrap {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .badge-wrap svg {
      width: 88px;
      height: 88px;
      filter: drop-shadow(0 6px 12px rgba(21, 128, 61, 0.25));
    }
    h1 {
      margin: 0 0 0.35rem;
      font-size: 1.35rem;
      text-align: center;
      color: var(--green-dark);
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }
    .sub {
      text-align: center;
      color: var(--muted);
      font-size: 0.95rem;
      margin-bottom: 2rem;
    }
    .curso {
      font-size: 1.5rem;
      font-weight: 700;
      text-align: center;
      margin: 0 0 1.25rem;
      color: var(--ink);
      line-height: 1.3;
    }
    .persona {
      font-size: 1.2rem;
      text-align: center;
      margin: 0 0 1.5rem;
      padding: 1rem 1.25rem;
      background: var(--cream);
      border-radius: 12px;
      border: 1px solid rgba(21, 128, 61, 0.2);
    }
    .fecha {
      text-align: center;
      font-size: 0.9rem;
      color: var(--muted);
    }
    @media print {
      body { background: #fff; padding: 0; }
      .noprint { display: none !important; }
      .sheet {
        border: none;
        box-shadow: none;
        border-radius: 0;
        max-width: none;
        padding: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="noprint">
    <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
    <p class="hint">Use la opción del navegador <strong>Guardar como PDF</strong> en el cuadro de impresión para descargar el archivo.</p>
  </div>

  <article class="sheet">
    <div class="badge-wrap" aria-hidden="true">
      <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="">
        <defs>
          <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#22c55e"/>
            <stop offset="100%" style="stop-color:#15803d"/>
          </linearGradient>
        </defs>
        <circle cx="60" cy="60" r="52" fill="url(#g)" opacity="0.15"/>
        <circle cx="60" cy="60" r="44" fill="none" stroke="url(#g)" stroke-width="4"/>
        <path d="M60 28 L72 52 L98 56 L78 74 L82 100 L60 86 L38 100 L42 74 L22 56 L48 52 Z" fill="url(#g)" stroke="#14532d" stroke-width="1.5" stroke-linejoin="round"/>
      </svg>
    </div>
    <h1>Certificado de finalización</h1>
    <p class="sub">Se certifica que ha completado satisfactoriamente el curso</p>
    <p class="curso"><?php echo htmlspecialchars((string) ($nombreCurso ?? '')); ?></p>
    <p class="persona"><?php echo htmlspecialchars((string) ($nombreAsesor ?? '')); ?></p>
    <?php if (!empty($fechaOtorgada)): ?>
      <p class="fecha">Fecha de otorgamiento: <?php echo htmlspecialchars((string) $fechaOtorgada); ?></p>
    <?php else: ?>
      <p class="fecha">Fecha: <?php echo htmlspecialchars(date('d/m/Y')); ?></p>
    <?php endif; ?>
  </article>
</body>
</html>
