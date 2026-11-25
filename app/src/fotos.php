<?php
declare(strict_types=1);

/**
 * Fotos das cientistas. Toda imagem enviada é decodificada e regravada pelo GD:
 * sai sempre um JPEG quadrado de 640 px, sem metadados (EXIF/GPS) e sem nada além dos pixels.
 * O filtro de cor de cada área é aplicado na exibição (CSS), então a foto guardada fica neutra.
 */

const FOTOS_DIR = '/var/www/dados/fotos';
const FOTO_LADO = 640;
const FOTO_MAX_BYTES = 5 * 1024 * 1024;
const FOTO_MAX_PIXELS = 40_000_000;

function url_foto(?string $arquivo): ?string
{
    return $arquivo && preg_match('/^[a-z0-9-]+\.jpg$/', $arquivo) ? '/fotos/' . $arquivo : null;
}

/** Foto em duotom (cor da área) ou, sem foto, a personagem ilustrada. */
function retrato(array $c, string $classe = ''): string
{
    $url = url_foto($c['foto'] ?? null);
    if ($url) {
        return '<span class="retrato ' . e($classe) . '"><img src="' . e($url) . '" alt="Foto de ' . e($c['nome']) . '" loading="lazy" decoding="async"></span>';
    }
    return '<span class="retrato retrato--ilustrado ' . e($classe) . '">' . simbolo('s-ep-' . variante_personagem((int) $c['id']), 'retrato-personagem') . '</span>';
}

/**
 * Valida e processa a foto de $_FILES[$campo]. Devolve o nome do arquivo gravado,
 * null se nenhum arquivo foi enviado, ou lança InvalidArgumentException com mensagem para o usuário.
 */
function receber_foto(string $campo): ?string
{
    $f = $_FILES[$campo] ?? null;
    if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (!function_exists('imagecreatetruecolor')) {
        error_log('[constelar] Extensão GD ausente: reconstrua a imagem (docker compose up -d --build).');
        throw new InvalidArgumentException('O envio de fotos está indisponível neste servidor. Envie a indicação sem foto.');
    }
    if (is_array($f['error']) || $f['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException(in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'A foto é grande demais (máximo 5 MB).' : 'Não foi possível receber a foto. Tente de novo.');
    }
    if (!is_uploaded_file($f['tmp_name']) || filesize($f['tmp_name']) > FOTO_MAX_BYTES) {
        throw new InvalidArgumentException('A foto é grande demais (máximo 5 MB).');
    }

    $info = @getimagesize($f['tmp_name']);
    $tipos = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
    if (!$info || !isset($tipos[$info[2]])) {
        throw new InvalidArgumentException('Envie a foto em JPG, PNG ou WEBP.');
    }
    [$w, $h] = $info;
    if ($w < 200 || $h < 200) {
        throw new InvalidArgumentException('A foto é pequena demais (mínimo 200 × 200 pixels).');
    }
    if ($w * $h > FOTO_MAX_PIXELS) {
        throw new InvalidArgumentException('A foto tem resolução alta demais.');
    }

    $img = @$tipos[$info[2]]($f['tmp_name']);
    if (!$img) {
        throw new InvalidArgumentException('O arquivo enviado não é uma imagem válida.');
    }
    $img = corrigir_rotacao($img, $f['tmp_name'], $info[2]);
    return gravar_foto($img);
}

/** Fotos de celular guardam a rotação no EXIF; aplica-a nos pixels. */
function corrigir_rotacao(GdImage $img, string $caminho, int $tipo): GdImage
{
    if ($tipo !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
        return $img;
    }
    $exif = @exif_read_data($caminho);
    $graus = [3 => 180, 6 => -90, 8 => 90][(int) ($exif['Orientation'] ?? 1)] ?? 0;
    return $graus ? (imagerotate($img, $graus, 0) ?: $img) : $img;
}

/**
 * Recorte quadrado. Sem $foco: centralizado na horizontal e puxado para cima (onde costuma estar o rosto).
 * Com $foco = [x, y, lado]: centro do rosto (frações da largura/altura) e lado (fração do menor lado).
 */
function gravar_foto(GdImage $img, ?array $foco = null): string
{
    $w = imagesx($img);
    $h = imagesy($img);
    if ($foco) {
        [$fx, $fy, $z] = array_map('floatval', $foco);
        $lado = max(1, (int) (min($w, $h) * min(1, max(.2, $z))));
        $x = (int) min(max($fx * $w - $lado / 2, 0), $w - $lado);
        $y = (int) min(max($fy * $h - $lado / 2, 0), $h - $lado);
    } else {
        $lado = min($w, $h);
        $x = intdiv($w - $lado, 2);
        $y = $h > $w ? (int) round(($h - $lado) * 0.18) : 0;
    }

    $saida = imagecreatetruecolor(FOTO_LADO, FOTO_LADO);
    imagefill($saida, 0, 0, imagecolorallocate($saida, 255, 255, 255)); // fundo branco para PNG transparente
    imagecopyresampled($saida, $img, 0, 0, $x, $y, FOTO_LADO, FOTO_LADO, $lado, $lado);

    if (!is_dir(FOTOS_DIR)) {
        mkdir(FOTOS_DIR, 0755, true);
    }
    $nome = bin2hex(random_bytes(16)) . '.jpg';
    if (!imagejpeg($saida, FOTOS_DIR . '/' . $nome, 86)) {
        throw new RuntimeException('Falha ao gravar a foto.');
    }
    return $nome;
}

function apagar_foto(?string $arquivo): void
{
    if ($arquivo && preg_match('/^[a-z0-9-]+\.jpg$/', $arquivo) && is_file(FOTOS_DIR . '/' . $arquivo)) {
        @unlink(FOTOS_DIR . '/' . $arquivo);
    }
}
