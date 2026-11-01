<?php

declare(strict_types=1);

namespace App\Identity;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

final class ProvisioningQr
{
    public function dataUri(#[\SensitiveParameter] string $payload): string
    {
        $image = (new QRCode(new QROptions([
            'outputBase64' => true,
            'eccLevel' => 'M',
            'addQuietzone' => true,
            'quietzoneSize' => 4,
            'svgAddXmlHeader' => false,
        ])))->render($payload);

        if (!is_string($image) || !str_starts_with($image, 'data:image/svg+xml;base64,')) {
            throw new \RuntimeException('No se pudo generar el código QR.');
        }

        return $image;
    }
}
