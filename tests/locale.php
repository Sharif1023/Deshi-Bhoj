<?php
// Run: php tests/locale.php (no database required)
require dirname(__DIR__).'/app/Core/bootstrap.php';
use App\Services\Locale;
$count = 0;
function verify(bool $ok, string $label): void {
    global $count;
    if (!$ok) throw new RuntimeException('FAIL: '.$label);
    $count++;
}
$_SERVER['REQUEST_METHOD']='GET'; $_COOKIE=[]; $_GET=[];
Locale::boot(); verify(Locale::current()==='en','Default English');
verify(Locale::text('কার্ট (3)')==='Cart (3)','Cart quantity preserved');
verify(Locale::text(' কাচ্চি বিরিয়ানি ')===' Kacchi biryani ','Whitespace preserved');
verify(Locale::text('আনুমানিক ২০–৩০ মিনিট')==='Approx. 20–30 minutes','Composite time');
verify(Locale::text('কালা ভুনা × 2')==='Kala bhuna × 2','Order item');
verify(Locale::text('My custom content')==='My custom content','Unknown content unchanged');
$html=Locale::html('<!doctype html><html><head><title>খাবারের মেনু · দেশি ভোজ</title></head><body><p>কার্ট (2)</p><input name="q" value="কাচ্চি বিরিয়ানি" placeholder="খাবার খুঁজুন"><input type="hidden" name="_token" value="abc123"><textarea>কাচ্চি বিরিয়ানি</textarea><a href="/menu?category=biryani">মেনু</a><span data-no-translate>নাম</span><script>const label="মেনু";</script></body></html>');
$dom=new DOMDocument();@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xp=new DOMXPath($dom);
verify($dom->documentElement->getAttribute('lang')==='en','HTML language');
verify($xp->evaluate('string(//title)')==='Food menu · Deshi Bhoj','Page title');
verify($xp->evaluate('string(//input[@name="q"]/@value)')==='কাচ্চি বিরিয়ানি','Input unchanged');
verify($xp->evaluate('string(//input[@name="q"]/@placeholder)')==='Search dishes','Placeholder translated');
verify($xp->evaluate('string(//input[@name="_token"]/@value)')==='abc123','CSRF unchanged');
verify($xp->evaluate('string(//textarea)')==='কাচ্চি বিরিয়ানি','Textarea unchanged');
verify($xp->evaluate('string(//a/@href)')==='/menu?category=biryani','Link unchanged');
verify($xp->evaluate('string(//span)')==='নাম','Protected content unchanged');
verify(str_contains($xp->evaluate('string(//script)'),'মেনু'),'Script unchanged');
verify(in_array('কাচ্চি বিরিয়ানি',Locale::searchTerms('kacchi'),true),'English search');
$_GET=['lang'=>'bn'];Locale::boot();verify(Locale::current()==='bn','Select Bangla');
verify(Locale::text('Your cart has been updated.')==='আপনার কার্ট আপডেট হয়েছে।','Bangla flash');
verify(Locale::text('কার্ট (3)')==='কার্ট (3)','Original Bangla retained');
$_GET=[];$_COOKIE=['site_language'=>'bn'];Locale::boot();verify(Locale::current()==='bn','Cookie preference');
$_GET=['lang'=>['en']];Locale::boot();verify(Locale::current()==='bn','Array ignored');
$_GET=['lang'=>'../../etc/passwd'];Locale::boot();verify(Locale::current()==='bn','Invalid language ignored');
$_COOKIE=['site_language'=>'invalid'];$_GET=[];Locale::boot();verify(Locale::current()==='en','Invalid cookie fallback');
$_SERVER['REQUEST_METHOD']='POST';$_GET=['lang'=>'bn'];Locale::boot();verify(Locale::current()==='en','POST does not switch language');
echo "$count language checks passed.\n";
