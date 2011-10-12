$(function () {
    // crypto/encoding
    function b64_toUrl(b64) {
        return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.');
    }
    function b64_fromUrl(b64InUrl) {
        return b64InUrl.replace(/\-/g, '+').replace(/_/g, '/').replace(/\./g, '=');
    }
    function encrypt(pwd, plain) {
        plain = new Date().getTime().toString(36) + ',' + plain;
        var enc = JSON.parse(sjcl.encrypt(pwd, plain, {ks: 256}));
        return b64_toUrl(enc.salt + '|' + enc.iv + '|' + enc.ct);
    }
    function decrypt(pwd, cip) {
        try {
            var p = b64_fromUrl(cip).split('|'),
                cip = JSON.stringify({
                    ks: 256,
                    iv: p[1],
                    salt: p[0],
                    ct: p[2]
                }),
                plain = sjcl.decrypt(pwd, cip),
                firstComma = plain.indexOf(',');
            return {
                txt: plain.substr(firstComma + 1),
                date: new Date(parseInt(plain.substr(0, firstComma), 36))
            };
        } catch (e) {
            return false;
        }
    }

    // ui
    function showPage(i) {
        $('div.page').each(function () {
            $(this)[(this.id == ('m' + i)) ? 'show' : 'hide']();
        });
    }
    function setMsg(html, className) {
        className = 'alert-message ' + className;
        if (html) {
            $('#msg').html(html).show()[0].className = className;
        } else {
            $('#msg').hide();
        }
    }

    // actions
    function action1() {
        var home = location.href.replace(/#.*/, '');
        if (location.href !== home) {
            location.assign(home);
        }
        setMsg();
        $('title').text($('title').data('default'));
        $('#plaintext, #pwd1, #pwd2, #newCipher').val('');
        showPage(1);
        $('#shortName').val($('#shortName').data('default'))[0].select();
    }
    function action2() {
        $('#ciphertext').val($.deparam.fragment().c);
        showPage(2);
        $('#pwd').val('')[0].focus();
    }
    function action3(decryptedTxt, encryptDate) {
        $('#dateSpan').text(encryptDate.toString());
        setMsg('Secret unlocked!', 'success');
        showPage(3);
        $('#decrypted').text(decryptedTxt)[0].select();
    }
    function action4(ciphertext) {
        location.hash = '#' + $.param({c: ciphertext});
        var url = location.href,
            shortName = $.trim($('#shortName').val() || $('#shortName').data('default')),
            title = shortName + ' (SecretMark)';
        $('#shortFilename').text(shortName.replace(/\s+/g, '-') + '.url');
        $('#bookmark').text(title)[0].href = url;
        $('title').text(title);
        var data = "[InternetShortcut]\nURL=" + url + "\n";
        data = sjcl.codec.base64.fromBits(sjcl.codec.utf8String.toBits(data));
        $('#dataUri')[0].href = 'data:application/octet-stream;base64,' + data;
        showPage(4);
        $('#cipherText').val(ciphertext)[0].select();
    }

    // bind events

    $('#cipherText').click(function () {
        this.select();
    });

    $('#shortName').click(function () {
        if ($(this).val() == $(this).data('default')) {
            $(this).val('');
        }
    });
    
    $('#cipherEntry').submit(function (e) {
        e.preventDefault();
        var ciphertext = $('#newCipher').val().replace(/^.*#c=/, '').replace(/%7C/g, '|');
        $('#newCipher').val('');
        location.hash = '#' + $.param({c: ciphertext});
        action2();
    });

    $('#plainEntry').submit(function (e) {
        e.preventDefault();
        var pwd1 = $('#pwd1').val(),
            pwd2 = $('#pwd2').val(),
            plain = $('#plaintext').val();
        if (pwd1 === '') {
            setMsg('A password is required.', 'info');
            return false;
        }
        if ($('#reqMinLength')[0].checked && pwd1.length < 8) {
            setMsg('Your password must be at least 8 characters.', 'info');
            return false;
        }
        if (pwd1 !== pwd2) {
            setMsg('The given passwords don\'t match.', 'info');
            return false;
        }
        action4(encrypt(pwd1, plain));
    });

    $('#passEntry').submit(function (e) {
        e.preventDefault();
        var cip = $.deparam.fragment().c,
            pwd = $('#pwd').val(),
            decrypted = decrypt(pwd, cip);
        $('#pwd').val('');
        if (decrypted) {
            action3(decrypted.txt, decrypted.date);
        } else {
            setMsg('Either your password was wrong or the URL is corrupted.', 'info');
            $('#pwd')[0].select();
        }
    });

    $('h1 a').click(function () {
        var url = location.href.replace(/#.*/, '');
        location.assign(url);
    });

    // run page

    $('title').data('default', $('title').text());

    $('#loading').hide();
    if ($.deparam.fragment().c) {
        action2();
    } else {
        action1();
    }
    $('#footer').show();
});