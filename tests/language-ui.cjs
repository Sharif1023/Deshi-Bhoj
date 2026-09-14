'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname,'../public/assets/app.js'),'utf8');
let count=0;
for (const lang of ['en','bn']) {
 const handlers={},navHandlers={},docHandlers={};let destination;
 const mobile={hidden:true};const nav={addEventListener:(n,f)=>navHandlers[n]=f,setAttribute:(n,v)=>nav[n]=v,focus:()=>{}};
 const select={addEventListener:(n,f)=>handlers[n]=f};
 const document={documentElement:{lang},querySelectorAll:()=>[],querySelector:s=>({'[data-language-select]':select,'[data-nav-toggle]':nav,'[data-mobile-nav]':mobile}[s]??null),addEventListener:(n,f)=>docHandlers[n]=f};
 vm.runInNewContext(source,{document,URL,window:{location:{href:'http://localhost:8000/menu?q=kacchi&category=biryani#food',assign:u=>destination=u},matchMedia:()=>({matches:true})}});
 navHandlers.click();assert.equal(mobile.hidden,false);assert.equal(nav.textContent,lang==='bn'?'বন্ধ করুন ×':'Close ×');count++;
 docHandlers.keydown({key:'Escape'});assert.equal(mobile.hidden,true);assert.equal(nav.textContent,lang==='bn'?'মেনু ☰':'Menu ☰');count++;
 handlers.change({target:{value:lang==='en'?'bn':'en'}});const url=new URL(destination);
 assert.equal(url.pathname,'/menu');assert.equal(url.searchParams.get('q'),'kacchi');assert.equal(url.searchParams.get('category'),'biryani');assert.equal(url.hash,'#food');assert.equal(url.searchParams.get('lang'),lang==='en'?'bn':'en');count++;
}
console.log(`${count} language UI checks passed.`);
