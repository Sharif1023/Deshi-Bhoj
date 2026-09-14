'use strict';
(() => {
 const token=()=>document.querySelector('input[name="_token"]')?.value||'';
 let pending=0;
 const lock=delta=>{pending+=delta;document.querySelectorAll('[data-content-form] button[type="submit"], [data-content-form] button:not([type])').forEach(b=>b.disabled=pending>0);};
 const button=(text,action,cls='btn-outline inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-medium text-ink hover:bg-stone-100')=>{const b=document.createElement('button');b.type='button';b.textContent=text;b.className=cls;b.addEventListener('click',action);return b;};
 async function fetchJSON(url,options={}){const response=await fetch(url,{credentials:'same-origin',...options,headers:{Accept:'application/json',...options.headers}});const type=response.headers.get('content-type')||'';const data=type.includes('application/json')?await response.json():null;if(!response.ok||!data){if(data?.error)throw new Error(data.error);if(response.redirected)throw new Error('সেশন শেষ হয়েছে। আবার login করে upload করুন।');const html=data?'':await response.text();const doc=new DOMParser().parseFromString(html,'text/html');throw new Error(doc.querySelector('main p.mb-8')?.textContent||'Upload failed ('+response.status+'). Run php bin/console doctor and check storage/logs/app.log.');}return data;}

 const swatchClasses = {"red":"!bg-[#c23935]","gold":"!bg-[#ac853b]","green":"!bg-[#398047]","blue":"!bg-[#2563eb]","purple":"!bg-[#7c3aed]","white":"!bg-[#fff]","black":"!bg-[#111]"};
 if(window.Quill){
  const Color=Quill.import('attributors/class/color');Color.whitelist=['red','gold','green','blue','purple','white','black'];Quill.register(Color,true);
  document.querySelectorAll('[data-richtext]').forEach(textarea=>{
   const container=document.createElement('div');container.className='rte-container overflow-hidden rounded-xl border border-stone-300 bg-white text-ink';
   const toolbar=document.createElement('div');toolbar.className='rte-toolbar [&.ql-toolbar]:border-0 [&.ql-toolbar]:[border-bottom:1px_solid_#dedbd3] [&.ql-toolbar]:font-[inherit] [&.ql-toolbar]:flex [&.ql-toolbar]:items-center [&.ql-toolbar]:flex-wrap [&.ql-toolbar]:[gap:5px] [&.ql-toolbar]:p-2.5 [&_.rte-swatch]:[width:22px] [&_.rte-swatch]:[height:22px] [&_.rte-swatch]:rounded-full [&_.rte-swatch]:[border:1px_solid_#a8a29e] [&_.rte-swatch]:overflow-hidden [&_.rte-swatch]:[text-indent:-9999px] [&_.rte-swatch]:p-0 [&_.rte-clear]:[width:auto] [&_.rte-clear]:[padding:0_7px] [&_.rte-clear]:text-xs';
   ['bold','italic','underline','strike'].forEach((format,i)=>{const b=document.createElement('button');b.type='button';b.className='ql-'+format;b.setAttribute('aria-label',format);b.title=format;toolbar.append(b);});
   const editor=document.createElement('div');editor.className='rte-editor [&_.ql-color-red]:text-[#c23935] [&_.ql-color-gold]:text-[#ac853b] [&_.ql-color-green]:text-[#398047] [&_.ql-color-blue]:text-[#2563eb] [&_.ql-color-purple]:text-[#7c3aed] [&_.ql-color-white]:text-[#fff] [&_.ql-color-black]:text-[#111] [&.ql-container]:border-0 [&.ql-container]:font-[inherit] [&.ql-container]:text-base [&_.ql-editor]:[min-height:110px] [&_.ql-editor]:[max-height:350px] [&_.ql-editor]:overflow-y-auto';
   textarea.after(container);container.append(toolbar,editor);
   const q=new Quill(editor,{theme:'snow',formats:['bold','italic','underline','strike','color'],modules:{toolbar},placeholder:'Write your content…'});
   if(textarea.value.includes('<'))q.clipboard.dangerouslyPasteHTML(textarea.value);else q.setText(textarea.value);
   editor.querySelector('.ql-editor').setAttribute('aria-label',document.querySelector('label[for="'+textarea.id+'"]')?.textContent||'Rich text editor');
   ['black','gold','red','green','blue','purple','white'].forEach(color=>{const b=button(color,()=>q.format('color',color),'rte-swatch !h-[22px] !w-[22px] !rounded-full !p-0 '+swatchClasses[color]);b.title=color+' text';b.setAttribute('aria-label',color+' text');b.addEventListener('mousedown',e=>e.preventDefault());toolbar.append(b);});
   const clear=button('Clear',()=>{const range=q.getSelection(true);if(range)q.removeFormat(range.index,range.length);},'rte-clear');clear.addEventListener('mousedown',e=>e.preventDefault());toolbar.append(clear);
   textarea.hidden=true;textarea.removeAttribute('required');
   const sync=()=>{textarea.value=q.getSemanticHTML();};q.on('text-change',sync);textarea.form?.addEventListener('submit',sync);
  });
 }
 document.querySelectorAll('[data-media-picker]').forEach(picker=>{
  const value=picker.querySelector('[data-media-value]'),area=picker.querySelector('[data-media-items]'),status=picker.querySelector('[data-media-status]'),multi=picker.dataset.multiple==='true';
  let items=value.value.split('\n').filter(Boolean);
  const draw=()=>{value.value=items.join('\n');area.replaceChildren();items.forEach((url,index)=>{const card=document.createElement('div');card.className='media-thumb overflow-hidden rounded-xl border border-stone-200 bg-white [&_img]:aspect-square [&_img]:w-full [&_img]:object-cover';const img=document.createElement('img');img.src=url;img.alt=index===0?'Cover image':'Product image '+(index+1);card.append(img);const controls=document.createElement('div');controls.className='media-controls flex flex-wrap gap-1 p-2';if(multi){controls.append(button(index===0?'Cover':'Make cover',()=>{items.splice(index,1);items.unshift(url);draw();},'media-action rounded border border-stone-200 px-2 py-1 text-xs hover:bg-stone-100'));if(index>0)controls.append(button('←',()=>{[items[index-1],items[index]]=[items[index],items[index-1]];draw();},'media-action rounded border border-stone-200 px-2 py-1 text-xs hover:bg-stone-100'));if(index<items.length-1)controls.append(button('→',()=>{[items[index+1],items[index]]=[items[index],items[index+1]];draw();},'media-action rounded border border-stone-200 px-2 py-1 text-xs hover:bg-stone-100'));}controls.append(button('Remove',()=>{items.splice(index,1);draw();},'media-action rounded border border-stone-200 px-2 py-1 text-xs hover:bg-stone-100'));card.append(controls);area.append(card);});};
  const add=url=>{if(multi){if(!items.includes(url)){if(items.length>=12)throw new Error('Maximum 12 images per product.');items.push(url);}}else items=[url];draw();};draw();
  picker.querySelector('[data-media-upload]').addEventListener('change',async event=>{
   const files=Array.from(event.target.files);status.textContent='';if((multi?items.length+files.length:files.length)>(multi?12:1)){status.textContent=multi?'Choose up to 12 images in total.':'Choose one image.';event.target.value='';return;}
   lock(1);try{for(const [i,file] of files.entries()){if(file.size>5*1024*1024)throw new Error(file.name+' exceeds 5 MB.');status.textContent='Uploading '+(i+1)+' of '+files.length+'…';const body=new FormData();body.append('_token',token());body.append('file',file);const result=await fetchJSON('/control-center/media/upload',{method:'POST',body});add(result.url);}status.textContent='Upload complete. Save your changes to publish.';}catch(error){status.textContent=error.message;}finally{lock(-1);event.target.value='';}
  });
  picker.querySelector('[data-media-library]').addEventListener('click',async()=>{
   const dialog=document.createElement('dialog');dialog.className='media-dialog m-auto max-h-[85vh] w-[min(900px,95vw)] overflow-y-auto rounded-2xl bg-paper p-6 backdrop:bg-[#101510d9] backdrop:backdrop-blur-sm';const head=document.createElement('div');head.className='flex items-center justify-between gap-4 mb-5';const title=document.createElement('h2');title.textContent='Choose an image';title.className='text-2xl';head.append(title,button('Close ×',()=>dialog.close()));dialog.append(head);const grid=document.createElement('div');grid.className='media-library-grid grid grid-cols-2 gap-4 sm:grid-cols-3';dialog.append(grid);document.body.append(dialog);dialog.addEventListener('close',()=>dialog.remove());dialog.showModal();try{const result=await fetchJSON('/control-center/media/list');if(!result.items.length)grid.textContent='Your library is empty. Upload your first image.';for(const item of result.items){const b=button('',()=>{try{add(item.url);dialog.close();}catch(error){status.textContent=error.message;dialog.close();}},'library-choice overflow-hidden rounded-xl border bg-white text-left [&_img]:aspect-square [&_img]:w-full [&_img]:object-cover [&_span]:block [&_span]:truncate [&_span]:p-2 [&_span]:text-xs');const img=document.createElement('img');img.src=item.url;img.alt=item.alt||item.original_name;const caption=document.createElement('span');caption.textContent=item.original_name;b.append(img,caption);grid.append(b);}}catch(error){grid.textContent=error.message;}
  });
 });
 document.querySelector('[data-admin-search]')?.addEventListener('input',e=>{const term=e.target.value.toLowerCase();document.querySelectorAll('[data-search]').forEach(row=>row.hidden=!row.dataset.search.includes(term));});
 document.querySelectorAll('[data-content-form]').forEach(form=>form.addEventListener('submit',event=>{if(pending){event.preventDefault();alert('Wait for image uploads to finish.');}}));
})();

document.querySelectorAll('[data-content-form]').forEach(form => {
 const switcher = form.querySelector('[data-content-language]');
 if (!switcher) return;
 const select = locale => {
  form.querySelectorAll('[data-content-panel]').forEach(panel => panel.hidden = panel.dataset.contentPanel !== locale);
  switcher.querySelectorAll('[data-content-locale]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.contentLocale === locale)));
  switcher.querySelector('[name="content_locale"]').value = locale;
 };
 switcher.querySelectorAll('[data-content-locale]').forEach(button => button.addEventListener('click', () => select(button.dataset.contentLocale)));
 select(switcher.dataset.initialLanguage === 'bn' ? 'bn' : 'en');
});
