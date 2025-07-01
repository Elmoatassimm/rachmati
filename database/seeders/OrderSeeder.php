<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Rachma;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('user_type', 'client')->get();
        $rachmat = Rachma::all();

        $statuses = ['pending', 'completed', 'rejected'];
        $weights = [30, 60, 10]; // Probability distribution for statuses (simplified system)
        $paymentMethods = ['ccp', 'baridi_mob', 'dahabiya'];

        // Create 200 orders with varying dates instead of 30
        for ($i = 0; $i < 200; $i++) {
            $client = $clients->random();
            $rachma = $rachmat->random();
            
            // Random status based on weights
            $status = $this->getWeightedRandomStatus($statuses, $weights);
            
            // Generate dates based on status (simplified system)
            $createdAt = $this->getRandomCreatedDate();
            $confirmedAt = null;
            $fileSentAt = null;
            $completedAt = null;
            $rejectedAt = null;

            if ($status === 'completed') {
                $confirmedAt = $createdAt->copy()->addHours(rand(1, 48));
                $fileSentAt = $confirmedAt->copy()->addDays(rand(1, 7));
                $completedAt = $fileSentAt->copy();
            }

            if ($status === 'rejected') {
                $rejectedAt = $createdAt->copy()->addHours(rand(1, 24));
            }

            // Select random payment proof image (using the created payment proofs)
            $paymentProofs = [
                'payment_proofs/ccp_receipt_1.jpg',
                'payment_proofs/ccp_receipt_2.jpg',
                'payment_proofs/baridi_mob_1.jpg',
                'payment_proofs/baridi_mob_2.jpg',
                'payment_proofs/dahabiya_receipt_1.jpg',
                'payment_proofs/dahabiya_receipt_2.jpg',
                'payment_proofs/bank_transfer_1.jpg',
                'payment_proofs/bank_transfer_2.jpg',
            ];

            $orderData = [
                'client_id' => $client->id,
                'rachma_id' => $rachma->id,
                'status' => $status,
                'amount' => $rachma->price,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'payment_proof_path' => $paymentProofs[array_rand($paymentProofs)],
                'admin_notes' => $this->getRandomAdminNotes($status),
                'created_at' => $createdAt,
                'updated_at' => $completedAt ?? $rejectedAt ?? $createdAt,
                'confirmed_at' => $confirmedAt,
                'file_sent_at' => $fileSentAt,
                'completed_at' => $completedAt,
                'rejected_at' => $rejectedAt,
            ];

            // Add rejection reason for rejected orders
            if ($status === 'rejected') {
                $orderData['rejection_reason'] = $this->getRandomRejectionReason();
            }

            Order::create($orderData);
        }

        $this->command->info('200 orders created successfully with realistic data!');
    }

    private function getWeightedRandomStatus(array $statuses, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0];
    }

    private function getRandomCreatedDate(): Carbon
    {
        // Orders from last 3 months
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
        return Carbon::createFromTimestamp($randomTimestamp);
    }

    private function getRandomAdminNotes(?string $status): ?string
    {
        $notes = [
            'pending' => [
                'في انتظار المراجعة والمعالجة',
                'طلب جديد يحتاج مراجعة',
                null, null, // 50% chance of no notes
            ],
            'completed' => [
                'طلب مكتمل بنجاح وتم تسليم الملف',
                'العميل راضٍ عن الخدمة',
                'تم إكمال الطلب في الوقت المحدد',
                null,
            ],
            'rejected' => [
                'تم رفض الطلب لأسباب فنية',
                'طلب غير مكتمل',
            ],
        ];

        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }

    private function getRandomRejectionReason(): string
    {
        $reasons = [
            'صورة إثبات الدفع غير واضحة',
            'مبلغ الدفع غير صحيح',
            'طريقة الدفع غير مدعومة',
            'معلومات الطلب ناقصة',
            'الرشمة المطلوبة غير متوفرة حالياً',
            'مشكلة في التحقق من الدفع',
        ];

        return $reasons[array_rand($reasons)];
    }
} 