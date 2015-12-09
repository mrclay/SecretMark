<?php

// ugh I need to set up SSL
$url = 'http://www.mrclay.org/secret-mark/make-page/';
$ssl_url = 'https://bedford.accountservergroup.com/~mrclayor/secret-mark/make-page/';
if (false !== strpos($_SERVER['HTTP_HOST'], 'mrclay.org')) {
	header("Location: $ssl_url");
	exit;
}

$sjcl_path = __DIR__ . '/../js/sjcl.js';
$last_mod = max(filemtime(__FILE__), filemtime($sjcl_path));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $last_mod));

?>
<!doctype html>
<html lang="en">
<!-- Full source: https://github.com/mrclay/SecretMark/blob/master/make-page/index.php -->
<head>
	<meta charset="utf-8">
	<title>Make an encrypted HTML page</title>
	<link rel="stylesheet"
		  href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
		  integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7"
		  crossorigin="anonymous">
</head>
<body style="margin:2em">
<h1>Make an encrypted HTML page</h1>
<section>
	<p>This creates a standalone HTML page that can be decrypted by password completely offline.</p>
	<p>This tool is also completely client-side. Save and use it locally if you prefer.</p>
	<form style="margin-bottom:2em">
		<div class="form-group">
			<label for="file_base">Filename</label>
			<div class="form-inline">
				<input class="form-control"
					   type="text"
					   id="file_base"
					   value="my-secret"
					   style="width:10em"
					   onfocus="this.select()"> .html
			</div>
		</div>
		<div class="form-group">
			<label for="msg">Content</label>
			<textarea class="form-control"
					  rows="10"
					  id="msg"
					  style="font-family:monospace"
					  autofocus></textarea>
		</div>
		<div class="form-group">
			<label for="pwd1">Password (repeat) <small style="font-style:italic">Use a strong one!</small></label>
			<input type="password" class="form-control" id="pwd1" required autocomplete="off">
			<input type="password" class="form-control" id="pwd2" required autocomplete="off">
		</div>
		<button type="submit" class="btn btn-primary">Create encrypted page</button>
	</form>
</section>
<footer>
	<p>By <a href="http://www.mrclay.org/">Steve Clay</a> (my <a href="http://amzn.com/w/37D5BK0ET30ZE">wish list</a>).
		Uses <a href="http://bitwiseshiftleft.github.io/sjcl/">SJCL 1.0.3</a> (256-bit AES, PBKDF2 key generation
		with 100K rounds)</p>
	<p>Why? HTML works everywhere and forever: ultimate storage container.</p>
</footer>

<script id="tpl_sjcl">
	<?php readfile($sjcl_path); ?>
</script>

<script type="text/template" id="tpl_result">
	<div class="alert alert-success" role="alert"><p>Your encrypted page is ready!</p></div>
	<ul>
		<li>Download: <a id="down"></a></li>
		<li>Bookmark: <a id="book"></a></li>
	</ul>
	<h3>See it in action</h3>
	<iframe style="height:20em;width:100%"></iframe>
</script>

<script type="text/template" id="tpl_script">
{{{SJCL}}}
document.querySelector('input').addEventListener('keyup', function (e) {
	if (e.keyCode == 13) {
		var cipher = {{{DATA}}};
		var plain = sjcl.decrypt(e.target.value, cipher);
		document.querySelector('section').innerHTML = "<textarea readonly rows='10' " +
			"style='font-family:monospace;width:95%;padding:1em'></textarea>";
		var ta = document.querySelector('textarea');
		ta.value = plain;
		ta.select();
	}
});
</script>

<script>
document.querySelector('form').addEventListener('submit', function (e) {
	e.preventDefault();

	var pwd = document.querySelector('#pwd1').value,
		pwd2 = document.querySelector('#pwd2').value;
	if (!pwd || (pwd !== pwd2)) {
		alert("Password empty or didn't match");
		return;
	}

	var json = sjcl.encrypt(pwd, document.querySelector('#msg').value, {
		iter: 100000,
		ks: 256
	});

	if (window.console) {
		console.log('sjcl.encrypt() output', JSON.parse(json));
	}

	var basename = document.querySelector('#file_base').value;

	var html = '<!doctype html><body style="margin:3em"><section><input type="password" autofocus placeholder="Password">' +
		'</section><footer><p>Created with <a href="<?= $url ?>"><?= $url ?></a>.</footer><script>{{{SCRIPT}}}<\/script></body>';

	html = html.replace('{{{SCRIPT}}}', document.querySelector('#tpl_script').innerHTML)
		.replace('{{{DATA}}}', JSON.stringify(json))
		.replace('{{{SJCL}}}', document.querySelector('#tpl_sjcl').innerHTML);

	var href = 'data:text/html;base64,' + sjcl.codec.base64.fromBits(sjcl.codec.utf8String.toBits(html));

	// replace the page!
	document.querySelector('section').innerHTML = document.querySelector('#tpl_result').innerHTML;

	var a = document.querySelector('#down');
	a.textContent = basename + '.html';
	a.href = href;
	a.download = basename + ".html";

	a = document.querySelector('#book');
	a.textContent = basename;
	a.href = href;

	var iframe = document.querySelector('iframe');
	iframe.contentWindow.document.open();
	iframe.contentWindow.document.write(html);
	iframe.contentWindow.document.close();
});
</script>
</body>
</html>
