<?php

namespace App\Actions\Item;

use App\Models\Item;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class GenerateItemQrCodeAction
{
    public function handle(Item $item): void
    {
        $url    = url("/api/v1/items/{$item->id}");
        $writer = new PngWriter();
        $result = $writer->write(new QrCode($url, size: 300, margin: 10));

        $path = "qr-codes/{$item->id}.png";
        Storage::disk('public')->put($path, $result->getString());

        $item->updateQuietly(['qr_code' => $path]);
    }
}
