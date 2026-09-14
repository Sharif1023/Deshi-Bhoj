<?php
use App\Core\DB;
use App\Services\Content;
if (DB::one('SELECT setting_key FROM settings LIMIT 1')) return;
DB::transaction(function () {
    $source = json_decode(file_get_contents(ROOT.'/database/source-content.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach ($source['settings'] + ['analytics.views'=>0,'contact.phone'=>'+8801700000000','contact.email'=>'hello@example.com','contact.address'=>'আপনার রেস্টুরেন্টের ঠিকানা দিন, বাংলাদেশ','contact.hours'=>'প্রতিদিন দুপুর ১২টা – রাত ১০টা','ordering.delivery_fee'=>6000,'ordering.minimum'=>30000,'ordering.delivery_enabled'=>true,'reservation.max_guests'=>12] as $key=>$value) Content::save($key,$value);
    $categories=[];
    foreach($source['seedCategories'] as $c) $categories[$c['slug']]=DB::insert('categories',['name'=>$c['name'],'slug'=>$c['slug'],'description'=>$c['description'],'image'=>$c['image'],'sort_order'=>$c['sortOrder']]);
    foreach($source['seedProducts'] as $p) DB::insert('products',['category_id'=>$categories[$p['category']],'name'=>$p['name'],'slug'=>$p['slug'],'description'=>$p['description'],'price'=>$p['price'],'images'=>json_encode($p['images']),'allergens'=>$p['allergens'],'dietary'=>'','ingredients'=>implode(', ',$p['ingredients']),'preparation_minutes'=>$p['preparationMinutes'],'available'=>1,'featured'=>(int)$p['featured'],'badge'=>$p['badge'],'sort_order'=>$p['sortOrder']]);
    foreach(['চেনা দেশি পদ'=>'কাচ্চি, কালা ভুনা ও ইলিশের আয়োজন।','সহজ অর্ডার'=>'খাবার বেছে cart-এ যোগ করুন, তারপর checkout।','আপনার সুবিধামতো'=>'ডেলিভারি অথবা রেস্টুরেন্ট থেকে পিকআপ।'] as $title=>$description) DB::insert('features',compact('title','description'));
    DB::audit('Bangladesh demo menu installed. Review sample prices and contact details before launch.');
});
