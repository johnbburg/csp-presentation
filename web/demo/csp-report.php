<?php

/**
 * Mock CSP violation-report endpoint for the demo.
 *
 * Accepts the POST bodies that browsers send for the `report-to` directive
 * (application/reports+json) and the legacy `report-uri` directive
 * (application/csp-report), does nothing with them, and returns
 * 204 No Content — the response browsers expect from a reporting endpoint.
 *
 * This exists only so the demo's report-only CSP has somewhere to POST to
 * without generating console errors; it doesn't log, store, or inspect
 * anything a caller sends.
 */

http_response_code(204);
