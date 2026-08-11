<?php

/**
 * Minimal CSP violation-report collector for the demo.
 *
 * Accepts the POST bodies that browsers send for the `report-to` directive
 * (application/reports+json) and the legacy `report-uri` directive
 * (application/csp-report). It appends them to a log file and returns
 * 204 No Content.
 *
 * A reporting endpoint is necessarily unauthenticated — browsers POST to it
 * without credentials, so it cannot require a token. That means anyone can
 * write to it, and the caps below exist so an anonymous caller can't turn this
 * into unbounded disk usage. This is illustrative only, not a production
 * collector: a real one would rate-limit per source, validate the report shape,
 * and write somewhere with retention rather than a flat file.
 */

// Largest single report we will store, in bytes. Genuine CSP reports are well
// under 2 KB.
const CSP_REPORT_MAX_BYTES = 8192;

// Ceiling for the whole log. Once reached, further reports are accepted and
// discarded rather than growing the file.
const CSP_REPORT_MAX_LOG_BYTES = 1048576;

$log_file = sys_get_temp_dir() . '/csp-reports.log';

// Read one byte past the cap so an oversized body can be detected and trimmed
// rather than stored whole.
$payload = file_get_contents('php://input', FALSE, NULL, 0, CSP_REPORT_MAX_BYTES + 1);

if ($payload !== FALSE && $payload !== '') {
  if (strlen($payload) > CSP_REPORT_MAX_BYTES) {
    $payload = substr($payload, 0, CSP_REPORT_MAX_BYTES) . ' …[truncated]';
  }

  // Both of these are client-supplied. Collapsing newlines keeps one report to
  // one line, so a caller can't inject entries that look like separate,
  // earlier reports. The Content-Type is additionally stripped of anything
  // outside printable ASCII and clipped to a sane length.
  $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'unknown';
  $content_type = substr(preg_replace('/[^\x20-\x7E]/', '', $content_type), 0, 64);
  $body = preg_replace('/[\r\n]+/', ' ', $payload);

  // filesize() is cached per request; clear it so the ceiling is checked
  // against the file as it is now.
  clearstatcache(TRUE, $log_file);
  $size = file_exists($log_file) ? filesize($log_file) : 0;

  if ($size < CSP_REPORT_MAX_LOG_BYTES) {
    $line = '[' . gmdate('c') . '] ' . $content_type . ' ' . $body . "\n";
    // Harmless if it can't be written.
    @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
  }
}

http_response_code(204);
