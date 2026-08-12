<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>CSP Demo</title>
  <meta name="description" content="A demonstration of content security policy.">
  <meta name="author" content="John Brandenburg">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/custom.css">


  <style <?php
  if ($nonce) {
    echo $nonce_attribute;
  }
  ?>>
    .div-image-inline {
      width:416px;
      height:312px;
      background-image:url('images/moustache-cakes.jpeg')
    }
  </style>

  <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">

</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->



  <div class="container">


    <nav class="navbar">
      <div class="container">
        <ul class="navbar-list">
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=none">No CSP</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=report-only" >Report Only</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=enforced">Enforced</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=unsafe-inline">unsafe-inline</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=hash">Hashes</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=nonce">Nonces</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=report-to">report-to</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=child-src">child-src</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=fallback-trap">fallback trap</a>
          </li>
          <!--
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=complete">Complete</a>
          </li>
          -->
          <li class="navbar-item">
            <a class="navbar-link" href="/demo/advanced.php?csp=none">Advanced &rarr;</a>
          </li>
          <li class="navbar-item">
            <a class="navbar-link" href="/">Back to Drupal</a>
          </li>

        </ul>
      </div>
    </nav>


    <div class="row">
      <div class="column">
        <h1>Content Security Policy Demo</h1>
        <p>Click on the options above and see the behavior between different policies.</p>
      </div>
    </div>
  </div> <!-- End contaienr -->

  <br />


  <div class="container">

    <h3>Current Content Security Policy:</h3>
  <?php if ($csp) { ?>
    <p><code class="code-block"><?php echo htmlspecialchars($csp); ?></code></p>
  <?php } else { ?>
    <p>Not using any Content Security Policy</p>
    <?php } ?>
    <hr />

    <div class="row">
      <h3>Share Buttons (Add-to-Any)</h3>
      <!-- AddToAny BEGIN -->
      <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
        <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
        <a class="a2a_button_facebook"></a>
        <a class="a2a_button_twitter"></a>
        <a class="a2a_button_email"></a>
      </div>
      <script async <?php
      if ($nonce) {
        echo $nonce_attribute;
      }
      ?> src="https://static.addtoany.com/menu/page.js" ></script>
      <!-- AddToAny END -->
      <br />
      <p>Share buttons using AddToAny. These are loaded by JavaScript, and break
        unless the script's domain is allowed.</p>
      <p>They work under every policy here except <a href="?csp=enforced">Enforced</a>,
        which allow-lists no external hosts at all. The <a href="?csp=hash">hash</a>
        policy covers AddToAny's inline scripts with four SHA-256 hashes, which
        have to be regenerated whenever the vendor changes that code &mdash; the
        maintenance burden that pushes people toward nonces. The
        <a href="?csp=nonce">nonce policy</a> instead puts a nonce on the loader tag.</p>
      <p>AddToAny publishes its own CSP guidance at
        <a href="https://demo.addtoany.com/csp" target="_blank">demo.addtoany.com/csp</a>.</p>

    </div>
    <hr />


    <div class="row">
    <h2 class="fonttest">Fonts</h2>

    <p class="fonttest">This text should be using the "Audiowide" font from google fonts. Fonts downloaded from external
      sources need to be added to your CSP in the "font-src" group. You should probably be serving fonts locally anyway.</p>
