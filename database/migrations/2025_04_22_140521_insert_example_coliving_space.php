<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $idExists = DB::table('spaces')->where('id', 1)->exists();
        $uidExists = DB::table('spaces')->where('uid', '1kxb2jcn0qf3')->exists();

        if (! $idExists && ! $uidExists) {
            DB::table('spaces')->insert([
                'id' => 1,
                'user_id' => 1,
                'uid' => '1kxb2jcn0qf3',
                'place_id' => '',
                'private' => 0,
                'claimed' => 1,
                'status' => 'approved',
                'name' => 'Coliving App - Example Coliving Space',
                'username' => 'colivingapp',
                'name_slug' => '',
                'website' => 'https://coliving.app',
                'email' => 'info@coliving.app',
                'phone' => '',
                'whatsapp' => '',
                'country' => 'Spain',
                'country_slug' => 'spain',
                'country_code' => 'ES',
                'city' => '',
                'city_slug' => 'n-a',
                'latitude' => '42.4274333',
                'longitude' => '3.1588785',
                'amenities' => '',
                'photo' => 1,
                'created_at' => '2023-12-09 08:51:01',
                'updated_at' => '2025-04-21 06:15:45',
                'address' => 'Portbou, Upper Empordà, Cherona, Catalonia, Spain',
                'formatted_address' => '',
                'google_url' => '',
                'instagram' => 'https://www.instagram.com/colivingapphq/',
                'twitter' => 'https://twitter.com/colivingapp',
                'github' => 'https://github.com/colivingapp',
                'telegram' => 'https://t.me/colivingapp',
                'facebook' => '',
                'youtube' => 'https://youtube.com/@ColivingApp',
                'tiktok' => '',
                'pinterest' => 'https://www.pinterest.com/ColivingApp',
                'linkedin' => 'https://www.linkedin.com/company/colivingapp',
                'linktree' => '',
                'substack' => '',
                'medium' => '',
                'booking_com' => '',
                'airbnb_com' => '',
                'coliving_com' => '',
                'orgSpont' => 5,
                'quietLoud' => 3,
                'earlyNight' => 4,
                'workFun' => 3,
                'expTrad' => 5,
                'multiLocal' => 4,
                'cf_07' => 5,
                'cf_08' => 5,
                'cf_09' => 5,
                'cf_10' => 5,
                'description' => <<<EOD
"Example Coliving Space" is a demo space created to show you how everything works on the Coliving App. You automatically join this space when you sign up.

When you join the space, you'll see all other space mates in the hub, find additional information, and be able to use the chat feature. This demo space helps you understand how to interact within the app and make the most of your coliving experience. Enjoy exploring the features and discovering how easy and exciting coliving can be with our app!
EOD,
                'info' => <<<EOD
Welcome to the Example Coliving Space!

🌟 About This Space
You are currently in an example space designed to show you how our Coliving App works. As a member of this space, you'll see features in action and get a feel for how you can interact within your own real coliving environment in the future.

🛠 Using the Info Section
The Info section is here to help you access important information shared by the host, such as house rules, Wi-Fi passwords, and local amenities. It’s a convenient place for quickly finding key details about your living environment.

🔗 Your Automatic Joining
You were automatically added to this Example Coliving Space when you signed up for the app. This space is designed to be a temporary tutorial to help you understand how to navigate and use the app effectively. Feel free to explore the features and settings!

Thank you for joining us, and we hope you enjoy discovering how easy and exciting coliving can be with our app!
EOD,
            ]);

            DB::table('photos')->insert([
                'id' => 1,
                'space_uid' => '1kxb2jcn0qf3',
                'pos' => 0,
                'filename' => 'example-coliving-space.jpg',
                'space_id' => 1,
                'created_at' => '2023-12-09 08:51:01',
                'updated_at' => '2025-04-04 10:05:07',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('spaces')->where('uid', '1kxb2jcn0qf3')->orWhere('id', 1)->delete();
        DB::table('photos')->where('id', 1)->orWhere('space_id', 1)->delete();
    }
};
