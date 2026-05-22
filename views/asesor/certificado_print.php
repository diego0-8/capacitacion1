<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificado — <?php echo htmlspecialchars((string) ($nombreCurso ?? '')); ?></title>
  <style>
    :root {
      /* Colores base (CRM): azul oscuro + dorado */
      --crm-ink: #0c1929;
      --crm-gold: #c8a24a;
      --crm-wine: #7a1f2b;
      --paper: #fbf7ef;
      --shadow: rgba(0, 0, 0, 0.22);
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; }
    body {
      font-family: "Georgia", "Times New Roman", Times, serif;
      padding: 0;
      padding-bottom: 1rem;
      color: var(--crm-ink);
      background: #e8eef4;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .noprint {
      max-width: none;
      margin: 0 0 0.65rem;
      padding: 0.85rem 1rem;
      background: #fff;
      border: 1px solid rgba(15, 23, 42, 0.12);
      border-radius: 12px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.75rem 1rem;
    }
    .btn-print {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.65rem 1.05rem;
      font-size: 0.95rem;
      font-weight: 700;
      color: #fff;
      background: #0f766e;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      box-shadow: 0 10px 22px rgba(15, 118, 110, 0.25);
    }
    .btn-print:hover { filter: brightness(1.04); }
    .hint {
      flex: 1;
      min-width: 220px;
      font-size: 0.9rem;
      color: rgba(15, 23, 42, 0.75);
      margin: 0;
    }
    .page {
      /* Llena el ancho útil del navegador; altura proporcional A4 horizontal */
      width: 100vw;
      max-width: min(1123px, 100%);
      aspect-ratio: 297 / 210;
      margin: 0 auto;
      position: relative;
      background: var(--paper);
      overflow: hidden;
      border-radius: 0;
      box-shadow: 0 8px 36px rgba(15, 23, 42, 0.12);
      border: none;
    }
    .bg {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      background-image: url('<?php echo htmlspecialchars((string) ($empresaFondoUrl ?? '')); ?>');
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
      filter: saturate(0.92) contrast(1.03);
    }
    /* Franjas decorativas tipo modelo marco CRM (rellenan cantos hasta el borde) */
    .deco {
      position: absolute;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
    }
    .deco::before {
      content: "";
      position: absolute;
      left: -18%;
      top: -12%;
      width: 72%;
      height: 118%;
      background: radial-gradient(ellipse 60% 70% at 28% 32%, rgba(122, 31, 43, 0.22), transparent 72%);
      border-radius: 50%;
    }
    .deco::after {
      content: "";
      position: absolute;
      right: -20%;
      bottom: -35%;
      width: 82%;
      height: 118%;
      background: radial-gradient(ellipse 55% 65% at 72% 75%, rgba(12, 25, 41, 0.18), transparent 70%);
      border-radius: 50%;
    }
    .accent-gold-tr {
      position: absolute;
      right: -5%;
      top: -8%;
      width: 48%;
      height: 92%;
      background: radial-gradient(ellipse 50% 55% at 88% 12%, rgba(200, 162, 74, 0.18), transparent 68%);
      pointer-events: none;
    }
    .accent-gold-bl {
      position: absolute;
      left: -10%;
      bottom: -42%;
      width: 62%;
      height: 112%;
      background: radial-gradient(ellipse 52% 58% at 18% 88%, rgba(200, 162, 74, 0.16), transparent 70%);
      pointer-events: none;
    }
    .tint {
      position: absolute;
      inset: 0;
      /* Centro más claro solo para texto; los bordes dejan verse casi todo el foto-fondo */
      background:
        radial-gradient(ellipse 105% 90% at 50% 48%, rgba(251, 247, 239, 0.88) 0%, rgba(251, 247, 239, 0.35) 45%, transparent 72%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(12, 25, 41, 0.03));
      pointer-events: none;
    }
    .overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: stretch;
      justify-content: stretch;
      /* Margen exterior del pie de página sobre el fondo */
      padding: 3.25% 4.25% 3.85% 4.25%;
    }
    .content {
      width: 100%;
      max-width: none;
      flex: 1;
      margin: 0 auto;
      position: relative;
      /* Zona segura única para todo el texto (dentro del marco dorado) */
      padding: clamp(16px, 2.8vmin, 34px) clamp(20px, 3.8vmin, 48px)
        clamp(22px, 3.2vmin, 40px) clamp(20px, 3.8vmin, 48px);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 0;
      text-align: center;
      color: var(--crm-ink);
    }
    .mainBlock {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0.25rem 0 0;
      width: 100%;
      max-width: min(42rem, 92%);
      margin: 0 auto;
    }
    /* Marco estilo certificado (como referencia) */
    .frame {
      position: absolute;
      inset: 0;
      pointer-events: none;
    }
    .frame::before {
      content: "";
      position: absolute;
      inset: clamp(10px, 1.05%, 16px);
      border: 2px solid rgba(200, 162, 74, 0.62);
      border-radius: 2px;
    }
    .corner {
      position: absolute;
      width: clamp(44px, 6.8vmin, 78px);
      height: clamp(44px, 6.8vmin, 78px);
      border: 3px solid rgba(200, 162, 74, 0.85);
    }
    /* Esquinas alineadas con el rectángulo interior del marco */
    .corner.tl {
      top: clamp(10px, 1.05%, 16px);
      left: clamp(10px, 1.05%, 16px);
      border-right: 0;
      border-bottom: 0;
    }
    .corner.tr {
      top: clamp(10px, 1.05%, 16px);
      right: clamp(10px, 1.05%, 16px);
      border-left: 0;
      border-bottom: 0;
    }
    .corner.bl {
      bottom: clamp(10px, 1.05%, 16px);
      left: clamp(10px, 1.05%, 16px);
      border-right: 0;
      border-top: 0;
    }
    .corner.br {
      bottom: clamp(10px, 1.05%, 16px);
      right: clamp(10px, 1.05%, 16px);
      border-left: 0;
      border-top: 0;
    }

    .headerRow {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      gap: 0.35rem;
      margin: 0.15rem auto 0;
      width: 100%;
    }
    .title {
      margin: 0;
      font-size: clamp(1.75rem, 4.8vmin, 2.95rem);
      letter-spacing: 0.06em;
      text-transform: uppercase;
      font-weight: 700;
      color: #141414;
      text-shadow: 0 1px 0 rgba(255, 255, 255, 0.75);
    }
    .empresa {
      margin: 0;
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.09em;
      font-size: 0.95rem;
      color: rgba(12, 25, 41, 0.85);
      text-align: center;
      max-width: 28rem;
    }
    .nit {
      margin: 0.2rem 0 0;
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
      font-size: 0.82rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      color: rgba(12, 25, 41, 0.72);
      text-align: center;
      max-width: 28rem;
    }
    .divider {
      height: 2px;
      width: 100%;
      max-width: 28rem;
      margin: 1.35rem auto 1.1rem;
      background: linear-gradient(90deg, transparent, rgba(12, 25, 41, 0.55), transparent);
    }
    .bodyText {
      margin: 0;
      font-size: 1.05rem;
      color: rgba(12, 25, 41, 0.86);
      line-height: 1.6;
      font-family: "Georgia", "Times New Roman", Times, serif;
    }
    .nombre {
      margin: 0.7rem 0 0.25rem;
      font-size: 1.55rem;
      font-weight: 800;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }
    .cedulaDoc {
      margin: 0 0 0.35rem;
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      color: rgba(12, 25, 41, 0.82);
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
    }
    .line {
      width: 78%;
      height: 1px;
      margin: 0.2rem auto 0.85rem;
      background: rgba(12, 25, 41, 0.35);
    }
    .curso {
      margin: 0;
      font-size: 1.15rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .footerRow {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: clamp(0.75rem, 2vw, 1.75rem);
      margin-top: 0;
      padding-top: clamp(0.65rem, 2vmin, 1.25rem);
      padding-bottom: 0;
      width: 100%;
      max-width: min(42rem, 92%);
      margin-left: auto;
      margin-right: auto;
      padding-left: clamp(6px, 1.2vmin, 12px);
      padding-right: clamp(6px, 1.2vmin, 12px);
      font-family: "Georgia", "Times New Roman", Times, serif;
      box-sizing: border-box;
    }
    .footerRow .fechaCol {
      flex: 1 1 auto;
      min-width: min(260px, 48%);
      max-width: 58%;
      text-align: left;
    }
    .footerRow .firmaCol {
      flex: 0 1 auto;
      min-width: 0;
      max-width: min(220px, 42%);
      margin-left: auto;
    }
    .fecha {
      margin: 0;
      font-size: 0.98rem;
      color: rgba(12, 25, 41, 0.78);
    }
    .firma {
      width: 100%;
      margin: 0;
      max-width: 100%;
      text-align: center;
      color: rgba(12, 25, 41, 0.85);
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
    }
    .firma .firmaLine {
      height: 1px;
      background: rgba(12, 25, 41, 0.45);
      margin-bottom: 0.35rem;
    }
    .firma .firmaLabel {
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.03em;
    }
    @page { size: A4 landscape; margin: 0; }
    @media print {
      body { padding: 0; padding-bottom: 0; background: #fff; }
      .noprint { display: none !important; }
      .page {
        width: 297mm;
        height: 210mm;
        max-width: none;
        aspect-ratio: auto;
        margin: 0;
        border-radius: 0;
        box-shadow: none;
        border: none;
      }
      .overlay { padding: 9mm 11mm 12mm 11mm; }
      .content {
        padding: 11mm 12mm 12mm 12mm;
      }
    }
  </style>
</head>
<body>
  <div class="noprint">
    <button type="button" class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
    <p class="hint">Use la opción del navegador <strong>Guardar como PDF</strong> en el cuadro de impresión para descargar el archivo.</p>
  </div>

  <article class="page" aria-label="Certificado">
    <div class="bg" aria-hidden="true"></div>
    <div class="deco" aria-hidden="true"></div>
    <div class="accent-gold-tr" aria-hidden="true"></div>
    <div class="accent-gold-bl" aria-hidden="true"></div>
    <div class="tint" aria-hidden="true"></div>
    <div class="overlay">
      <div class="content">
        <div class="frame" aria-hidden="true">
          <span class="corner tl"></span>
          <span class="corner tr"></span>
          <span class="corner bl"></span>
          <span class="corner br"></span>
        </div>

        <div class="mainBlock">
          <div class="headerRow">
            <p class="title">Certificado</p>
            <p class="empresa"><?php echo htmlspecialchars((string) ($empresaNombre ?? '')); ?></p>
            <?php if (!empty($empresaNit)): ?>
              <p class="nit">NIT <?php echo htmlspecialchars((string) $empresaNit); ?></p>
            <?php endif; ?>
          </div>

          <div class="divider"></div>

          <p class="bodyText">Se certifica que</p>
          <p class="nombre"><?php echo htmlspecialchars((string) ($nombreAsesor ?? '')); ?></p>
          <?php if (!empty($documentoAsesor)): ?>
            <p class="cedulaDoc"><?php echo htmlspecialchars((string) $documentoAsesor); ?></p>
          <?php endif; ?>
          <div class="line" aria-hidden="true"></div>
          <p class="bodyText">ha completado satisfactoriamente el curso</p>
          <p class="curso"><?php echo htmlspecialchars((string) ($nombreCurso ?? '')); ?></p>
        </div>

        <div class="footerRow">
          <div class="fechaCol">
            <?php if (!empty($fechaCertificado)): ?>
              <p class="fecha">Fecha de otorgamiento: <?php echo htmlspecialchars((string) $fechaCertificado); ?></p>
            <?php endif; ?>
          </div>
          <div class="firmaCol">
            <div class="firma" aria-label="Firma">
              <div class="firmaLine"></div>
              <div class="firmaLabel">Coordinación de Capacitación</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </article>
</body>
</html>
