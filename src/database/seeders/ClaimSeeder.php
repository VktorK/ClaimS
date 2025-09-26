<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем пользователя VKRT
        $user = User::where('email', 'gdfg@fgdg.ru')->first();
        
        if (!$user) {
            $this->command->info('Пользователь VKRT не найден. Пропускаем создание претензий.');
            return;
        }

        // Получаем товары пользователя
        $products = Product::where('user_id', $user->id)->get();
        
        if ($products->isEmpty()) {
            $this->command->info('Товары не найдены. Пропускаем создание претензий.');
            return;
        }

        $claims = [
            [
                'title' => 'Брак в товаре',
                'description' => 'Обнаружен производственный брак в товаре',
                'status' => 'pending',
                'type' => 'defect',
                'claimed_amount' => 15000.00,
                'claim_date' => now()->subDays(5),
            ],
            [
                'title' => 'Гарантийный случай',
                'description' => 'Товар вышел из строя в гарантийный период',
                'status' => 'in_progress',
                'type' => 'warranty',
                'claimed_amount' => 25000.00,
                'claim_date' => now()->subDays(10),
            ],
            [
                'title' => 'Возврат товара',
                'description' => 'Товар не соответствует описанию',
                'status' => 'resolved',
                'type' => 'return',
                'claimed_amount' => 12000.00,
                'claim_date' => now()->subDays(15),
                'resolution_date' => now()->subDays(2),
                'resolution_notes' => 'Претензия удовлетворена, товар возвращен',
            ],
            [
                'title' => 'Жалоба на качество',
                'description' => 'Качество товара не соответствует заявленному',
                'status' => 'rejected',
                'type' => 'complaint',
                'claimed_amount' => 8000.00,
                'claim_date' => now()->subDays(20),
                'resolution_date' => now()->subDays(5),
                'resolution_notes' => 'Претензия отклонена, товар соответствует стандартам',
            ],
        ];

        foreach ($products->take(3) as $index => $product) {
            if (isset($claims[$index])) {
                Claim::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    ...$claims[$index]
                ]);
            }
        }

        $this->command->info('Создано ' . min(3, $products->count()) . ' претензий для товаров пользователя VKRT.');
    }
}
