const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const assert=require('node:assert/strict'),path=require('path'),fs=require('fs');
(async()=>{
 if(process.env.ALLOW_QA_WRITES!=='true')throw Error('Use a disposable QA installation with ALLOW_QA_WRITES=true');
 const base=process.env.TEST_BASE_URL||'http://127.0.0.1:8124';
 const browser=await chromium.launch({headless:true,executablePath:process.env.BROWSER_BINARY||undefined,args:['--no-sandbox','--disable-dev-shm-usage']});
 const page=await browser.newPage({viewport:{width:1440,height:1000}});const errors=[];page.on('pageerror',e=>{errors.push(e.message);console.error('PAGE ERROR',e.message)});
 const out=process.env.QA_SCREENSHOTS||'/tmp/koji-v2-qa';fs.mkdirSync(out,{recursive:true});let count=0;const pass=m=>{count++;console.log('PASS: '+m)};
 const capture=async file=>{await page.evaluate(async()=>{await Promise.all([...document.images].map(async img=>{img.loading='eager';try{await img.decode()}catch{throw Error('Image failed to load: '+img.getAttribute('src'))}}));});await page.evaluate(async()=>{for(let y=0;y<document.documentElement.scrollHeight;y+=600){window.scrollTo({top:y,behavior:'instant'});await new Promise(r=>setTimeout(r,30));}window.scrollTo({top:0,behavior:'instant'});});await page.waitForTimeout(750);await page.screenshot({path:out+'/'+file,fullPage:true,animations:'disabled'});};
 const go=async url=>{const r=await page.goto(base+url);assert.equal(r.status(),200,url);assert.doesNotMatch(await page.title(),/Something went wrong/);};
 await go('/');await capture('home.png');
 for(const url of ['/menu','/about','/gallery','/contact','/faq','/delivery','/privacy','/terms'])await go(url);
 pass('All public pages load');
 await go('/login');await page.getByLabel('Email',{exact:true}).fill(process.env.TEST_OWNER_EMAIL||'owner@example.com');await page.getByLabel('Password',{exact:true}).fill(process.env.TEST_OWNER_PASSWORD||'TestOwnerPassword!42');await page.getByRole('button',{name:'Sign in'}).click();
 await go('/control-center/products/1/edit?lang=en');

 const field=page.locator('#field-description-en').locator('..');const editor=field.locator('.ql-editor');await editor.fill('Freshly prepared QA food');await editor.press('ControlOrMeta+A');await field.getByRole('button',{name:'bold',exact:true}).click();await field.getByRole('button',{name:'gold text',exact:true}).click();
 const picker=page.locator('#editor [data-media-picker]');const old=await picker.locator('.media-thumb').count();
 await picker.locator('[data-media-upload]').setInputFiles([path.join(__dirname,'../public/assets/kacchi.png'),path.join(__dirname,'../public/assets/kala-bhuna.png')]);
 await picker.locator('[data-media-status]').filter({hasText:'Upload complete'}).waitFor();assert.equal(await picker.locator('.media-thumb').count(),old+2);pass('Multiple files upload with admin thumbnails');
 await picker.locator('.media-thumb').last().getByRole('button',{name:'Make cover',exact:true}).click();const cover=await picker.locator('.media-thumb img').first().getAttribute('src');assert.match(cover,/\/media\//);
 await page.locator('#editor').getByRole('button',{name:'Save changes',exact:true}).click();await page.getByRole('status').filter({hasText:'Changes saved'}).waitFor();
 await go('/control-center/products/1/edit?lang=en');assert.equal(await page.locator('#editor .media-thumb').count(),old+2);assert.equal(await page.locator('#editor .media-thumb img').first().getAttribute('src'),cover);pass('Gallery order and cover survive save/reload');
 await capture('admin-products.png');
 const slug=await page.locator('[name="slug"]').inputValue();await go('/menu/'+slug);assert.equal(await page.locator('[data-product-main]').getAttribute('src'),cover);assert.equal(await page.locator('[data-product-thumb]').count(),old+2);assert.equal(await page.locator('.rich-content strong').filter({hasText:'Freshly prepared QA food'}).count(),1);assert.equal(await page.locator('.rich-content .ql-color-gold').filter({hasText:'Freshly prepared QA food'}).count(),1);pass('Saved formatted text and uploaded cover appear publicly');
 const second=await page.locator('[data-product-thumb]').nth(1).getAttribute('data-product-thumb');await page.locator('[data-product-thumb]').nth(1).click();assert.equal(await page.locator('[data-product-main]').getAttribute('src'),second);await page.locator('[data-product-zoom]').click();await page.getByRole('dialog').waitFor();await page.getByRole('button',{name:'Close ×',exact:true}).click();pass('Public product thumbnails and zoom work');
 await capture('product.png');
 await go('/control-center/products/1/edit?lang=en');await page.locator('#editor .media-thumb').last().getByRole('button',{name:'Remove',exact:true}).click();await page.locator('#editor').getByRole('button',{name:'Save changes',exact:true}).click();await go('/menu/'+slug);assert.equal(await page.locator('[data-product-thumb]').count(),old+1);pass('Removing one image preserves the rest');
 await go('/control-center/settings?group=Homepage');const heroPicker=page.locator('[name="hero_image"]').locator('..');await heroPicker.locator('[data-media-upload]').setInputFiles(path.join(__dirname,'../public/assets/kacchi.png'));await heroPicker.locator('[data-media-status]').filter({hasText:'Upload complete'}).waitFor();const hero=await heroPicker.locator('.media-thumb img').getAttribute('src');
 await page.getByRole('button',{name:'Save changes',exact:true}).first().click();await page.getByRole('status').filter({hasText:'settings saved'}).waitFor();await go('/');assert.equal(await page.locator('img[fetchpriority="high"]').getAttribute('src'),hero);pass('Settings image upload publishes to homepage');
 await go('/control-center/products/1/edit?lang=en');await page.locator('#editor [data-media-library]').click();await page.getByRole('dialog').locator('.library-choice').first().waitFor();await page.getByRole('dialog').locator('.library-choice').first().click();assert.equal(await page.getByRole('dialog').count(),0);pass('Reusable media-library picker');
 for(const url of ['/','/menu','/menu/'+slug,'/about','/gallery','/contact','/faq','/checkout']){await page.setViewportSize({width:390,height:844});await go(url);assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true,url);}
 await go('/');await page.getByRole('button',{name:'Menu ☰',exact:true}).click();assert.equal(await page.locator('[data-mobile-nav]').isVisible(),true);await page.keyboard.press('Escape');assert.equal(await page.locator('[data-mobile-nav]').isVisible(),false);await capture('mobile.png');pass('Mobile pages have no overflow and menu is keyboard accessible');
 await page.emulateMedia({reducedMotion:'reduce'});await go('/');assert.equal(await page.locator('.reveal-ready').count(),0);pass('Reduced-motion preference respected');
 assert.deepEqual(errors,[]);pass('No uncaught JavaScript errors');
 await browser.close();console.log(count+' browser checks passed.');
})().catch(e=>{console.error(e);process.exit(1)});