</div>
    <hr />

    <div class="row">
      <h2>Inline Javascript </h2>
      <p>In the next few examples, we insert code via via <code>document.write();</code> </p>
      <span class="jsbox">
        <script>
          document.write('This is text inserted by javascript. It should be blocked by any CSP unless it is using a hash, or the "unsafe-inline" option');
        </script>
      </span>
      <p>In the red box, you should see the text: </p>
      <blockquote>"This is text inserted by javascript. It should be blocked by any CSP unless it is using a hash, or the "unsafe-inline" option"</blockquote>
    <p>This won't include either a nonce or a hash, so will not display the text if CSP is enforced.</p>
    </div>

    <hr />

    <div class="row">
      <h2>Inline Javascript with a nonce</h2>
      <span class="jsbox">
      <script <?php
      if ($nonce) {
        echo $nonce_attribute;
      }
      ?>>
        document.write('This is text inserted by javascript allowed by a nonce.')
      </script>
    </span>
      <p>This javascript will only be included if the CSP uses <code>unsafe-inline</code>,
        the hash, or the nonce set in the tag's attributes. Here we willuse the nonce if that is what you are viewing.</p>
    </div>

    <hr />

    <div class="row">
      <h2>Inline Javascript with a hash</h2>
      <span class="jsbox">
      <script>
        document.write('This is text inserted by javascript allowed by a hash.')
      </script>
    </span>

      <p>This js will be excluded unless the CSP allows <code>unsafe-inline</code>
        or explicitly includes the hash <code>'sha256-4J8+swjpXzJqezCClmAbHMHlahnf2WGWxdFHouce0EE='</code>.
        Since we do not define a nonce in the tag, the js will not run, even if it were included.</p>
    </div>

    <hr />



    <h2>Analytics and Google Tag Manager</h2>
    <p>Google recommends a per-response nonce for the GTM loader. Each tag can
      then require additional script, image, frame, and connection destinations.</p>
    <p>The released Drupal Google Tag module does not yet add all of those CSP
      requirements automatically. Until it does, manually add every destination
      used by your GTM container to the appropriate directive.</p>
    <p><a href="https://developers.google.com/tag-platform/security/guides/csp" target="_blank" rel="noopener">Google's current CSP guidance</a>
      &middot; <a href="https://www.drupal.org/project/google_tag/issues/3203811" target="_blank" rel="noopener">Drupal issue #3203811: test the nonce patch</a></p>

    <hr />

    <h2>Any js-based tools</h2>
    <ul>
      <li>ReCaptcha</li>
      <li>Maps</li>
    </ul>
    <hr />

    <div class="row">
      <div class="one-half column">
        <h2>Images</h2>
        <p>This is a plain image using an img tag.</p>
      </div>
      <div class="one-half column">
        <div>
          <img src="images/moustache-cakes.jpeg" width="416" height="312" alt="This is alt text" title="This is title text"/>
        </div>

      </div>
    </div>


    <div class="row">
      <div class="one-half column">
        <h2>External Images</h2>
        <p>This plain img element is loaded from an external domain ("picsum.photos"). Under an enforced CSP it is blocked unless that domain is in <code>img-src</code> (or <code>default-src</code>).</p>
      </div>
      <div class="one-half column">
        <div>
          <img src="https://picsum.photos/416/312" width="416" height="312" alt="Random external demo image" title="Loaded from picsum.photos"/>
        </div>

      </div>
    </div>

    <hr />


    <div class="row">
      <div class="column">
        <h2>Images set as div backgrounds</h2>
      </div>
    </div>

  <div class="row">
    <div class="one-half column">
      <p>
        An image set as the <code>background-image</code> of a div via an inline
        <code>style</code> attribute. A plain hash or nonce does <strong>not</strong>
        cover style <em>attributes</em> — so under an enforced CSP this is blocked
        unless you allow <code>'unsafe-inline'</code>, or pair a matching hash with
        <code>'unsafe-hashes'</code> (CSP Level 3). A nonce cannot apply to an
        attribute. Blocking by default is the correct, spec'd behavior.
      </p>
      <p>The robust fix is to move the rule into a stylesheet or a nonce'd
        <code>&lt;style&gt;</code> element instead of an inline attribute.</p>
    </div>
    <div class="one-half column">
      <div <?php
      if ($nonce) {
        echo $nonce_attribute;
      }
      ?> style="width:416px;height:312px;background-image:url('images/moustache-cakes.jpeg')">
        <p class="floating">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae nibh nec sapien consectetur varius. Etiam molestie felis et turpis commodo, in accumsan ante pharetra. Sed feugiat faucibus elementum. Vestibulum fringilla ullamcorper enim at volutpat. In blandit elementum nunc eget tempor.
        </p>
      </div>
    </div>
  </div>
    <hr />


    <div class="row">
      <div class="one-half column">
        <p>
          This image was inserted by a style element embedded in the HTML
          document. It will require a hash or a nonce to display the image.
          We will use the nonce.
        </p>
      </div>
      <div class="one-half column">
        <div class="div-image-inline">
          <p class="floating">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae nibh nec sapien consectetur varius. Etiam molestie felis et turpis commodo, in accumsan ante pharetra. Sed feugiat faucibus elementum. Vestibulum fringilla ullamcorper enim at volutpat. In blandit elementum nunc eget tempor.
          </p>
        </div>
      </div>
    </div>

    <hr />

    <div class="row">
      <div class="one-half column">
        <p>
          This image was inserted by an external style sheet.
        </p>
        <p>Note, if you are using an unaliased CDN, e.g. abcdefg.cloudfront.net, you would need to add this to your CSP.</p>

      </div>
       <div class="one-half column">
          <div class="div-image-external">
            <p class="floating">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae nibh nec sapien consectetur varius. Etiam molestie felis et turpis commodo, in accumsan ante pharetra. Sed feugiat faucibus elementum. Vestibulum fringilla ullamcorper enim at volutpat. In blandit elementum nunc eget tempor.
            </p>
          </div>
        </div>


    </div>



