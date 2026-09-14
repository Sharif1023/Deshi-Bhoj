<?php
use App\Core\DB;
use App\Services\Content;
if (DB::one("SELECT setting_key FROM settings WHERE setting_key='system.bangladesh'")) return;
$source=json_decode(file_get_contents(ROOT.'/database/source-content.json'),true,512,JSON_THROW_ON_ERROR);
// Keep a content-only snapshot before this one-time localization. Customer/order data is untouched.
$snapshot=[];
foreach(['settings','products','categories','features','gallery','faqs'] as $table) $snapshot[$table]=DB::all('SELECT * FROM '.$table);
$backup=ROOT.'/storage/backups';
if(!is_dir($backup)&&!mkdir($backup,0775,true)&&!is_dir($backup))throw new RuntimeException('Cannot create content backup directory.');
if(file_put_contents($backup.'/before-bangladesh-'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json',json_encode($snapshot,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))===false)throw new RuntimeException('Cannot back up content; migration stopped.');
DB::transaction(function()use($source){
 foreach($source['settings'] as $key=>$value)Content::save($key,$value);
 $categories=[];
 foreach($source['seedCategories'] as $c){$row=DB::one('SELECT id FROM categories WHERE slug=?',[$c['slug']]);$categories[$c['slug']]=$row?$row['id']:DB::insert('categories',['name'=>$c['name'],'slug'=>$c['slug'],'description'=>$c['description'],'image'=>$c['image'],'sort_order'=>$c['sortOrder']]);}
 $legacy=json_decode(file_get_contents(ROOT.'/database/legacy-demo-slugs.json'),true,512,JSON_THROW_ON_ERROR);
 foreach($source['seedProducts'] as $i=>$p){
  if(DB::one('SELECT id FROM products WHERE slug=?',[$p['slug']]))continue;
  $row=DB::one('SELECT id FROM products WHERE slug=?',[$legacy[$i]??'']);
  $values=['category_id'=>$categories[$p['category']],'name'=>$p['name'],'slug'=>$p['slug'],'description'=>$p['description'],'price'=>$p['price'],'images'=>json_encode($p['images']),'allergens'=>$p['allergens'],'dietary'=>'','ingredients'=>implode(', ',$p['ingredients']),'preparation_minutes'=>$p['preparationMinutes'],'available'=>1,'featured'=>(int)$p['featured'],'badge'=>$p['badge'],'sort_order'=>$p['sortOrder']];
  if($row){DB::run('UPDATE products SET '.implode(',',array_map(fn($k)=>$k.'=?',array_keys($values))).' WHERE id=?',[...array_values($values),$row['id']]);}else DB::insert('products',$values);
 }
 // Remove only empty legacy demo categories; custom categories remain intact.
 foreach(['sushi','rolls','sashimi','specials','drinks'] as $slug)DB::run('DELETE FROM categories WHERE slug=? AND NOT EXISTS (SELECT 1 FROM products WHERE products.category_id=categories.id)',[$slug]);
 foreach(['Fresh, never compromised.','Crafted by master hands.','From our kitchen to you.'] as $title)DB::run('DELETE FROM features WHERE title=?',[$title]);
 foreach(['চেনা দেশি পদ'=>'কাচ্চি, কালা ভুনা ও ইলিশের আয়োজন।','সহজ অর্ডার'=>'খাবার বেছে cart-এ যোগ করুন, তারপর checkout।','আপনার সুবিধামতো'=>'ডেলিভারি অথবা রেস্টুরেন্ট থেকে পিকআপ।'] as $title=>$description)if(!DB::one('SELECT id FROM features WHERE title=?',[$title]))DB::insert('features',compact('title','description'));
 foreach(['hero-sushi.webp','salmon-nigiri.webp','restaurant-interior.webp','food.svg'] as $file)DB::run('DELETE FROM gallery WHERE image=?',['/assets/'.$file]);
 foreach(['kacchi'=>'কাচ্চি বিরিয়ানি','kala-bhuna'=>'কালা ভুনা','ilish'=>'সর্ষে ইলিশ'] as $file=>$title){$url='/assets/'.$file.'.png';$info=getimagesize(ROOT.'/public'.$url);if(!DB::one('SELECT id FROM media WHERE url=?',[$url]))DB::insert('media',['url'=>$url,'original_name'=>$title.' (AI demo)','alt'=>$title,'width'=>$info[0],'height'=>$info[1],'created_at'=>date('Y-m-d H:i:s')]);if(!DB::one('SELECT id FROM gallery WHERE image=?',[$url]))DB::insert('gallery',['title'=>$title,'image'=>$url]);}
 $faq=['Can I order for pickup?'=>['পিকআপ অর্ডার করা যাবে?','হ্যাঁ। Checkout-এ পিকআপ নির্বাচন করুন এবং অর্ডার নম্বর রাখুন। সংগ্রহের সময় আমাদের দল নিশ্চিত করবে।'],'How do reservations work?'=>['টেবিল বুকিং কীভাবে করব?','যোগাযোগ পাতায় তারিখ, সময় ও অতিথির সংখ্যা দিয়ে অনুরোধ পাঠান। আমাদের নিশ্চিতকরণের পর বুকিং চূড়ান্ত হবে।'],'Can you accommodate food allergies?'=>['অ্যালার্জি থাকলে কী করব?','অর্ডারের আগে যোগাযোগ করুন। রান্নাঘরে সাধারণ অ্যালার্জেন ব্যবহৃত হয়; অ্যালার্জেনমুক্ত খাবারের নিশ্চয়তা দেওয়া হয় না।'],'How can I pay?'=>['কীভাবে পেমেন্ট করব?','নগদে পেমেন্ট করা যাবে। অনলাইন পেমেন্ট চালু থাকলে payment partner-এর মাধ্যমে bKash/Nagad পাওয়া যাবে।'],'Can I change my order?'=>['অর্ডার পরিবর্তন করা যাবে?','অর্ডার নম্বরসহ দ্রুত যোগাযোগ করুন। রান্না শুরু হয়েছে কি না তার ওপর পরিবর্তনের সুযোগ নির্ভর করে।']];
 foreach($faq as $old=>[$title,$answer])DB::run('UPDATE faqs SET title=?,answer=? WHERE title=?',[$title,$answer,$old]);
 Content::save('system.bangladesh',true);
 DB::audit('Bangladesh content update applied; orders, accounts, uploaded media and custom products preserved.');
});
