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
          <!--
          <li class="navbar-item">
            <a class="navbar-link" href="?csp=complete">Complete</a>
          </li>
          -->
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
    <p><code class="code-block"><?php echo $csp ?></code></p>
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
      <script <?php
      if ($nonce) {
        echo $nonce_attribute;
      }
      ?> async src="https://static.addtoany.com/menu/page.js"></script>
      <!-- AddToAny END -->
      <br />
      <p>Share buttons using AddToAny. These are loaded by javascript, and will
        break unless the javascript domain is allowed. They might degrade if not
        handled properly</p>

    </div>
    <hr />


    <div class="row">
    <h2 class="fonttest">Fonts</h2>

    <p class="fonttest">This text should be using the "Audiowide" font from google fonts. Fonts downloaded from external
      sources need to be added to your CSP in the "font-src" group.</p>
</div>
    <hr />

    <div class="row">
      <h2>Inline Javascript </h2>
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



    <h2>Analytics</h2>
    <p>Since analytics are generally included via inline scripts. They need a nonce or hash to allow them to meet the CSP.</p>
    <p><a href="https://developers.google.com/tag-manager/web/csp">Google tag manager Documentation</a>. the Drupal
      Google Tag Manager module gets aruond this be including the script in an external file. e.g.</p>
    <pre><code>
        &lt;script src="/sites/default/files/google_tag/primary/google_tag.script.js?qgt931" defer&gt;&lt;/script&gt;
    </code></pre>

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
        <p>This plain img elemeny was loaded from an external domain "pbs.twimg.com".</p>
      </div>
      <div class="one-half column">
        <div>
          <img <?php
          if ($nonce) {
            echo $nonce_attribute;
          }
          ?>src="https://pbs.twimg.com/media/EiYLR_YXsAATYo3?format=jpg&name=small" width="416" height="312" alt="This is alt text" title="This is title text"/>
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
        An image that is set as a backgound-image of
        a div by a style attribute. Hashes behave differently between Chrome
        and Firefox. This image will be blocked in Chrome unless it is
        allowed by including the "unsafe-inline" option. It will display in
        Firefox when using the hash in the CSP. A nonce cannot be used here.
        Blocking is actually the correct behavior according to the spec.
      </p>
      <p>Oddly, Chrome will still suggest an sha256 hash, even if it is already in use.</p>
      <p>This is what we do currently in Gesso.
      </p>
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