<hr />



    <div class="row">
      <div class="column">
        <h2>Embedded Media</h2>
      </div>
    </div>

<?php if ($csp_option === 'child-src' || $csp_option === 'fallback-trap') { ?>
    <div class="row">
      <div class="column">
        <h3>The fallback trap</h3>
        <p>Both of these policies list <code>www.youtube.com</code> and
          <code>w.soundcloud.com</code> in <code>child-src</code>. The only
          difference is that the trap version also defines
          <code>frame-src 'self'</code>, which omits them.</p>
        <p>Because <code>frame-src</code> is present, the browser never consults
          <code>child-src</code> for frames. Those two entries are dead and the
          embeds below are blocked &mdash; and nothing in the console tells you
          that the <code>child-src</code> entries stopped applying.</p>
        <p>Compare: <a href="?csp=child-src">child-src only</a> (embeds load)
          vs. <a href="?csp=fallback-trap">child-src + frame-src</a> (embeds
          blocked).</p>
      </div>
    </div>
<?php } ?>

    <div class="row">
      <div class="one-half column">
        <p>YouTube uses an iframe.</p>
      </div>
      <div class="one-half column">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/FGBhQbmPwH8" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
    </div>

    <hr />

    <div class="row">
      <div class="one-half column">
        <p>Soundcloud also uses an iFrame to embed audio. But it also relies on
        inline, attribute styles. e.g. <code>style=""</code>, which do not work
          in Chrome without the <code>unsafe-inline</code> option.</p>
      </div>
      <div class="one-half column">
        <iframe width="100%" height="300" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/801624052&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe><div style="font-size: 10px; color: #cccccc;line-break: anywhere;word-break: normal;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; font-family: Interstate,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Garuda,Verdana,Tahoma,sans-serif;font-weight: 100;"><a href="https://soundcloud.com/user-989205552" title="Cornelius Link" target="_blank" style="color: #cccccc; text-decoration: none;">Cornelius Link</a> · <a href="https://soundcloud.com/user-989205552/astronomia-medieval-style-tavern-version" title="Astronomia (Medieval Style) [Tavern Version]" target="_blank" style="color: #cccccc; text-decoration: none;">Astronomia (Medieval Style) [Tavern Version]</a></div>
      </div>
    </div>

    <hr />


  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
</html>
