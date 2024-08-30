<?php

$csp_option = isset($_GET['csp']) ? $_GET['csp'] : '';
$csp = '';
$nonce = false;
$nonce_attribute = '';

switch ($csp_option) {
  case 'none':
    $csp = '';
    break;
  case 'enforced':
    $csp = "content-security-policy: default-src 'self';";
    break;
  case 'enforced-self':
    $csp = "content-security-policy: default-src 'self';";
  case 'report-only':
    $csp = "content-security-policy-report-only: default-src 'self';";
    break;
  case 'unsafe-inline':
    $csp = "content-security-policy: default-src 'self' 'unsafe-inline' fonts.googleapis.com static.addtoany.com pbs.twimg.com fonts.gstatic.com www.youtube.com w.soundcloud.com;";
    break;
  case 'hash':
    $policies = [
      'default-src',
      '\'self\'',
      'fonts.googleapis.com',
      'fonts.gstatic.com',
      'static.addtoany.com',
      'www.youtube.com',
      'https://pbs.twimg.com',
      'w.soundcloud.com',
      '\'sha256-dadL/maigdac9kyYKsSxUmw/Mj0iCSdr5nVx4zTJARY=\'', // The audiowide font.
//    '\'sha256-aGSaVsy2B0PTiMliSGlULZ1jBpm01TIahO82wjGzxT8=\'', // The inline js example (no hash, so let if be blocked.)
//    '\'sha256-KiD+CBpemmD9ST0Cxs7gGroKznVscKSN9B3EsU/xcEE=\'',  // The nonce js example. let fail.
      '\'sha256-9m5ZYdQpD7bOrk7D4hj7D991rkdrUtKisZ2FiiOCzxI=\'', // inline image .
      '\'sha256-4J8+swjpXzJqezCClmAbHMHlahnf2WGWxdFHouce0EE=\'', // hash js example.
      '\'sha256-rzkjI77fzABKN64xUTJ3vqEM6jchqET51GYeZdYh3Rg=\'', // Youtube
      '\'sha256-JMeWrM1/oCUD5M4FnrlNUWNkgHF4Z05ZPARrDlklsWo=\'',
      '\'sha256-9m5ZYdQpD7bOrk7D4hj7D991rkdrUtKisZ2FiiOCzxI=\'', // attribute inline style. Does not work in chrome, but does work in firefox.

      '\'sha256-w7vyv9EyxUMstR93JzPSMf5Ik2nmyz60L9hnmT5FOIQ=\'', // AddToAny updated 08/2024
      '\'sha256-Kd0zQQiHTqHGCnKzauVxIUj/nq4oZmiXUGENiDXgwE8=\'', // AddToAny updated 08/2024
      '\'sha256-47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=\'', // AddToAny updated 08/2024
      '\'sha256-X7EzNh+qw8yo804CRJMcy3pSiwJIy7onG3OQwmsT8j8=\'', // AddToAny updated 08/2024
      //'data:'
    ];
    $csp = "content-security-policy: " . implode(' ', $policies) . ";";
    break;
  case 'nonce':
    $nonce = 'ABC123';
    $nonce_attribute = 'nonce="' . $nonce . '"';

    $policies = [
      'default-src',
      '\'self\'',
      'fonts.googleapis.com',
      'fonts.gstatic.com',
      'static.addtoany.com',
      'www.youtube.com',
      'https://pbs.twimg.com',
      'w.soundcloud.com',
      '\'nonce-'.$nonce.'\'',
    ];
    $csp = "content-security-policy: " . implode(' ', $policies) . ";";
    break;

  case 'complete' :
    $nonce = 'ABC123';
    $nonce_attribute = 'nonce="' . $nonce . '"';

    $policies = [
      'default-src',
      '\'self\'',
      'fonts.googleapis.com',
      'static.addtoany.com',
      'nonce-'. $nonce .'',
      '\'sha256-4J8+swjpXzJqezCClmAbHMHlahnf2WGWxdFHouce0EE=\'', // hash js example.


    ];

    $csp = "content-security-policy: " . implode(' ', $policies) . ";";
    break;
}


/*
content-security-policy: default-src 'self'; font-src 'self' d2rluhlsrx2f7f.cloudfront.net dujgk33i56scb.cloudfront.net fonts.gstatic.com; frame-src 'self' www.google.com; img-src 'self' d2rluhlsrx2f7f.cloudfront.net dujgk33i56scb.cloudfront.net scontent-iad3-1.cdninstagram.com static.addtoany.com www.google-analytics.com scontent-lga3-2.cdninstagram.com data:; script-src 'self' 'unsafe-inline' www.googletagmanager.com www.google.com www.gstatic.com www.google-analytics.com js-agent.newrelic.com bam.nr-data.net d2rluhlsrx2f7f.cloudfront.net dujgk33i56scb.cloudfront.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://static.addtoany.com; style-src 'self' 'unsafe-inline' static.addtoany.com d2rluhlsrx2f7f.cloudfront.net dujgk33i56scb.cloudfront.net fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; frame-ancestors 'self'; report-uri http://rti-sra.forumone.dev/report-uri/enforce
*/

if ($csp) {
    header($csp);
}

include 'demo.php';
?>
