// Regression checks for actionable upload responses; no server required.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync(__dirname+'/../public/assets/admin.js','utf8');
const fn=source.slice(source.indexOf(' async function fetchJSON'),source.indexOf('\n if(window.Quill)'));
let response;
const context=vm.createContext({fetch:async()=>response,DOMParser:class{parseFromString(){return {querySelector:()=>({textContent:'Folder is not writable'})};}}});
vm.runInContext(fn+'\nglobalThis.call=fetchJSON;',context);
(async()=>{
 response={ok:true,headers:{get:()=> 'application/json'},json:async()=>({url:'/media/'+'a'.repeat(40)+'.png'})};
 assert.match((await context.call('/upload')).url,/\.png$/);
 response={ok:false,headers:{get:()=> 'application/json'},json:async()=>({error:'post_max_size is too small'})};
 await assert.rejects(()=>context.call('/upload'),/post_max_size/);
 response={ok:true,redirected:true,headers:{get:()=> 'text/html'}};
 await assert.rejects(()=>context.call('/upload'),/login/);
 response={ok:false,status:422,headers:{get:()=> 'text/html'},text:async()=>'<main>error</main>'};
 await assert.rejects(()=>context.call('/upload'),/Folder is not writable/);
 console.log('PASS: upload success, JSON server error, expired login, HTML server error (4 checks)');
})().catch(error=>{console.error(error);process.exit(1);});
