<?php
use App\Core\DB;
use App\Services\Content;
$extra=[
 'brand.logo'=>'','navigation.home'=>'Home','navigation.menu'=>'Our menu','navigation.about'=>'Our story','navigation.gallery'=>'Gallery','navigation.contact'=>'Contact',
 'announcement'=>'আপনার পছন্দের দেশি খাবার',
 'home.menu_title'=>'A few favorites. <em>A lasting impression.</em>',
 'home.menu_description'=>'Familiar flavors, thoughtful details. Discover the dishes that tell our story, one beautiful bite at a time.',
 'home.reserve_title'=>'An evening to savor. <em>A table to call yours.</em>',
 'home.reserve_description'=>'A quiet dinner. A long-overdue catch-up. A little celebration. We would love to make it special.',
 'about.image'=>'/assets/kacchi.png','about.chef_image'=>'/assets/kala-bhuna.png',
 'social.instagram'=>'','social.facebook'=>'',
 'ordering.pickup_time'=>'20–30 minutes','ordering.delivery_time'=>'35–50 minutes',
 'ordering.service_area'=>'Delivery availability depends on your address. Please contact the restaurant to confirm coverage before ordering.',
 'allergen.notice'=>'Our kitchen handles fish, shellfish, soy, sesame, eggs and gluten. Tell us about allergies before ordering; we cannot guarantee an allergen-free kitchen.',
 'delivery.title'=>'From our kitchen to your table.',
 'delivery.body'=>'<p>Choose pickup or delivery at checkout. Preparation and delivery times are estimates, and our team will contact you if an order needs clarification.</p><p>Please provide a complete address and a reachable Bangladesh mobile number. Availability is confirmed by restaurant staff.</p>',
 'privacy.body'=>'<p>We use your name, email, phone number, delivery address and order details to prepare orders, handle reservations and respond to messages.</p><p>Session cookies keep your cart and staff login working. Online payment is handled by the payment provider; this website does not collect your wallet PIN or OTP.</p><p>Contact the restaurant using the contact page for questions about your information.</p>',
 'terms.body'=>'<p>Orders and reservation requests are subject to availability and staff confirmation. Menu prices are shown in BDT. Any delivery fee is displayed at checkout.</p><p>To request a change or cancellation, contact us as soon as possible with your order number. Preparation may already have started. Refund requests require staff review and are processed through the original payment method when approved.</p><p>Please inform us of food allergies before placing an order. Product photography is illustrative.</p>',
];
DB::transaction(function()use($extra){
 foreach($extra as $key=>$value) if(!DB::one('SELECT setting_key FROM settings WHERE setting_key=?',[$key]))Content::save($key,$value);
 if(!DB::one("SELECT setting_key FROM settings WHERE setting_key='system.edition2'")) {
  foreach(['Can I order for pickup?'=>'Yes. Select pickup at checkout and keep your order reference. Our team prepares your order and can confirm the collection time.','How do reservations work?'=>'Submit the reservation form with your preferred date, time and party size. Your request is pending until our team confirms availability.','Can you accommodate food allergies?'=>'Please contact us before ordering and include allergy notes at checkout. Our kitchen handles common allergens and cannot guarantee an allergen-free environment.','How can I pay?'=>'Cash at pickup or delivery is available. bKash and Nagad are offered through our payment partner when online payments are enabled.','Can I change my order?'=>'Contact the restaurant promptly with your order number. Changes depend on whether preparation has started.'] as $question=>$answer)DB::insert('faqs',['title'=>$question,'answer'=>$answer]);
  Content::save('system.edition2',true);
 }
});
