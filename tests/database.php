<?php
require_once dirname(__DIR__).'/app/Core/bootstrap.php';
function qaDatabase(string $action, ?string $name=null): string {
 if(env('ALLOW_QA_WRITES')!=='true')throw new RuntimeException('Set ALLOW_QA_WRITES=true on a test MySQL server.');
 $name??='koji_test_'.bin2hex(random_bytes(6));
 if(!preg_match('/^koji_test_[a-f0-9]{12}$/',$name))throw new RuntimeException('Invalid isolated test database name.');
 $pdo=new PDO('mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';charset=utf8mb4',env('DB_USERNAME','root'),env('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 if($action==='create')$pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
 elseif($action==='drop')$pdo->exec('DROP DATABASE `'.$name.'`');else throw new RuntimeException('Invalid test action.');
 return $name;
}
if(realpath($_SERVER['SCRIPT_FILENAME'])===__FILE__)echo qaDatabase($argv[1]??'',$argv[2]??null);
