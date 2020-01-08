<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new App\Model\User;
        $admin->user_id = 'domesin';
        $admin->name = '도매신 관리자';
        $admin->email = 'domesin';
        $admin->password = bcrypt('123456');
        $admin->is_admin = true;
        $admin->shop_type_id = 1;
        $admin->save();

        // $user1 = new App\Model\User;
        // $user1->user_id = 'user_test_1';
        // $user1->name = '테스트1';
        // $user1->email = 'test';
        // $user1->password = bcrypt('123456');
        // $user1->shop_type_id = 2;
        // $user1->save();

        // $user2 = new App\Model\User;
        // $user2->user_id = 'user_test_2';
        // $user2->name = '테스트2';
        // $user2->email = 'test2';
        // $user2->password = bcrypt('123456');
        // $user2->shop_type_id = 3;
        // $user2->save();

        // $user3 = new App\Model\User;
        // $user3->user_id = 'user_test_3';
        // $user3->name = '테스트3';
        // $user3->email = 'test3';
        // $user3->password = bcrypt('123456');
        // $user3->shop_type_id = 4;
        // $user3->save();
    }
}
