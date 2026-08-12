<?php

$csp_option = isset($_GET['csp']) ? $_GET['csp'] : '';
$csp = '';
// Optional extra response header (e.g. the Reporting-Endpoints header that
// pairs with the report-to directive).
$extra_header = '';
$nonce = false;
$nonce_attribute = '';

/**
 * This demo's own origin, e.g. "https://example.org".
 *
 * Set CSP_DEMO_ORIGIN in the environment to pin this. That is what a real
 * deployment should do, because the fallback below reads
 * $_SERVER['HTTP_HOST'] — the client-supplied Host header, which is a request
 * input and not a fact about the server.
 *
 * The pattern check only proves the value is shaped like a host. It cannot
 * tell your hostname from somebody else's, so on its own it does not stop a
 * request that supplies a different real domain. That is why the pin exists,
 * and why the directive that actually carries data (report-uri, below) uses a
 * same-origin relative URL instead of this.
 */
function demo_origin() {
  $pinned = getenv('CSP_DEMO_ORIGIN');
  if (is_string($pinned) && $pinned !== '') {
    return rtrim($pinned, '/');
  }
  $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
  if (!preg_match('/^[A-Za-z0-9.-]{1,253}(:[0-9]{1,5})?$/', $host)) {
    return '';
  }
  return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $host;
}

switch ($csp_option) {
  case 'none':
    $csp = '';
    break;
  case 'enforced':
  case 'enforced-self':
    $csp = "content-security-policy: default-src 'self';";
    break;
  case 'report-only':
    $csp = "content-security-policy-report-only: default-src 'self';";
    break;
  case 'unsafe-inline':
    $csp = "content-security-policy: default-src 'self' 'unsafe-inline' fonts.googleapis.com static.addtoany.com picsum.photos fastly.picsum.photos fonts.gstatic.com www.youtube.com w.soundcloud.com;";
    break;
  case 'hash':
    $policies = [
      'default-src',
      '\'self\'',
      'fonts.googleapis.com',
      'fonts.gstatic.com',
      'static.addtoany.com',
      'www.youtube.com',
      'picsum.photos',
      'fastly.picsum.photos',
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
      'picsum.photos',
      'fastly.picsum.photos',
      'w.soundcloud.com',
      '\'nonce-'.$nonce.'\'',
    ];
    $csp = "content-security-policy: " . implode(' ', $policies) . ";";
    break;

  case 'report-to':
    // report-uri is deprecated; the current mechanism is a named endpoint
    // declared in a Reporting-Endpoints response header and referenced by the
    // report-to directive. Both are sent here, which is the advice on the
    // slides: older browsers only understand report-uri.
    //
    // report-uri takes a URI reference, so a relative path works and resolves
    // against the document. That keeps reports same-origin by construction —
    // there is no hostname in it for a request to influence. Reporting-Endpoints
    // needs an absolute URL, so it uses the pinned origin above.
    $origin = demo_origin();
    if ($origin !== '') {
      $extra_header = 'reporting-endpoints: csp-endpoint="' . $origin . '/demo/csp-report.php"';
    }
    $csp = "content-security-policy-report-only: default-src 'self'; report-to csp-endpoint; report-uri /demo/csp-report.php;";
    break;

  case 'child-src':
    // Half one of the fallback-trap pair. child-src lists the iframe hosts and
    // no frame-src is defined, so frames fall back to child-src. Both embeds
    // load. Everything else is permissive so the frames are the only variable.
    $policies = [
      "default-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.gstatic.com static.addtoany.com picsum.photos fastly.picsum.photos",
      "child-src 'self' www.youtube.com w.soundcloud.com static.addtoany.com",
    ];
    $csp = "content-security-policy: " . implode('; ', $policies) . ";";
    break;

  case 'fallback-trap':
    // Half two. Identical to the above, plus a frame-src that omits the iframe
    // hosts. Because frame-src is now present, child-src is never consulted for
    // frames — its youtube/soundcloud entries are dead and both embeds break.
    // Adding a directive silently narrowed what its sibling was allowing.
    $policies = [
      "default-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.gstatic.com static.addtoany.com picsum.photos fastly.picsum.photos",
      "child-src 'self' www.youtube.com w.soundcloud.com static.addtoany.com",
      "frame-src 'self'",
    ];
    $csp = "content-security-policy: " . implode('; ', $policies) . ";";
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
if ($extra_header) {
    header($extra_header);
}

include 'demo.php';
?>
