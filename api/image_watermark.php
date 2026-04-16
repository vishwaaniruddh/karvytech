<?php

/**
 * Text Watermark Function (Multi-Line Support)
 *
 * Ye function image me multi-line watermark add karne ke liye banaya gaya hai.
 * Text lines ko \n se separate karo — har line alag dikhai degi.
 * Background strip automatically sabhi lines ko cover karegi.
 * Watermark hamesha bottom-right mein aayega.
 *
 * @param string $sourceImage     Source image ka path
 * @param string $watermarkText   Watermark text (\n se multiple lines)
 * @param string $outputImage     Output path (null = browser mein print)
 * @param int    $fontSize        Font size (autoScale=true hone par override hoga)
 * @param string $fontPath        TTF font file ka path
 * @param array  $textColor       Text color [R, G, B] (default: White)
 * @param int    $marginRight     Right margin
 * @param int    $marginBottom    Bottom margin
 * @param bool   $autoScale       true = font size image ke hisab se auto set hoga
 * @return bool
 */
function addTextWatermark($sourceImage, $watermarkText, $outputImage = null, $fontSize = 30, $fontPath = 'arial.ttf', $textColor = [255, 255, 255], $marginRight = 20, $marginBottom = 40, $autoScale = true) {

    if (!file_exists($sourceImage)) return false;

    $imageInfo = getimagesize($sourceImage);
    if (!$imageInfo) return false;

    $width  = $imageInfo[0];
    $height = $imageInfo[1];
    $mime   = $imageInfo['mime'];

    // Auto-scale: font size = 2.5% of image width (min 22px) — readable without zoom
    if ($autoScale) {
        $fontSize     = max(22, (int)($width * 0.025));
        $marginRight  = max(15, (int)($width * 0.012));
        $marginBottom = max(15, (int)($height * 0.02));
    }

    // Image resource banao
    switch ($mime) {
        case 'image/jpeg': $image = imagecreatefromjpeg($sourceImage); break;
        case 'image/png':  $image = imagecreatefrompng($sourceImage);  break;
        case 'image/gif':  $image = imagecreatefromgif($sourceImage);  break;
        default: return false;
    }

    $white = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

    if (!file_exists($fontPath)) {
        imagedestroy($image);
        return false;
    }

    // -------------------------------------------------------
    // MULTI-LINE SUPPORT
    // \n se text lines alag karo
    // -------------------------------------------------------
    $lines      = explode("\n", str_replace(["\r\n", "\r"], "\n", $watermarkText));
    $lineHeight = (int)($fontSize * 1.45); // line ke beech gap
    $padX       = (int)($fontSize * 0.6);
    $padY       = (int)($fontSize * 0.45);

    // Har line ki width nikalo — sabse wide line background strip determine karegi
    $maxTextWidth    = 0;
    $lineBboxes      = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue; // empty lines skip
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
        $lw   = abs($bbox[2] - $bbox[0]);
        $lineBboxes[] = ['w' => $lw, 'text' => $line];
        if ($lw > $maxTextWidth) $maxTextWidth = $lw;
    }

    $lineCount       = count($lineBboxes);
    $totalTextHeight = $lineCount * $lineHeight;

    // Background strip dimensions
    $stripW = $maxTextWidth + $padX * 2;
    $stripH = $totalTextHeight + $padY * 2;

    // Position: bottom-right
    $bgX1 = max(0, $width  - $stripW - $marginRight);
    $bgY1 = max(0, $height - $stripH - $marginBottom);

    // ---- Semi-transparent dark strip ONLY behind text ----
    $strip = imagecreatetruecolor($stripW, $stripH);
    $black = imagecolorallocate($strip, 0, 0, 0);
    imagefilledrectangle($strip, 0, 0, $stripW, $stripH, $black);
    imagecopymerge($image, $strip, $bgX1, $bgY1, 0, 0, $stripW, $stripH, 60);
    imagedestroy($strip);
    // ------------------------------------------------------

    // Har line ko strip ke andar likho (right-aligned)
    $currentY = $bgY1 + $padY + $fontSize;

    foreach ($lineBboxes as $lb) {
        $lineX = $bgX1 + $padX + ($maxTextWidth - $lb['w']); // right-align within strip
        imagettftext($image, $fontSize, 0, $lineX, $currentY, $white, $fontPath, $lb['text']);
        $currentY += $lineHeight;
    }

    // Save or output
    $success = false;
    if ($outputImage) {
        switch ($mime) {
            case 'image/jpeg': $success = imagejpeg($image, $outputImage, 92); break;
            case 'image/png':  $success = imagepng($image, $outputImage);      break;
            case 'image/gif':  $success = imagegif($image, $outputImage);      break;
        }
    } else {
        header("Content-Type: $mime");
        switch ($mime) {
            case 'image/jpeg': $success = imagejpeg($image); break;
            case 'image/png':  $success = imagepng($image);  break;
            case 'image/gif':  $success = imagegif($image);  break;
        }
    }

    imagedestroy($image);
    return $success;
}

/* =================================================================================
// Example Usage — Multi-Line Watermark
// =================================================================================

$sourceFile = '../uploads/sample_image.jpg';

// \n se alag karo — har line alag row mein aayegi
$watermark = "Tushar Mahobia\n28.6139 N, 77.2090 E\nConnaught Place, New Delhi\n15-04-2026 10:30 AM";

$outputFile = '../uploads/watermarked_image.jpg';

$isSuccess = addTextWatermark($sourceFile, $watermark, $outputFile);

if($isSuccess) {
    echo "Watermark successfully applied!";
} else {
    echo "Something went wrong!";
}

*/
?>
