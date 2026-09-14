<?php
// Run with ALLOW_QA_WRITES=true against a disposable MySQL test server.
require_once dirname(__DIR__).'/app/Core/bootstrap.php';
use App\Core\{DB,ValidationException};
use App\Services\{Translations,Locale,Content,OrderService};
$isolated = null;
if (!defined('QA_V3_ADAPTER')) {
 require __DIR__.'/database.php'; $isolated=qaDatabase('create');putenv('DB_DATABASE='.$isolated);
 require ROOT.'/database/migrate.php';require ROOT.'/database/seed.php';require ROOT.'/database/upgrade-v3.php';
}
ob_start();$checks=0;
function v3check(bool $ok,string $label):void {global $checks;if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;echo "PASS: $label\n";}
try {
 $p=DB::one('SELECT * FROM products ORDER BY id LIMIT 1');$id=(int)$p['id'];
 $fields=Translations::fields('products');$_POST=['translations'=>['en'=>[],'bn'=>[]]];
 foreach($fields as $field=>$type){$_POST['translations']['en'][$field]='English '.$field;$_POST['translations']['bn'][$field]='বাংলা '.$field;}
 $_POST['translations']['en']['name']='<span class="ql-color-black">Custom biryani</span><script>bad()</script>';
 $submitted=Translations::submitted('products',(require ROOT.'/config/entities.php')['products']['fields']);
 v3check(!str_contains($submitted['en']['name'],'script')&&!str_contains($submitted['en']['name'],'bad()'),'Translated rich content sanitized');
 DB::transaction(fn()=>Translations::save('products',$id,$submitted));
 v3check(Translations::value('products',$id,'name','bn')==='বাংলা name','Bengali stored separately');
 $_GET=['lang'=>'en'];$_SERVER['REQUEST_METHOD']='GET';Locale::boot();
 v3check(plain(Translations::row('products',$p)['name'])==='Custom biryani','English public content selection');
 $_GET=['lang'=>'bn'];Locale::boot();v3check(Translations::row('products',$p)['name']==='বাংলা name','Bengali public content selection');
 v3check(Translations::draft('products',$id,'name','en','old')===$submitted['en']['name'],'Editor restores saved language');
 DB::run('UPDATE products SET price=100 WHERE id=?',[$id]);
 $quote=OrderService::quote([$id=>1],'delivery');v3check($quote['subtotal']===100,'Delivery accepts one-taka item below former minimum');
 v3check($quote['total']===$quote['subtotal']+$quote['delivery_fee'],'Server adds delivery charge');
 v3check(OrderService::quote([$id=>1],'pickup')['delivery_fee']===0,'Pickup charge is zero');
 $customer=['customer_name'=>'QA Guest','email'=>'qa@example.test','phone'=>'01712345678','address'=>'Dhaka','order_type'=>'delivery','notes'=>'','payment_method'=>'cod'];
 $key=bin2hex(random_bytes(32));$o=OrderService::create([$id=>1],$customer,$key);
 v3check($o['id']===OrderService::create([$id=>1],$customer,$key)['id'],'Checkout idempotency');
 v3check(count(OrderService::events((int)$o['id']))===1,'Initial tracking event recorded once');
 $item=DB::one('SELECT * FROM order_items WHERE order_id=?',[$o['id']]);
 v3check(OrderService::itemName($item)==='বাংলা name','Bengali receipt snapshot');
 $_GET=['lang'=>'en'];Locale::boot();v3check(OrderService::itemName($item)==='Custom biryani','English receipt strips rich markup');
 v3check(OrderService::itemName(['name'=>'কাচ্চি-<span class="ql-color-black">বিরিয়ানি</span>'])==='কাচ্চি-বিরিয়ানি','Historical receipt strips markup');
 foreach(['Confirmed','Preparing','Ready','Out for delivery','Delivered'] as $status)OrderService::transition((int)$o['id'],$status);
 v3check(count(OrderService::events((int)$o['id']))===6,'Complete delivery status history');
 $blocked=false;try{OrderService::transition((int)$o['id'],'Pending');}catch(ValidationException){$blocked=true;}v3check($blocked,'Terminal status cannot reopen');
 v3check(!in_array('Out for delivery',OrderService::nextStatuses(['status'=>'Ready','order_type'=>'pickup']),true),'Pickup excludes delivery dispatch');
 v3check(OrderService::normalizePhone('+8801712345678')===OrderService::normalizePhone('01712345678'),'Tracking phone normalization');
 v3check(OrderService::normalizePhone('01712345679')!==OrderService::normalizePhone('01712345678'),'Different phone cannot match');
 $count=count(OrderService::events((int)$o['id']));require ROOT.'/database/upgrade-v3.php';require ROOT.'/database/upgrade-v3.php';
 v3check(count(OrderService::events((int)$o['id']))===$count,'Repeated upgrade does not duplicate events');
 v3check(Translations::value('products',$id,'name','bn')==='বাংলা name','Upgrade preserves translated content');
 v3check(DB::one('SELECT id FROM orders WHERE id=?',[$o['id']])!==null,'Upgrade preserves orders');
 v3check(count(Content::products('Custom biryani'))===1,'Search finds stored English translation');
 v3check(count(Content::products('বাংলা name'))===1,'Search finds stored Bengali translation');
 echo "$checks v3 checks passed.\n";
} finally {if($isolated)qaDatabase('drop',$isolated);echo ob_get_clean();}
