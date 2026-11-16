<?php
declare(strict_types=1);

/**
 * Arte do Constelar: todos os desenhos ficam aqui, em SVG, e são reutilizados no site (<use>)
 * e no jogo (que rasteriza estes mesmos símbolos em texturas).
 *
 * Cores variáveis usam classes, definidas em estilo.css e redefinidas pelo jogo:
 *   .c-cor / .t-cor      cor da constelação (preenchimento / traço)
 *   .c-papel / .t-papel  "papel" (jaleco, roupas)
 */
function sprites(): void
{
    static $feito = false;
    if ($feito) {
        return;
    }
    $feito = true;
    ?>
<svg class="sprites" width="0" height="0" aria-hidden="true" focusable="false">
<defs id="sprites-defs">

<!-- Contorno de adesivo (borda de papel ao redor do desenho) -->
<filter id="f-adesivo" x="-20%" y="-20%" width="140%" height="140%" color-interpolation-filters="sRGB">
  <feGaussianBlur in="SourceAlpha" stdDeviation="2.6"/>
  <feComponentTransfer result="borda"><feFuncA type="linear" slope="40" intercept="-4"/></feComponentTransfer>
  <feFlood flood-color="#f2efe7" result="cor"/>
  <feComposite in="cor" in2="borda" operator="in"/>
  <feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>
</filter>

<!-- ============================================================ Lume, a estrela-guia -->
<symbol id="s-lume" viewBox="0 0 120 108">
  <g stroke="#141414" stroke-linejoin="round" stroke-linecap="round">
    <path d="M60 6L74.1 36.6 107.6 40.6 82.8 63.4 89.4 96.5 60 80 30.6 96.5 37.2 63.4 12.4 40.6 45.9 36.6Z" fill="#ffd23f" stroke-width="4"/>
    <ellipse cx="50.5" cy="52" rx="6.2" ry="8" fill="#fff" stroke-width="2.6"/>
    <ellipse cx="69.5" cy="52" rx="6.2" ry="8" fill="#fff" stroke-width="2.6"/>
    <path d="M53 66q7 6.5 14 0" fill="none" stroke-width="3.2"/>
  </g>
  <circle cx="52" cy="54" r="3.1" fill="#141414"/><circle cx="71" cy="54" r="3.1" fill="#141414"/>
  <circle cx="41" cy="63" r="3.6" fill="#ff8a3d"/><circle cx="79" cy="63" r="3.6" fill="#ff8a3d"/>
</symbol>

<!-- ============================================================ Estrela-cientista (personagem) -->
<g id="ep-corpo" stroke="#141414" stroke-linejoin="round" stroke-linecap="round">
  <!-- pernas e sapatos -->
  <path d="M42 112v10M58 112v10" fill="none" stroke-width="4"/>
  <rect x="35" y="120" width="11" height="5.5" rx="2.75" fill="#141414" stroke="none"/>
  <rect x="54" y="120" width="11" height="5.5" rx="2.75" fill="#141414" stroke="none"/>
  <!-- braços (contorno + miolo) -->
  <path d="M34 75L23 95M66 75L80 61" fill="none" stroke-width="10.5"/>
  <path class="t-papel" d="M34 75L23 95M66 75L80 61" fill="none" stroke-width="5.2"/>
  <circle class="c-cor" cx="22" cy="97.5" r="4.6" stroke-width="2.6"/>
  <circle class="c-cor" cx="82" cy="58.5" r="4.6" stroke-width="2.6"/>
  <!-- jaleco -->
  <path class="c-papel" d="M34 70Q34 62 42 62H58Q66 62 66 70L71 110Q71.5 114 67.5 114H32.5Q28.5 114 29 110Z" stroke-width="3"/>
  <path class="c-cor" d="M42.5 62L50 77 57.5 62Z" stroke-width="2.4"/>
  <path d="M50 77V114" fill="none" stroke-width="2"/>
  <path d="M36.5 93h8.5v8h-8.5z" fill="none" stroke-width="2"/>
  <path class="t-cor" d="M39.5 89.5v6" fill="none" stroke-width="2.6"/>
  <circle cx="55" cy="89" r="1.7" fill="#141414" stroke="none"/>
  <circle cx="55" cy="100" r="1.7" fill="#141414" stroke="none"/>
  <!-- cabeça-estrela -->
  <path class="c-cor" d="M50 4L60.6 25.4 84.2 28.9 67.1 45.6 71.2 69.1 50 58 28.8 69.1 32.9 45.6 15.8 28.9 39.4 25.4Z" stroke-width="3.2"/>
</g>

<symbol id="s-ep-0" viewBox="0 0 100 128"><!-- dormindo (estrela apagada) -->
  <use href="#ep-corpo"/>
  <g stroke="#141414" fill="none" stroke-linecap="round" stroke-width="2.6">
    <path d="M38.5 38q4.5 4 9 0M52.5 38q4.5 4 9 0"/>
  </g>
  <ellipse cx="50" cy="48" rx="2.8" ry="2.2" fill="#141414"/>
</symbol>

<symbol id="s-ep-1" viewBox="0 0 100 128"><!-- sorriso -->
  <use href="#ep-corpo"/>
  <g stroke="#141414" stroke-linecap="round">
    <ellipse cx="43" cy="37" rx="4.6" ry="5.8" fill="#fff" stroke-width="2"/>
    <ellipse cx="57" cy="37" rx="4.6" ry="5.8" fill="#fff" stroke-width="2"/>
    <path d="M44 46.5q6 5.2 12 0" fill="none" stroke-width="2.6"/>
  </g>
  <circle cx="44" cy="38.4" r="2.3" fill="#141414"/><circle cx="58" cy="38.4" r="2.3" fill="#141414"/>
</symbol>

<symbol id="s-ep-2" viewBox="0 0 100 128"><!-- óculos -->
  <use href="#ep-corpo"/>
  <g stroke="#141414" stroke-linecap="round">
    <circle cx="42.3" cy="38" r="6.6" fill="#fff" stroke-width="2.4"/>
    <circle cx="57.7" cy="38" r="6.6" fill="#fff" stroke-width="2.4"/>
    <path d="M48.9 38h2.2" fill="none" stroke-width="2.4"/>
    <path d="M45 48.6q5 3.8 10 0" fill="none" stroke-width="2.5"/>
  </g>
  <circle cx="43.4" cy="38.6" r="2.2" fill="#141414"/><circle cx="58.8" cy="38.6" r="2.2" fill="#141414"/>
</symbol>

<symbol id="s-ep-3" viewBox="0 0 100 128"><!-- olhos fechados, rindo -->
  <use href="#ep-corpo"/>
  <g stroke="#141414" stroke-linecap="round" stroke-linejoin="round">
    <path d="M38.5 39.5q4.5-6 9 0M52.5 39.5q4.5-6 9 0" fill="none" stroke-width="2.6"/>
    <path d="M44 44.5h12a6 6 0 0 1-12 0Z" fill="#141414" stroke-width="2"/>
  </g>
  <ellipse cx="50" cy="48.6" rx="3" ry="1.7" fill="#ff8fd0"/>
</symbol>

<symbol id="s-ep-4" viewBox="0 0 100 128"><!-- curiosa -->
  <use href="#ep-corpo"/>
  <g stroke="#141414" stroke-linecap="round">
    <circle cx="43" cy="37.5" r="5.4" fill="#fff" stroke-width="2"/>
    <circle cx="57" cy="37.5" r="5.4" fill="#fff" stroke-width="2"/>
    <path d="M38.5 29.5l7-1.6M54.5 27.9l7 1.6" fill="none" stroke-width="2.2"/>
  </g>
  <circle cx="44.6" cy="36.2" r="2.4" fill="#141414"/><circle cx="58.6" cy="36.2" r="2.4" fill="#141414"/>
  <ellipse cx="50" cy="48" rx="3" ry="2.6" fill="#141414"/>
</symbol>

<!-- ============================================================ Exploradora (jogadora) -->
<symbol id="s-astronauta" viewBox="0 0 100 128">
  <g stroke="#141414" stroke-linejoin="round" stroke-linecap="round">
    <path d="M73 15l5-9" fill="none" stroke-width="3"/>
    <circle cx="78.6" cy="5" r="3.6" fill="#ffd23f" stroke-width="2.4"/>
    <rect x="23" y="60" width="54" height="42" rx="11" fill="#b69cff" stroke-width="3"/>
    <path d="M40 104v15M60 104v15" fill="none" stroke-width="13"/>
    <path d="M40 104v15M60 104v15" fill="none" stroke="#f2efe7" stroke-width="7.4"/>
    <rect x="31.5" y="115" width="17" height="9.5" rx="4.2" fill="#6a2cff" stroke-width="2.8"/>
    <rect x="51.5" y="115" width="17" height="9.5" rx="4.2" fill="#6a2cff" stroke-width="2.8"/>
    <path d="M30 74L17 93M70 74L83 93" fill="none" stroke-width="12.5"/>
    <path d="M30 74L17 93M70 74L83 93" fill="none" stroke="#f2efe7" stroke-width="7"/>
    <circle cx="16" cy="95" r="5.6" fill="#6a2cff" stroke-width="2.8"/>
    <circle cx="84" cy="95" r="5.6" fill="#6a2cff" stroke-width="2.8"/>
    <rect x="29" y="58" width="42" height="50" rx="13" fill="#f2efe7" stroke-width="3"/>
    <rect x="40" y="80" width="20" height="13" rx="3.5" fill="#6a2cff" stroke-width="2.4"/>
    <circle cx="50" cy="36" r="29" fill="#f2efe7" stroke-width="3.2"/>
    <rect x="29" y="23" width="42" height="27" rx="13" fill="#6a2cff" stroke-width="3"/>
  </g>
  <circle cx="46" cy="86.5" r="1.9" fill="#ffd23f"/><circle cx="54" cy="86.5" r="1.9" fill="#3ddc97"/>
  <path d="M36.5 34.5q1.5-6 8-6.5" fill="none" stroke="#b69cff" stroke-width="3.4" stroke-linecap="round"/>
  <circle cx="50" cy="29.5" r="1.9" fill="#b69cff"/>
</symbol>

<!-- ============================================================ Telescópio do observatório -->
<symbol id="s-telescopio" viewBox="0 0 240 236">
  <g stroke="#141414" stroke-linejoin="round" stroke-linecap="round">
    <path d="M118 150L70 226M118 150L170 226M118 150V230" fill="none" stroke-width="11"/>
    <path d="M118 150L70 226M118 150L170 226M118 150V230" fill="none" stroke="#f2efe7" stroke-width="5"/>
    <g transform="rotate(-34 118 138)">
      <rect x="34" y="124" width="26" height="28" rx="5" fill="#141414" stroke-width="3"/>
      <rect x="52" y="114" width="132" height="48" rx="9" fill="#f2efe7" stroke-width="4"/>
      <rect x="92" y="114" width="20" height="48" fill="#6a2cff" stroke-width="3.5"/>
      <rect x="136" y="114" width="12" height="48" fill="#ffd23f" stroke-width="3.5"/>
      <rect x="176" y="104" width="34" height="68" rx="9" fill="#6a2cff" stroke-width="4"/>
      <rect x="204" y="110" width="12" height="56" rx="6" fill="#4cc9f0" stroke-width="3.5"/>
      <rect x="84" y="96" width="62" height="14" rx="6" fill="#ffd23f" stroke-width="3.5"/>
      <path d="M98 110v4M132 110v4" fill="none" stroke-width="4"/>
    </g>
    <circle cx="118" cy="150" r="15" fill="#ffd23f" stroke-width="4"/>
    <circle cx="118" cy="150" r="4.5" fill="#141414" stroke="none"/>
  </g>
</symbol>

<!-- ============================================================ Símbolos das constelações (48x48) -->
<symbol id="i-telescopio" viewBox="0 0 48 48">
  <g stroke="#141414" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round">
    <path d="M22 28L14 43M22 28L30 43M22 28V43" fill="none"/>
    <path class="c-cor" d="M7.6 26.2L32.4 10.4 38 19.2 13.2 35Z"/>
    <path class="c-papel" d="M32.4 10.4L37.6 7.1 43.2 15.9 38 19.2Z"/>
    <path class="c-papel" d="M4.2 28.4L7.6 26.2 13.2 35 9.8 37.2Z"/>
    <circle cx="22" cy="28" r="3.2" fill="#141414" stroke="none"/>
  </g>
</symbol>
<symbol id="i-atomo" viewBox="0 0 48 48">
  <g stroke="#141414" stroke-width="2.4" fill="none">
    <ellipse cx="24" cy="24" rx="20" ry="7.6"/>
    <ellipse cx="24" cy="24" rx="20" ry="7.6" transform="rotate(60 24 24)"/>
    <ellipse cx="24" cy="24" rx="20" ry="7.6" transform="rotate(120 24 24)"/>
  </g>
  <circle class="c-cor" cx="24" cy="24" r="5.2" stroke="#141414" stroke-width="2.4"/>
  <circle cx="44" cy="24" r="2.6" fill="#141414"/><circle cx="14" cy="6.7" r="2.6" fill="#141414"/><circle cx="14" cy="41.3" r="2.6" fill="#141414"/>
</symbol>
<symbol id="i-celula" viewBox="0 0 48 48">
  <g stroke="#141414" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round">
    <path class="c-cor" d="M24 4.5C33 3.5 43 9.5 43.5 21 44 31 38.5 42 27 43.5 15.500 45 5 38 4.500 27 4 16 13 5.500 24 4.500Z"/>
    <circle class="c-papel" cx="27" cy="25" r="8.2"/>
    <circle cx="28.5" cy="26" r="2.6" fill="#141414" stroke="none"/>
    <path d="M12.500 20.500q3.500-2.500 5 1.500M15 32q3.500 1 4.500 4M33.500 11q3 .5 4 3.500" fill="none" stroke-width="2.4"/>
  </g>
</symbol>
<symbol id="i-frasco" viewBox="0 0 48 48">
  <g stroke="#141414" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round">
    <path class="c-papel" d="M19.500 5.500v12.500L7.200 39.200Q5.500 43 9.500 43h29Q42.500 43 40.800 39.200L28.500 18V5.500Z"/>
    <path class="c-cor" d="M13.100 29h21.800l5.900 10.200Q42.500 43 38.500 43h-29Q5.500 43 7.200 39.200Z"/>
    <path d="M16.500 5.500h15" fill="none"/>
    <circle cx="20" cy="36" r="1.8" fill="#141414" stroke="none"/><circle cx="27.500" cy="33.500" r="1.300" fill="#141414" stroke="none"/>
  </g>
</symbol>
<symbol id="i-pintura" viewBox="0 0 48 48">
  <g stroke-linejoin="round" stroke-linecap="round">
    <path class="c-papel" d="M9 6.500L33 4.500 43.500 15 41.500 37.500 28 43.500 8.500 40 4.500 22Z" stroke="#141414" stroke-width="2.6"/>
    <g class="t-cor" fill="none" stroke-width="3.4">
      <path d="M24 17.500v12M24 20.500L15.500 13M24 20.500L32.500 13M24 29.500L17.500 37.500M24 29.500L30.500 37.500"/>
    </g>
    <circle class="c-cor" cx="24" cy="12.800" r="3.300"/>
  </g>
</symbol>
<symbol id="i-compasso" viewBox="0 0 48 48">
  <g stroke="#141414" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round" fill="none">
    <path d="M24 12L11.500 41M24 12L36.500 41"/>
    <path d="M6 36.500Q24 47 42 36.500" stroke-dasharray="1 5.200"/>
    <path class="c-papel" d="M17.500 27h13v4.600h-13z"/>
    <circle class="c-cor" cx="24" cy="9" r="5.200"/>
  </g>
</symbol>
<symbol id="i-estrela" viewBox="0 0 48 48">
  <path class="c-cor" d="M24 3.500L30.300 17 45 19 34.200 29.200 37 44 24 36.800 11 44 13.800 29.200 3 19 17.700 17Z" stroke="#141414" stroke-width="2.6" stroke-linejoin="round"/>
</symbol>

<!-- ============================================================ Ícones de interface (24x24, cor do texto) -->
<symbol id="u-estrela" viewBox="0 0 24 24"><path d="M12 1.500c.9 5.800 3.700 8.600 9.500 9.500-5.800.9-8.600 3.700-9.500 9.500-.9-5.800-3.700-8.600-9.500-9.500 5.800-.9 8.600-3.700 9.500-9.500Z" fill="currentColor"/></symbol>
<symbol id="u-mapa" viewBox="0 0 24 24"><path d="M9 4L3 6.500v13.500L9 17.500l6 2.500 6-2.500V4L15 6.500ZM9 4v13.500M15 6.500V20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/></symbol>
<symbol id="u-album" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"><rect x="4" y="3.500" width="16" height="17" rx="2.500"/><path d="M8 3.500v17"/><path d="M14 8.200l1.100 2.300 2.500.3-1.800 1.700.5 2.500-2.300-1.200-2.300 1.200.5-2.500-1.800-1.700 2.500-.3Z" fill="currentColor" stroke="none"/></g></symbol>
<symbol id="u-som" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"><path d="M4 9.500h3.500L12 5.500v13L7.500 14.500H4Z" fill="currentColor"/><path d="M15.500 9q2 3 0 6M18.300 6.500q4 5.500 0 11"/></g></symbol>
<symbol id="u-mudo" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"><path d="M4 9.500h3.500L12 5.500v13L7.500 14.500H4Z" fill="currentColor"/><path d="M16 9.500l5 5M21 9.500l-5 5"/></g></symbol>
<symbol id="u-menu" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></g></symbol>
<symbol id="u-fechar" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.400" stroke-linecap="round"/></symbol>
<symbol id="u-seta" viewBox="0 0 24 24"><path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" stroke-linejoin="round"/></symbol>
<symbol id="u-tela" viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" stroke-linejoin="round"/></symbol>
<symbol id="u-reiniciar" viewBox="0 0 24 24"><path d="M4.500 12a7.500 7.500 0 1 0 2.300-5.400M4 4v4.500h4.500" fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" stroke-linejoin="round"/></symbol>
<symbol id="u-ajuda" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.500"/><path d="M9.500 9.500a2.600 2.600 0 1 1 3.700 2.400c-.8.400-1.200 1-1.200 1.900"/></g><circle cx="12" cy="16.800" r="1.200" fill="currentColor"/></symbol>
<symbol id="u-cadeado" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10.500" width="14" height="10" rx="2.500"/><path d="M8 10.500V8a4 4 0 0 1 8 0v2.500"/></g></symbol>
<symbol id="u-check" viewBox="0 0 24 24"><path d="M4.500 12.500l5 5L19.500 7" fill="none" stroke="currentColor" stroke-width="2.600" stroke-linecap="round" stroke-linejoin="round"/></symbol>

<!-- ============================================================ Enfeites do céu do jogo (tom sobre tom) -->
<symbol id="d-planeta" viewBox="0 0 200 140">
  <g stroke="#3d2787" stroke-width="3" stroke-linejoin="round" stroke-linecap="round">
    <circle cx="100" cy="70" r="46" fill="#26135a"/>
    <path d="M62 45q30 12 78 2M57 78q40 14 88-2" fill="none"/>
    <path d="M58 88C22 96 6 104 8 112c3 11 55 6 100-10s86-40 83-51c-2-7-17-7-40-3" fill="none" stroke-width="5"/>
  </g>
</symbol>
<symbol id="d-lua" viewBox="0 0 120 120">
  <path d="M84 12A52 52 0 1 0 108 84 44 44 0 1 1 84 12Z" fill="#26135a" stroke="#3d2787" stroke-width="3" stroke-linejoin="round"/>
  <circle cx="44" cy="64" r="7" fill="none" stroke="#3d2787" stroke-width="3"/><circle cx="62" cy="92" r="4" fill="none" stroke="#3d2787" stroke-width="3"/>
</symbol>
<symbol id="d-cometa" viewBox="0 0 220 120">
  <g stroke="#3d2787" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 14L150 76M30 50L142 90M70 14L160 66" fill="none"/>
    <circle cx="176" cy="88" r="24" fill="#26135a"/>
  </g>
</symbol>
<symbol id="d-foguete" viewBox="0 0 120 180">
  <g stroke="#3d2787" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" fill="#26135a">
    <path d="M32 112L10 150h28M88 112l22 38H82"/>
    <path d="M60 8C86 34 92 82 82 128H38C28 82 34 34 60 8Z"/>
    <circle cx="60" cy="66" r="13" fill="none"/>
    <path d="M46 128l-4 22M60 128v36M74 128l4 22" fill="none"/>
  </g>
</symbol>
<symbol id="d-satelite" viewBox="0 0 200 120">
  <g stroke="#3d2787" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" fill="#26135a">
    <rect x="8" y="38" width="56" height="44" rx="4"/><rect x="136" y="38" width="56" height="44" rx="4"/>
    <path d="M26 38v44M46 38v44M154 38v44M174 38v44M64 60h16M120 60h16" fill="none"/>
    <rect x="80" y="34" width="40" height="52" rx="8"/>
    <path d="M100 34V16" fill="none"/><circle cx="100" cy="12" r="5"/>
  </g>
</symbol>

</defs>
</svg>
    <?php
}

/** Um símbolo do sprite como <svg><use/></svg>. */
function simbolo(string $id, string $classe = '', string $rotulo = ''): string
{
    $aria = $rotulo === '' ? 'aria-hidden="true"' : 'role="img" aria-label="' . e($rotulo) . '"';
    return '<svg class="' . e($classe) . '" ' . $aria . ' focusable="false"><use href="#' . e($id) . '"/></svg>';
}
