# Presentation TODOs

Open items for the CSP talk (`web/pres/index.html`) and its demos (`web/demo/`).
Presenting week of 2026-08-10.

---

## Blocking — fix before presenting

### 1. Dead WebPageTest links on "Don't feel too bad" (slide 1.2)

Both links return HTTP 200 but render *"Test Result Expired. Sorry, the test you
requested has expired. Test results are kept for 13 months."* The tests are from
September 2020.

The screenshots themselves are local files and still display fine — only the
hyperlinks are dead.

Pick one:

- Remove the two `<a href="https://www.webpagetest.org/result/…">` wrappers and
  keep the images as dated artifacts. Cheapest.
- Caption them "September 2020" and leave the links off.
- Run a fresh WebPageTest on google.com the morning of the talk, screenshot it,
  and replace both image and link. Strongest opener — a live 2026 result beats a
  six-year-old one.

### 2. Decide which demos to run live

61+ slides plus two demo sections will not fit a 45-minute slot. Three worth
running live, in this order:

1. `form-action` — `/demo/advanced.php?csp=form-action-absent`. Biggest reaction:
   an enforced CSP that lets a form post user data off-site, with a clean console.
2. The fallback trap — `/demo/index.php?csp=child-src` → `?csp=fallback-trap`.
   Most memorable: adding a directive silently kills a sibling's entries.
3. `connect-src` vs `strict-dynamic` — `/demo/advanced.php?csp=connect-src`.
   Proves the GTM argument.

Say out loud that the rest are linked from the deck for people to explore, so
nobody feels shortchanged.

---

## Content fixes

### 3. Reconcile the `strict-dynamic` slide with the deck's own advice

Slide 7.1 shows web.dev's recipe verbatim — `script-src`, `object-src`,
`base-uri`, no `default-src`. That contradicts "always set `default-src`" two
stacks earlier, and it now also differs from the demo, which does send a
`default-src`.

Add a line to the slide: this is the minimal XSS-focused recipe; add
`default-src` in production. Cross-reference the new `default-src` gotcha slide
(5.4).

### 4. Safari / Trusted Types contradiction

"Current limitations of CSP" (slide 8) says Safari "still trails Chromium on the
newest bits (e.g. Trusted Types)." The Trusted Types slide (7.3) says Safari 26
added it in 2025 and it's now cross-browser.

MDN lists Trusted Types as Baseline since February 2026, so 7.3 is correct.
Replace the example on slide 8 with something Safari genuinely still trails on,
or drop the parenthetical.

### 5. Add `sandbox` to the Directives list

Slide 5.0 lists 20 directives. `sandbox` isn't among them, but the new "except
when they don't" slide (5.7) cites it as one of the five that don't inherit from
`default-src`.

### 6. British Airways figure

Slide 4.1 says "~380,000 card payments skimmed." That is BA's original September
2018 disclosure. The ICO's investigation put it at roughly 500,000 customers.

Either attribute it ("BA's initial disclosure") or switch to the ICO figure. The
£183m → £20m (October 2020) numbers are correct as written.

---

## Structural

### 7. Drupal content is split across two places

Stack 5 ends with three Drupal slides — "CSP in Drupal", "default-src is not on
by default", "Report-only mode" — and then the deck drops Drupal for two whole
stacks before picking it up again at stack 10.

Move those three into the Drupal stack (10). That leaves stack 5 as a clean unit
about how directives behave, and puts all Drupal material in one place.

Report-only is the judgement call: it's a generic concept, not Drupal-specific.
If it should stay early, promote it to its own stack right after "An HTTP header
that…" rather than leaving it as the tail of the directives stack.

### 8. Relocate the "Source-list keywords" stack

Stack 9 sits after the strict-CSP section but is basic reference material. It
belongs alongside `'unsafe-inline'` / hashes / nonces in stack 6.

Its second slide (inline `style=` attribute) duplicates ground already covered by
"The hash gotcha" (6.3) and `'unsafe-hashes'` (6.4). Consider cutting it.

### 9. Thin closing takeaway

The abstract promises "right-size your security posture to what your site
actually needs." That is carried entirely by the "Don't feel too bad / PII? /
Finance?" slide — which is also the one with the dead links. Worth investing in
if you want a stronger close.

---

## Demo notes

### 10. `frame-ancestors` in a `<meta>` tag

Covered on the advanced demo page but not in the deck. Worth a spoken aside:
`frame-ancestors` is silently ignored in a `<meta>` tag, which matters because
meta-tag CSP is what a lot of "just paste this snippet" advice recommends.

### 11. `upgrade-insecure-requests` is largely obsolete

The advanced demo says so on the page. Chrome auto-upgrades mixed-content images,
audio and video regardless of CSP, and blocks mixed *active* content outright.
Verified: with no CSP at all, an `http://` image still requests over `https`.

Fits the existing "directives that didn't survive" slide (5.1) if you want it in
the deck.

---

## Done

- Fallback trap slides + `?csp=child-src` / `?csp=fallback-trap` demo pair.
- GTM split into three slides; removed the incorrect claim that
  `'strict-dynamic'` helps with GTM's beacons.
- "Your header is not your config" + `curl -I` + two-CSP-headers slides.
- Report-only caveat, caching and config-drift bullets.
- `default-src` gotcha slide (5.4) — absent `default-src` leaves unnamed
  directives entirely unrestricted.
- "Except when they don't" — the five directives that don't inherit.
- `object-src` corrected: not a Flash relic. `<object>`/`<embed>` execute script
  and `script-src` does not govern them. Verified in Chrome.
- Altering the policy in code: event subscriber, `fallbackAwareAppendIfEnabled()`,
  render-element `#attached`, theme hook.
- Advanced demo (`web/demo/advanced.php`) with 15 cases, plus
  `web/demo/frame-test.php` for clickjacking.
- Corrected the stale AddToAny claims on the basic demo page.
- Added `default-src` to the `strict-dynamic` demo case so it is comparable with
  the nonce case.

---

## Verified — no action needed

- All 7 images and 9 local assets present.
- All 26 demo links return 200.
- No console errors; no slide exceeds reveal's 700px content height.
- `script-src-attr` broadly supported since December 2022 (MDN Baseline).
- Trusted Types Baseline February 2026 — consistent with the Safari 26 / FF 148
  claim on slide 7.3.
- `report-to` Baseline March 2026 — consistent with the FF 149 claim on 12.1.
- Sending both `report-to` and `report-uri` is correct: browsers that support
  `report-to` ignore `report-uri`.
- The Stack Overflow and Stack Exchange links return 403 to automated checks.
  That is Cloudflare bot protection; they load fine in a real browser.
