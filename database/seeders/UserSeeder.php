<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Users (skip if admin already exists)
        $adminUsers = [];
        
        if (!User::where('email', 'admin@rachmat.com')->exists()) {
            $adminUsers[] = [
                'name' => 'مدير النظام الرئيسي',
                'email' => 'admin@rachmat.com',
                'phone' => '+213555000001',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'is_verified' => true,
                'email_verified_at' => now(),
            ];
        }
        
        $adminUsers[] = [
            'name' => 'مدير المحتوى',
            'email' => 'content@rachmat.com',
            'phone' => '+213555000002',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'is_verified' => true,
            'email_verified_at' => now(),
        ];

        // Designer Users
        $designerUsers = [
            [
                'name' => 'فاطمة بن علي',
                'email' => 'fatima@designer.com',
                'phone' => '+213661234567',
                'password' => Hash::make('password'),
                'user_type' => 'designer',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'أحمد محمد',
                'email' => 'ahmed@designer.com',
                'phone' => '+213771234567',
                'password' => Hash::make('password'),
                'user_type' => 'designer',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'خديجة رضا',
                'email' => 'khadija@designer.com',
                'phone' => '+213551234567',
                'password' => Hash::make('password'),
                'user_type' => 'designer',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'يوسف العربي',
                'email' => 'youssef@designer.com',
                'phone' => '+213791234567',
                'password' => Hash::make('password'),
                'user_type' => 'designer',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'نور الهدى',
                'email' => 'nour@designer.com',
                'phone' => '+213661234568',
                'password' => Hash::make('password'),
                'user_type' => 'designer',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
        ];

        // Client Users
        $clientUsers = [
            [
                'name' => 'عائشة الجزائرية',
                'email' => 'aicha@client.com',
                'phone' => '+213561234567',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'محمد الدين',
                'email' => 'mohammed@client.com',
                'phone' => '+213671234567',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'سارة بوزيد',
                'email' => 'sara@client.com',
                'phone' => '+213781234567',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'عبد الرحمن كمال',
                'email' => 'abdelrahman@client.com',
                'phone' => '+213691234567',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'مريم زهرة',
                'email' => 'mariam@client.com',
                'phone' => '+213561234568',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'عمر الصادق',
                'email' => 'omar@client.com',
                'phone' => '+213671234568',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'هند الجميلة',
                'email' => 'hind@client.com',
                'phone' => '+213781234568',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'كريم النور',
                'email' => 'karim@client.com',
                'phone' => '+213691234568',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
        ];

        // Create all users (safe from duplicate constraints)
        foreach ($adminUsers as $userData) {
            $email = $userData['email'];
            unset($userData['email']);
            User::firstOrCreate(['email' => $email], $userData);
        }

        foreach ($designerUsers as $userData) {
            $email = $userData['email'];
            unset($userData['email']);
            User::firstOrCreate(['email' => $email], $userData);
        }

        foreach ($clientUsers as $userData) {
            $email = $userData['email'];
            unset($userData['email']);
            User::firstOrCreate(['email' => $email], $userData);
        }

        // Generate more client users for pagination testing
        $this->generateBulkClientUsers(50);

        $this->command->info('Users created successfully!');
        $this->command->info('Total Users: ' . User::count());
        $this->command->info('- Admin: ' . User::where('user_type', 'admin')->count());
        $this->command->info('- Designers: ' . User::where('user_type', 'designer')->count());
        $this->command->info('- Clients: ' . User::where('user_type', 'client')->count());
    }

    private function generateBulkClientUsers(int $count): void
    {
        $firstNames = [
            'أحمد', 'محمد', 'علي', 'حسن', 'يوسف', 'عبد الله', 'عمر', 'خالد', 'سعيد', 'كريم',
            'فاطمة', 'عائشة', 'خديجة', 'زينب', 'مريم', 'سارة', 'هدى', 'أمينة', 'سلمى', 'ياسمين',
            'عبد الرحمن', 'إبراهيم', 'عثمان', 'طارق', 'وليد', 'رشيد', 'نبيل', 'جمال', 'فيصل', 'ماجد',
            'نور', 'رانيا', 'لينا', 'دينا', 'ريم', 'هند', 'سمية', 'كوثر', 'شيماء', 'رقية'
        ];

        $lastNames = [
            'العربي', 'الجزائري', 'بن علي', 'بن محمد', 'القادري', 'الشريف', 'النوري', 'السعدي',
            'الحسني', 'التونسي', 'المغربي', 'المصري', 'الشامي', 'البغدادي', 'الأندلسي', 'الفاسي',
            'بوزيد', 'بن عمر', 'بن يوسف', 'بن سعيد', 'العلوي', 'الهاشمي', 'الطاهري', 'الزهراني'
        ];

        for ($i = 1; $i <= $count; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = "{$firstName} {$lastName}";
            
            $email = "client{$i}@test.com";
            $phone = '+21356' . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);

            User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'is_verified' => rand(0, 1) ? true : false,
                'email_verified_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                'created_at' => now()->subDays(rand(1, 90)),
            ]);
        }
    }
} 