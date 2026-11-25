<?php
declare(strict_types=1);

require __DIR__ . '/sprites.php';

/** Figuras das constelações (mesmo arquivo que o jogo lê). */
function figuras(): array
{
    static $f = null;
    if ($f === null) {
        $f = json_decode((string) file_get_contents(__DIR__ . '/../public/assets/dados/figuras.json'), true) ?: [];
    }
    return $f;
}

/**
 * Pontos e arestas de uma figura para N cientistas.
 * Se houver mais cientistas do que pontos, as extras formam um anel externo.
 * (A mesma regra existe em assets/js/jogo/dados.js.)
 */
function geometria_figura(string $icone, int $n): array
{
    $figs = figuras();
    $fig = $figs[$icone] ?? $figs['estrela'];
    $pontos = $fig['pontos'];
    $arestas = $fig['arestas'];
    $base = count($pontos);

    $extras = max(0, $n - $base);
    for ($j = 0; $j < $extras; $j++) {
        $ang = deg2rad(-54 + $j * 360 / $extras);
        $p = [1.28 * cos($ang), 1.28 * sin($ang)];
        $perto = 0;
        $menor = INF;
        for ($k = 0; $k < $base; $k++) {
            $d = hypot($pontos[$k][0] - $p[0], $pontos[$k][1] - $p[1]);
            if ($d < $menor) {
                $menor = $d;
                $perto = $k;
            }
        }
        $pontos[] = $p;
        $arestas[] = [$base + $j, $perto];
    }
    return ['pontos' => $pontos, 'arestas' => $arestas];
}

/**
 * Desenho de uma constelação: linhas, estrelas das cientistas e estrelas decorativas.
 * $destaque: índice (0…) da estrela a realçar, ou null.
 */
function figura_svg(string $icone, int $estrelas, string $classe = '', ?int $destaque = null, string $rotulo = ''): string
{
    $g = geometria_figura($icone, $estrelas);
    $k = 100;
    $aria = $rotulo === '' ? 'aria-hidden="true"' : 'role="img" aria-label="' . e($rotulo) . '"';
    $o = '<svg class="figura ' . e($classe) . '" viewBox="-140 -140 280 280" ' . $aria . ' focusable="false">';
    foreach ($g['arestas'] as [$a, $b]) {
        $o .= sprintf(
            '<line class="fg-linha" x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f"/>',
            $g['pontos'][$a][0] * $k, $g['pontos'][$a][1] * $k, $g['pontos'][$b][0] * $k, $g['pontos'][$b][1] * $k
        );
    }
    foreach ($g['pontos'] as $i => [$x, $y]) {
        if ($i < $estrelas) {
            $t = $i === $destaque ? 46 : 28;
            if ($i === $destaque) {
                $o .= sprintf('<circle class="fg-anel" cx="%.1f" cy="%.1f" r="30"/>', $x * $k, $y * $k);
            }
            $o .= sprintf(
                '<use class="fg-estrela%s" href="#u-estrela" x="%.1f" y="%.1f" width="%d" height="%d"/>',
                $i === $destaque ? ' fg-estrela--destaque' : '', $x * $k - $t / 2, $y * $k - $t / 2, $t, $t
            );
        } else {
            $o .= sprintf('<circle class="fg-ponto" cx="%.1f" cy="%.1f" r="4"/>', $x * $k, $y * $k);
        }
    }
    return $o . '</svg>';
}

/** Figurinha do álbum: cartão de uma cientista com a personagem na cor da constelação. */
function figurinha(array $c, string $classeExtra = ''): string
{
    $o = '<a class="figurinha area-' . (int) $c['area_id'] . ' ' . e($classeExtra) . '" href="/cientista.php?id=' . (int) $c['id'] . '">';
    $o .= '<span class="fig-num" aria-hidden="true">' . sprintf('%02d', (int) $c['numero']) . '</span>';
    if (!empty($c['destaque'])) {
        $o .= '<span class="fig-selo">Homenageada</span>';
    }
    $o .= empty($c['foto'])
        ? '<span class="fig-arte">' . simbolo('u-estrela', 'fig-brilho') . simbolo('s-ep-' . (int) $c['variante'], 'fig-personagem') . '</span>'
        : '<span class="fig-arte fig-arte--foto">' . retrato($c, 'fig-foto') . '</span>';
    $o .= '<span class="fig-texto"><strong class="fig-nome">' . e($c['nome']) . '</strong>';
    $o .= '<span class="fig-area">' . e($c['area']) . '</span></span>';
    return $o . '</a>';
}
