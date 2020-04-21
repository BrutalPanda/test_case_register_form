$.get(location.origin+'/scripts/login_by_session_id.php', function(authDataEncoded) {
    let authData = JSON.parse(authDataEncoded);
    if (authData['success'] && authData['message'] == 'login success') {
        setCookie('session_id', authData['data']['sessionId']);
        $('#login').html(authData['data']['user']['login']);
        $('#email').html(authData['data']['user']['email']);
        $('#phone').html(authData['data']['user']['phone']);
        $('#firstName').html(authData['data']['user']['first_name']);
        $('#lastName').html(authData['data']['user']['last_name']);
        $('#secondName').html(authData['data']['user']['second_name']);
        if(authData['data']['user']['userpic_filename'] !== ''){
            $('.img-userpic').attr('src','../../files/'+authData['data']['user']['userpic_filename']);
        }
        $('.info-block').show();
    }
});

function logout(){
    $.get(location.origin+'/scripts/logout.php', function(){
        location.href = location.origin+'/'+language;
    });
}

function setCookie(name, value, options = {}) {

    options = {
        path: '/',
        ...options
    };

    if (options.expires instanceof Date) {
        options.expires = options.expires.toUTCString();
    }

    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

    for (let optionKey in options) {
        updatedCookie += "; " + optionKey;
        let optionValue = options[optionKey];
        if (optionValue !== true) {
            updatedCookie += "=" + optionValue;
        }
    }

    document.cookie = updatedCookie;
}