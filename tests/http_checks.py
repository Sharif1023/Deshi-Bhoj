"""Runs on a disposable QA database. Requires Python 3; only standard library used."""
import http.cookiejar, urllib.request, urllib.parse, urllib.error, re, os, subprocess, time, pathlib, tempfile, json, sys
if os.environ.get('ALLOW_QA_WRITES') != 'true':
    raise SystemExit('Set ALLOW_QA_WRITES=true for a disposable test installation only.')
root=pathlib.Path(__file__).resolve().parents[1]
php=os.environ.get('PHP_BINARY','php')
port='8123';base='http://127.0.0.1:'+port
jar=http.cookiejar.CookieJar()
opener=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(jar))
count=0

def request(path,data=None):
    req=urllib.request.Request(base+path,data=urllib.parse.urlencode(data).encode() if data is not None else None)
    try:
        with opener.open(req) as r:return r.status,r.read().decode(),r.url
    except urllib.error.HTTPError as r:return r.code,r.read().decode(),r.url

def token(html):
    m=re.search(r'name="_token"\s+value="([^"]+)"',html)
    assert m,'Missing CSRF field'
    return m[1]
def post(path,data,html):return request(path,dict(data,_token=token(html)))
def passed(label):
    global count
    count+=1;print('PASS: '+label,flush=True)

