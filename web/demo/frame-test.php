<?php

/**
 * Framing test for the frame-ancestors demo.
 *
 * Stands in for an attacker's page: it embeds the advanced demo in an iframe and
 * reports whether the browser allowed it. The ?csp= value is passed straight
 * through to the framed page, so the only thing that changes between the two
 * links is whether that page sends frame-ancestors.
 */

$target_option = isset($_GET['csp']) ? $_GET['csp'] : 'frame-ancestors';
$allowed = ['frame-ancestors', 'frame-ancestors-absent'];
if (!in_array($target_option, $allowed, TRUE)) {
  $target_option = 'frame-ancestors';
}
$target = '/demo/advanced.php?csp=' . $target_option;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Framing test — frame-ancestors</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/demo/css/normalize.css">
  <link rel="stylesheet" href="/demo/css/skeleton.css">
  <link rel="stylesheet" href="/demo/css/custom.css">
  <style>
    .verdict { padding:10px 14px; border-radius:3px; font-weight:600; }
    .verdict-bad  { background:#c0392b; color:#fff; }
    .verdict-good { background:#27ae60; color:#fff; }
    .verdict-wait { background:#eee; color:#666; }
    iframe { border:3px solid #333; width:100%; height:420px; background:#fafafa; }
  </style>
</head>
<body>
<div class="container">
  <h1>Framing test</h1>
  <p>This page is standing in for an attacker's site. It is trying to embed the
    demo page in an iframe &mdash; the setup behind every clickjacking attack.</p>

  <p>Framing: <code><?php echo htmlspecialchars($target); ?></code></p>

  <p><span class="verdict verdict-wait" id="verdict">checking&hellip;</span></p>

  <p>
    <a class="button" href="/demo/frame-test.php?csp=frame-ancestors-absent">Try with frame-ancestors absent</a>
    <a class="button" href="/demo/frame-test.php?csp=frame-ancestors">Try with frame-ancestors 'none'</a>
    <a class="button" href="/demo/advanced.php?csp=frame-ancestors">Back to the demo</a>
  </p>

  <p>Note that <code>frame-ancestors</code> is enforced by the <em>framed</em>
    page, not by this one &mdash; and that it does not inherit from
    <code>default-src</code>. A policy of <code>default-src 'self'</code> leaves
    you fully frameable.</p>

  <iframe id="victim" src="<?php echo htmlspecialchars($target); ?>"></iframe>
</div>

<script>
(function () {
  'use strict';
  var f = document.getElementById('victim');
  var v = document.getElementById('verdict');

  // A frame blocked by frame-ancestors still fires load in some browsers, but
  // its document is inaccessible and stays about:blank. Check for real content.
  setTimeout(function () {
    var framed = false;
    try {
      var d = f.contentDocument;
      framed = !!(d && d.body && d.body.childElementCount > 0);
    }
    catch (err) {
      // A cross-origin throw would mean it did load; same-origin here, so a
      // throw means something unusual. Treat as framed.
      framed = true;
    }
    if (framed) {
      v.className = 'verdict verdict-bad';
      v.textContent = 'FRAMED — clickjackable. The page allowed itself to be embedded.';
    }
    else {
      v.className = 'verdict verdict-good';
      v.textContent = 'BLOCKED — frame-ancestors refused the embed.';
    }
  }, 2000);
}());
</script>
</body>
</html>
