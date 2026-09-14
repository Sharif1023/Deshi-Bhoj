<?php
require dirname(__DIR__).'/app/Core/bootstrap.php';
$cases=[
 '<p>Hello <strong>bold</strong> <em>italic</em> <span class="ql-color-red">red</span></p>'=>['<strong>bold</strong>','ql-color-red'],
 '<img src=x onerror=alert(1)><script>alert(1)</script><p onclick="x">Safe</p>'=>['<p>Safe</p>'],
 '<svg><script>alert(1)</script></svg><p>Text</p>'=>['<p>Text</p>'],
 '<span style="color:red" class="evil ql-color-gold" onclick="x">Gold</span>'=>['class="ql-color-gold"'],
];
foreach($cases as $input=>$expected){$out=rich($input);foreach($expected as $part)if(!str_contains($out,$part))throw new RuntimeException('Formatting lost: '.$out);if(preg_match('/script|onerror|onclick|style=|<svg|<img|class="evil/i',$out))throw new RuntimeException('Unsafe output: '.$out);}
if(str_contains(rich_inline('<p>Hello <strong>World</strong></p>'),'<p>'))throw new RuntimeException('Inline markup invalid');
if(rich('<p>A&nbsp;sentence&#160;wraps.</p>') !== '<p>A sentence wraps.</p>')throw new RuntimeException('Editor spaces must wrap');
echo "Rich-text formatting and malicious markup checks passed.\n";
