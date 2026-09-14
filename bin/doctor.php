<?php
$failed=false;
function checkLine(string $label,bool $ok,string $hint=''):void {global $failed;$failed=$failed||!$ok;echo ($ok?'[OK] ':'[CHECK] ').$label.($ok||!$hint?'':' — '.$hint).PHP_EOL;}
checkLine('PHP 8.2+',version_compare(PHP_VERSION,'8.2','>='));
foreach(['pdo_mysql','mbstring','dom','fileinfo'] as $extension)checkLine($extension,extension_loaded($extension),'Enable this extension in php.ini.');
foreach(['uploads','sessions','logs','backups'] as $name){$dir=ROOT.'/storage/'.$name;if(!is_dir($dir))@mkdir($dir,0775,true);checkLine('storage/'.$name,is_dir($dir)&&is_writable($dir),'Grant the PHP process write access.');}
echo 'CLI php.ini: '.(php_ini_loaded_file()?:'none').PHP_EOL;
echo 'file_uploads='.ini_get('file_uploads').'; upload_max_filesize='.ini_get('upload_max_filesize').'; post_max_size='.ini_get('post_max_size').PHP_EOL;
echo "Web PHP can use a different php.ini. Use bin/serve.bat or bin/serve.sh locally. GD is optional for uploads.\n";
try{App\Core\DB::one('SELECT COUNT(*) n FROM media');checkLine('MySQL media table',true);}catch(Throwable $e){checkLine('MySQL media table',false,'Check .env and run php bin/console install.');}
exit($failed?1:0);
