<?php

/**
 * Advanced CSP demo — the directives people get wrong.
 *
 * The sibling demo (index.php) covers source allow-listing: which hosts may
 * serve scripts, styles, images and frames. This one covers directives that
 * behave in ways the allow-listing mental model does not predict:
 *
 *   - form-action, base-uri, frame-ancestors, sandbox and
 *     upgrade-insecure-requests do NOT fall back to default-src. A policy of
 *     `default-src 'self'` gives you exactly zero protection on any of them.
 *   - object-src covers a script-execution sink that script-src does not see.
 *   - worker-src falls back to child-src, so a child-src entry that looks dead
 *     is still governing something.
 *   - Two CSP headers are both enforced; the effective policy is their
 *     intersection, not their union.
 *
 * Each case below changes one directive against an otherwise permissive
 * baseline, so the directive under test is the only variable.
 */

$csp_option = isset($_GET['csp']) ? $_GET['csp'] : '';

// Emitted policy, additional headers, and page-shape switches the view reads.
$csp = '';
$extra_csp = '';
$extra_header = '';
$nonce = FALSE;
$nonce_attribute = '';
$inject_base = FALSE;

// Same-origin links and assets in the view use relative references. The
// absolute hosts below are intentionally cross-origin: those cases need a
// second origin to demonstrate what the relevant CSP directive blocks.
//
// The host from the real-world incident in the slides: a Google Ads tag added
// through the GTM UI, beaconing to a domain nobody on the dev team allow-listed.
// The CSP violation fires before the network request, so this demonstrates
// correctly even with no internet connection.
$beacon_host = 'https://ad.doubleclick.net';

// Permissive baseline. Everything the instrumentation needs is allowed, so any
// breakage you see is caused by the directive the case is demonstrating. The
// beacon host is allow-listed here on purpose: without it, connect-src would
// fall back to default-src and the beacon would be blocked in every case,
// which would hide what the connect-src case is actually showing.
$img_hosts = 'https://picsum.photos https://fastly.picsum.photos';
$base = "default-src 'self' 'unsafe-inline' data: {$img_hosts}";
$connect = "connect-src 'self' {$beacon_host}";
// Same baseline minus the image hosts. Used only as the second policy in the
// two-headers case, so that the intersection has something to bite on.
$base_no_img = "default-src 'self' 'unsafe-inline' data:";

switch ($csp_option) {
  case 'none':
    break;

  // --- form-action -----------------------------------------------------
  // Not a fetch directive, so it does not inherit from default-src.

  case 'form-action-absent':
    $csp = "content-security-policy: {$base}; {$connect};";
    break;

  case 'form-action':
    $csp = "content-security-policy: {$base}; {$connect}; form-action 'self';";
    break;

  // --- base-uri --------------------------------------------------------
  // A single injected <base> tag reroutes every relative URL on the page.

  case 'base-uri-absent':
    $inject_base = TRUE;
    $csp = "content-security-policy: {$base}; {$connect};";
    break;

  case 'base-uri':
    $inject_base = TRUE;
    $csp = "content-security-policy: {$base}; {$connect}; base-uri 'none';";
    break;

  // --- frame-ancestors -------------------------------------------------
  // Supersedes X-Frame-Options. Ignored entirely in a <meta> tag.

  case 'frame-ancestors-absent':
    $csp = "content-security-policy: {$base}; {$connect};";
    break;

  case 'frame-ancestors':
    $csp = "content-security-policy: {$base}; {$connect}; frame-ancestors 'none';";
    break;

  // --- connect-src -----------------------------------------------------
  // Permission to execute a script does not grant it permission to connect.

  case 'connect-src':
    $nonce = 'ABC123';
    $nonce_attribute = 'nonce="' . $nonce . '"';
    $csp = "content-security-policy: script-src 'nonce-{$nonce}'; "
      . "connect-src 'self'; img-src 'self' data: {$img_hosts}; style-src 'self' 'unsafe-inline';";
    break;

  // --- worker-src / child-src ------------------------------------------
  // worker-src falls back to child-src, then script-src, then default-src.

  case 'worker-via-child-src':
    $csp = "content-security-policy: default-src 'none'; script-src 'self' 'unsafe-inline'; "
      . "style-src 'self' 'unsafe-inline'; img-src 'self' data: {$img_hosts}; object-src 'self'; "
      . "{$connect}; child-src 'self';";
    break;

  case 'worker-src':
    $csp = "content-security-policy: default-src 'none'; script-src 'self' 'unsafe-inline'; "
      . "style-src 'self' 'unsafe-inline'; img-src 'self' data: {$img_hosts}; object-src 'self'; "
      . "{$connect}; child-src 'self'; worker-src 'none';";
    break;

  // --- script-src-attr vs script-src-elem -------------------------------
  // The granular split: a <script> block and an onclick= are different things.

  case 'script-attr':
    $csp = "content-security-policy: {$base}; {$connect}; script-src-elem 'self' 'unsafe-inline'; script-src-attr 'none';";
    break;

  // --- object-src ------------------------------------------------------
  // <object>/<embed> execute script, and script-src does not govern them.

  case 'object-src-absent':
    $csp = "content-security-policy: {$base}; {$connect};";
    break;

  case 'object-src':
    $csp = "content-security-policy: {$base}; {$connect}; object-src 'none';";
    break;

  // --- two policies at once ---------------------------------------------
  // Both are enforced. A resource must satisfy BOTH, so the effective policy
  // is the intersection. The second header below omits the image hosts that
  // the first one allows, and the stricter header wins.

  case 'double-header':
    $csp = "content-security-policy: {$base}; {$connect};";
    $extra_csp = "content-security-policy: {$base_no_img}; {$connect};";
    break;

  // --- upgrade-insecure-requests -----------------------------------------

  case 'upgrade-insecure':
    $csp = "content-security-policy: {$base}; {$connect}; upgrade-insecure-requests;";
    break;

  // --- sandbox -----------------------------------------------------------
  // The iframe sandbox attribute, applied to a top-level document. Scripts are
  // allowed here so the page can report on itself; forms and popups are not.

  case 'sandbox':
    $csp = "content-security-policy: {$base}; {$connect}; sandbox allow-scripts;";
    break;
}

if ($csp) {
  header($csp);
}
// The FALSE second argument appends rather than replaces, producing a second
// Content-Security-Policy response header.
if ($extra_csp) {
  header($extra_csp, FALSE);
}
if ($extra_header) {
  header($extra_header);
}

include 'advanced-demo.php';
