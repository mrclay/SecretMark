<?php

/**
 * @param bool $minifyAvailable is Minify installed at URI /min/ ?
 */
function showPage($minifyAvailable = false) {
    header('Content-Type: text/html; charset=utf-8');
    $path = ltrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    ?>
<!doctype html>
<head>
    <meta charset="utf-8">
    <title>SecretMark | mrclay.org</title>

<?php if ($minifyAvailable): ?>
    <link href="/min/b=<?php echo $path ?>/css&amp;f=bootstrap.min.css,app.css" rel="stylesheet">
<?php else: ?>
    <link rel="stylesheet" href="//twitter.github.com/bootstrap/1.3.0/bootstrap.min.css">
    <link href="css/app.css" rel="stylesheet">
<?php endif; ?>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="span4">
            <h1><a href="./">SecretMark</a></h1>
        </div>
        <div class="span12">
            <p>Store short, password-protected secrets in your bookmarks.<br>SecretMark is 100% Javascript &amp; your secrets/passwords never leave your browser.</p>
        </div>
    </div>
    <p id="loading">is loading...</p>
    <p id="msg" class="alert-message"></p>
    <div id="m1" class="page">
        <h2>Encrypt your secret</h2>
        <p>Note: you can encrypt as much as you want, but if you want to store this in a bookmark, keep it short.</p>
        <form id="plainEntry" class="form-stacked">
            <div class="clearfix">
                <label for="shortName">Bookmark label (optional)</label>
                <input id="shortName" data-default="my secret" autocomplete="off">
                e.g. "bank login creds"
            </div>
            <div class="clearfix">
                <label for="plaintext">Secret</label>
                <textarea id="plaintext" class="xxlarge span11"></textarea>
            </div>
            <div class="row clearfix">
                <div class="span4">
                    <label for="pwd1">Password</label>
                    <input type="password" id="pwd1" autocomplete="off">
                </div>
                <div class="span10">
                    <label for="pwd2">Repeat password</label>
                    <input type="password" id="pwd2" autocomplete="off">
                </div>
            </div>
            <div class="clearfix">
                <div class="input">
                    <label><input type="checkbox" checked id="reqMinLength"> require at least 8 chars in password (recommended)</label>
                </div>
            </div>
            <div class="actions">
                <input class="btn primary" type="submit" value="Encrypt">
            </div>
        </form>
        <hr>
        <h2>Decrypt a secret</h2>
        <form id="cipherEntry" class="form-stacked">
            <div class="clearfix">
                <label for="newCipher">Encrypted secret</label>
                <input class="xlarge" id="newCipher">
            </div>
            <div class="actions">
                <input type="submit" class="btn primary" value="Continue...">
            </div>
        </form>
    </div>
    <div id="m2" class="page">
        <h2>Decrypt this secret</h2>
        <form id="passEntry" class="form-stacked">
            <div class="clearfix">
                <label for="pwd">Password</label>
                <input type="password" id="pwd" autocomplete="off">
            </div>
            <div class="actions">
                <input type="submit" class="btn primary" value="Decrypt">
            </div>
        </form>
    </div>
    <div id="m3" class="page">
        <h2>Your secret</h2>
        <textarea id="decrypted" readonly class="xxlarge span11"></textarea>
        <p><small>Note: This was encrypted <span id="dateSpan"></span></small></p>
    </div>
    <div id="m4" class="page">
        <h2>Store your secret</h2>
        <p>Your secret is encrypted, but you must <strong>store it</strong>.</p>
        <h4>Option 1: Bookmark this page</h4>
        <p>Use your favorite social bookmark service if you want...</p>
        <h4>Option 2: Save this link, or drag it to your bookmarks or desktop</h4>
        <div class="actions">
            <a id="bookmark" class="btn primary" target="_blank">My Secret</a>
        </div>
        <h4>Option 3: Create an Internet Shortcut file</h4>
        <p><a id="dataUri" class="btn" href="#">Save as "<b id="shortFilename"></b>"</a> &larr; Right-click and Save Link as...</p>
        <h4>Option 4: Copy the encrypted secret into a text file</h4>
        <div class="clearfix">
            <input class="span11" id="cipherText" readonly>
        </div>
    </div>
</div>
<div id="footer">
    <hr>
    <div class="container">
        <h3>About</h3>
        <p>Created by <a href="http://www.mrclay.org/">Steve Clay</a>, styled with <a href="http://twitter.github.com/bootstrap/">Bootstrap</a>, powered by jQuery and the
            <a href="http://bitwiseshiftleft.github.com/sjcl/">Stanford Javascript Crypto Library</a>. </p>
        <p>Encryption details: 256-bit AES CTR mode, key derived with PBKDF2 with 1000 iterations, IV/salt from an <a href="http://bitwiseshiftleft.github.com/sjcl/doc/symbols/sjcl.random.html">impressive entropy accumulator</a>.</p>
    </div>
</div>

<?php if ($minifyAvailable): ?>
    <script src="/min/b=<?php echo $path ?>/js&amp;f=jquery.min.js,jquery.ba-bbq.min.js,sjcl.js,MrClay/LocationHash.js,app.js"></script>
<?php else: ?>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
    <script src="js/jquery.ba-bbq.min.js"></script>
    <script src="js/sjcl.js"></script>
    <script src="js/app.js"></script>
<?php endif; ?>
</body>
</html>
<?php
}

if (! is_file($_SERVER['DOCUMENT_ROOT'] . '/min/lib/Minify.php')) {
    showPage();
    exit;
}

function getContent() {
    ob_start();
    showPage(true);
    return ob_get_clean();
}

set_include_path($_SERVER['DOCUMENT_ROOT'] . '/min/lib/' . PATH_SEPARATOR . get_include_path());
require 'Minify.php';
Minify::setCache(sys_get_temp_dir());
Minify::serve('Files', array(
    'files' => new Minify_Source(array(
        'id' => md5(__FILE__),
        'contentType' => Minify::TYPE_HTML,
        'getContentFunc' => 'getContent',
    ))
));
