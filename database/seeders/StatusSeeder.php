<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code'          => 'ORDERED',
                'label'         => 'Ordered',
                'description'   => 'Barang sudah dipesan, menunggu pengiriman masuk.',
            ],
            [
                'code'          => 'RECEIVED',
                'label'         => 'Received',
                'description'   => 'Barang sudah diterima, menunggu pendataan dan inspeksi internal.',
            ],
            [
                'code'          => 'VERIFIED',
                'label'         => 'In Warehouse',
                'description'   => 'Barang berada di gudang, telah melalui pendataan dan inspeksi internal.',
            ],
            [
                'code'          => 'MOVING',
                'label'         => 'Moving',
                'description'   => 'Barang bergerak, periksa detail riwayat status.',
            ],
            [
                'code'          => 'READY_FOR_DELIVERY',
                'label'         => 'Ready for delivery',
                'description'   => 'Barang berada di gudang, tidak akan keluar lagi hingga pengiriman ke klien.',
            ],
            [
                'code'          => 'DELIVERED',
                'label'         => 'Delivered',
                'description'   => 'Barang telah dikirimkan ke klien.',
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