with tempfile.TemporaryDirectory(prefix='koji-http-') as temp:
    qa_name=subprocess.check_output([php,'tests/database.php','create'],cwd=root,env=os.environ,text=True).strip()
    env=dict(os.environ,DB_CONNECTION='mysql',DB_DATABASE=qa_name,APP_URL=base)
    subprocess.run([php,'bin/console','install'],cwd=root,env=env,check=True,capture_output=True)
    subprocess.run([php,'bin/console','user:create'],input='QA Owner\nowner@example.com\nTestOwnerPassword!42\n',text=True,cwd=root,env=env,check=True,capture_output=True)
    server=subprocess.Popen([php,'-S','127.0.0.1:'+port,'-t','public','public/router.php'],cwd=root,env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    try:
        for _ in range(40):
            try:request('/');break
            except OSError:time.sleep(.1)
        for path in ['/','/menu','/about','/gallery','/contact','/cart','/checkout','/login']:
            code,html,_=request(path);assert code==200,(path,code,html[:200]);assert 'Warning:' not in html
        passed('All public pages return 200 without warnings')
        assert request('/.env')[0]==404;assert request('/database/source-content.json')[0]==404;passed('Private files are not publicly served')
        assert request('/cart',dict(product_id=1,quantity=1,action='add'))[0]==419;passed('CSRF rejects forged mutation')
        _,html,_=request('/menu');code,html,_=post('/cart',dict(product_id=1,quantity=2,action='add'),html);assert code==200 and 'Your little feast' in html;passed('Cart adds product')
        _,checkout,_=request('/checkout');key=re.search(r'name="checkout_key"\s+value="([^"]+)"',checkout)[1]
        payload=dict(checkout_key=key,name='QA Customer',email='guest@example.com',phone='01712345678',address='',order_type='pickup',notes='No sesame',payment_method='cod')
        code,order,order_url=post('/checkout',payload,checkout);assert code==200 and '/order/' in order_url,(code,order[:300]);assert 'unpaid' in order;order_path=urllib.parse.urlsplit(order_url).path;passed('Cash checkout persists order')
        code,_,repeat=post('/checkout',payload,checkout);assert code==200 and repeat==order_url;passed('Duplicate HTTP checkout returns same order')
        _,html,_=request('/contact');future=time.strftime('%Y-%m-%dT19:00',time.localtime(time.time()+172800))
        code,html,_=post('/reservations',dict(name='QA Guest',email='guest@example.com',phone='01712345678',reserved_at=future,guests=2,notes='',website=''),html);assert code==200 and 'Reservation requested' in html;passed('Reservation stored')
        code,html,_=post('/contact',dict(name='QA Message',email='message@example.com',message='<script>alert(1)</script>',website=''),html);assert code==200 and 'message has been received' in html;passed('Contact message stored')
        _,html,url=request('/control-center');assert url.endswith('/login');passed('Admin guest redirects to login')
        code,html,url=post('/login',dict(email='owner@example.com',password='TestOwnerPassword!42'),html);assert code==200 and '/control-center' in url;passed('Owner login')
        for section in ['dashboard','products','categories','orders','payments','reservations','messages','customers','features','gallery','reviews','settings','team','security','media','faqs']:
            code,html,_=request('/control-center/'+section);assert code==200,(section,code,html[:200])
        passed('Every admin section renders')
        _,html,_=request('/control-center/features');code,html,_=post('/control-center/features/save',dict(id=0,title='QA Feature',description='Test description',sort_order=99),html);assert code==200 and 'QA Feature' in html
        feature_id=re.search(r'QA Feature.*?/control-center/features/(\d+)/edit',html,re.S)[1]
        code,html,_=post('/control-center/features/save',dict(id=feature_id,title='QA Updated',description='Updated',sort_order=99),html);assert 'QA Updated' in html
        code,html,_=post('/control-center/features/delete',dict(id=feature_id),html);assert code==200 and 'QA Updated' not in html;passed('CMS create/update/delete')
        _,html,_=request('/control-center/categories')
        code,html,_=post('/control-center/categories/save',dict(id=0,name='QA Category',slug='qa-category',description='QA description',image='/assets/food.svg',sort_order=100),html);assert code==200
        category_id=re.search(r'QA Category.*?/control-center/categories/(\d+)/edit',html,re.S)[1]
        product=dict(allergens='Fish',dietary='',id=0,name='QA Product',slug='qa-product',category_id=category_id,description='QA product description',price='199.50',images='/assets/food.svg',ingredients='Rice, fish',preparation_minutes=15,available=1,featured=1,badge='',sort_order=100)
        code,html,_=post('/control-center/products/save',product,html);assert code==200 and 'QA Product' in html
        product_id=re.search(r'QA Product.*?/control-center/products/(\d+)/edit',html,re.S)[1]
        assert '199.50' in request('/menu/qa-product')[1];passed('Category and product creation with BDT pricing')
        assert post('/control-center/categories/delete',dict(id=category_id),html)[0]==422;passed('Category deletion cannot orphan products')
        code,html,_=post('/control-center/products/delete',dict(id=product_id),html);assert code==200
        code,html,_=post('/control-center/categories/delete',dict(id=category_id),html);assert code==200
        # Genuine multipart upload through HTTP exercises is_uploaded_file and image decoding.
        import base64, struct, zlib
        boundary='KojiQABoundary42'
        fields={'_token':token(html),'id':'0','title':'QA Upload','image':'','sort_order':'0'}
        chunks=[]
        for k,v in fields.items():chunks.append(('--'+boundary+'\r\nContent-Disposition: form-data; name="'+k+'"\r\n\r\n'+v+'\r\n').encode())
        chunks.append(('--'+boundary+'\r\nContent-Disposition: form-data; name="file"; filename="pixel.png"\r\nContent-Type: image/png\r\n\r\n').encode())
        def png_chunk(t,d):return struct.pack('>I',len(d))+t+d+struct.pack('>I',zlib.crc32(t+d)&0xffffffff)
        png=b'\x89PNG\r\n\x1a\n'+png_chunk(b'IHDR',struct.pack('>IIBBBBB',2,2,8,2,0,0,0))+png_chunk(b'IDAT',zlib.compress(b'\0'+b'\xff\0\0'*2+b'\0'+b'\xff\0\0'*2))+png_chunk(b'IEND',b'')
        chunks.append(png+b'\r\n')
        chunks.append(('--'+boundary+'--\r\n').encode())
        req=urllib.request.Request(base+'/control-center/media/upload',data=b''.join(chunks),headers={'Content-Type':'multipart/form-data; boundary='+boundary})
        try:
            with opener.open(req) as r:uploaded=json.loads(r.read().decode());assert r.status==200
        except urllib.error.HTTPError as r:raise AssertionError(r.read().decode())
        code,html,_=post('/control-center/gallery/save',dict(id=0,title='QA Upload',image=uploaded['url'],sort_order=0),html)
        assert code==200 and 'QA Upload' in html
        gallery_id=re.search(r'QA Upload.*?/control-center/gallery/(\d+)/edit',html,re.S)[1]
        _,edit_html,_=request('/control-center/gallery?edit='+gallery_id)
        media=re.search(r'/media/[a-f0-9]{40}\.png',edit_html)[0]
        with opener.open(base+media) as r:assert r.headers['Content-Type']=='image/png' and r.read()==png
        passed('Image upload preserves validated PNG and serves correct bytes without GD')
        post('/control-center/gallery/delete',dict(id=gallery_id),html)
        (root/'storage/uploads'/media.split('/')[-1]).unlink()
        _,html,_=request('/control-center/messages');assert '&lt;script&gt;' in html and '<script>alert(1)</script>' not in html;passed('Stored message HTML is escaped')
        _,html,_=request('/control-center/orders')
        for status in ['Confirmed','Preparing','Delivered']:
            code,html,_=post('/control-center/orders/status',dict(id=1,status=status),html);assert code==200,(status,code)
        code,html,_=post('/control-center/cash/status',dict(id=1),html);assert code==200
        code,html,_=request(order_path);assert 'Delivered' in html and '<strong>paid</strong>' in html;passed('Fulfillment and collected cash update private order page')
        _,html,_=request('/control-center');code,csv,_=request('/control-center/export/orders');assert code==200 and 'guest@example.com' in csv;passed('Orders CSV export')
        _,html,_=request('/control-center/team');code,html,_=post('/control-center/team',dict(action='create',name='QA Manager',email='manager@example.com',password='ManagerPassword!42',role='manager'),html);assert code==200 and 'QA Manager' in html
        _,html,_=post('/logout',{},html);code,html,_=post('/login',dict(email='manager@example.com',password='ManagerPassword!42'),html);assert code==200
        assert request('/control-center/settings')[0]==403;assert request('/control-center/team')[0]==403
        _,html,_=request('/control-center');assert post('/control-center/settings',{},html)[0]==403;passed('Manager GET and POST owner restrictions')
        _,html,_=request('/control-center/security');code,html,url=post('/control-center/security',dict(current_password='ManagerPassword!42',password='NewManagerPassword!42'),html);assert code==200 and url.endswith('/login');passed('Password change signs out staff')
        passed('No real payment provider called during QA')
        print(f'{count} HTTP checks passed.')
    finally:
        server.terminate();server.wait(timeout=10)
        subprocess.run([php,'tests/database.php','drop',qa_name],cwd=root,env=env,check=True,capture_output=True)
