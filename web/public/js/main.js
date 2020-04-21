$.get(location.origin+'/scripts/login_by_session_id.php', function(authDataEncoded) {
    let authData = JSON.parse(authDataEncoded);
    if (authData['success'] && authData['message'] == 'login success') {
        location.href = location.origin+'/'+language+'/personal';
    }
});

$(document).ready(function() {
    $('#inputFile').change(function() {
        let file = document.getElementById("inputFile").files[0];
        if (file !== undefined) {
            $('#fileName').html(file.name);
        }
    });
    $('.input-file').each(function() {
        var $input = $(this),
            $label = $input.next('.js-labelFile'),
            labelVal = $label.html();

        $input.on('change', function(element) {
            var fileName = '';
            if (element.target.value) fileName = element.target.value.split('\\').pop();
            fileName ? $label.addClass('has-file').find('.js-fileName').html(fileName) : $label.removeClass('has-file').html(labelVal);
        });
    });
    $('#inputRegPhone').mask('0 (000) 000-0000');
    $('#loginForm').submit(function(e) {
        $('#loginForm').find('.form-group').removeClass('has-error');
        $('#errorMessageWrongLoginPass').hide();
        let loginValue = $('#inputEmailOrLogin').val();
        let password   = $('#inputPassword').val();
        let email = null;
        let login = null;
        if (loginValue.indexOf('@') != -1){
            email = loginValue;
        } else {
            login = loginValue;
        }
        if (loginValue.length === 0 || password.length === 0) {
            $('#loginForm').find('.form-group').addClass('has-error');
            $('#errorMessageWrongLoginPass').show();
        }
        $.get(location.origin+'/scripts/login_by_pass.php',
           {
               password: password,
               login: login,
               email: email
           },
           function(authDataEncoded) {
               let authData = JSON.parse(authDataEncoded);
               if (authData['success'] && authData['message'] == 'login success') {
                   setCookie('session_id', authData['data']['sessionId']);
                   location.href = location.origin+'/'+language+'/personal';
               }
               else {
                   $('#loginForm').find('.form-group').addClass('has-error');
                   $('#errorMessageWrongLoginPass').show();
               }
           }
       );
       e.preventDefault();
    });
    $('#registerForm').submit(function(e) {
        $('.form-group').removeClass('has-error');
        $('.error').hide();
        if (validateRegistrationData()) {
            $.ajax({
                url: location.origin + '/scripts/register.php',
                data: new FormData($('#registerForm')[0]),
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'JSON',
                success: function (authData) {
                    if (authData['success'] && authData['message'] == 'user exist') {
                        showModalUserExist();
                    }
                    else if (authData['success'] && authData['message'] == 'register successfully') {
                        setCookie('session_id', authData['data']['sessionId']);
                        location.href = location.origin+'/'+language+'/personal';
                    } else if (!authData['success']) {
                        processRegisterFail(authData['message']);
                    } else {
                        showModalFatalError();
                    }
                }
            });
        }
        e.preventDefault();
    });
});

function showModalUserExist() {
    if (language = 'ru') {
        $('#modalLabel').html('Ошибка регистрации');
        $('#modalText').html('Такой пользователь уже существует, попробуйте выполнить вход');
    } else if (language = 'en') {
        $('#modalLabel').html('Unsuccessful registration');
        $('#modalText').html('This user already exist, please, try to login');
    }
    $('#modalWindow').modal('show');
}

function showModalFatalError() {
    if (language = 'ru') {
        $('#modalLabel').html('Unsuccessful registration');
        $('#modalText').html('Something going wrong... Please, refresh this page and try again');
    }
    $('#modalWindow').modal('show');
}

function processRegisterFail(message) {
    switch (message) {
        case 'password are not equal':
            $('#inputRegPassConfirm').parent().addClass('has-error');
            $('#errorMessageWrongPassConf').show();
            break;
        case 'field login is empty':
            $('#inputRegLogin').parent().addClass('has-error');
            $('#errorMessageEmptyLogin').show();
            break;
        case 'field email is empty':
            $('#inputRegEmail').parent().addClass('has-error');
            $('#errorMessageEmptyEmail').show();
            break;
        case 'field pass is empty':
            $('#inputRegPass').parent().addClass('has-error');
            $('#errorMessageEmptyPass').show();
            break;
        case 'field pass_confirm is empty':
            $('#inputRegPassConfirm').parent().addClass('has-error');
            $('#errorMessageEmptyPassConf').show();
            break;
        case 'field first_name is empty':
            $('#inputRegFirstName').parent().addClass('has-error');
            $('#errorMessageEmptyName').show();
            break;
        case 'field phone is empty':
            $('#inputRegPhone').parent().addClass('has-error');
            $('#errorMessageEmptyPhone').show();
            break;
        case 'phone bad value':
            $('#inputRegPhone').parent().addClass('has-error');
            $('#errorMessageWrongPhone').show();
            break;
        case 'email bad value':
            $('#inputRegEmail').parent().addClass('has-error');
            $('#errorMessageWrongEmail').show();
            break;
        case 'name bad value':
            $('#inputRegFirstName').parent().addClass('has-error');
            $('#errorMessageWrongName').show();
            break;
        case 'not allowed file extension':
            $('#inputFile').parent().addClass('has-error');
            $('#errorMessageWrongFileExt').show();
            break;
        case 'file not exist':
            $('#inputFile').parent().addClass('has-error');
            $('#errorMessageWrongFileNotLoaded').show();
            break;
        default:
            showModalFatalError();
            break;
    }
}

function validateRegistrationData() {
    let noErrors = true;

    let file = document.getElementById("inputFile").files[0];
    if (file !== undefined) {
        let fileSize = file === undefined ? 0 : file.size;
        let fileData = file.name.split('.');
        let fileExtension = fileData.pop();
        let allowedFileExtension = ['jpg', 'png', 'gif'];
        if (fileSize > 5000000) {
            $('#inputFile').parent().addClass('has-error');
            $('#errorMessageWrongFile').show();
            noErrors = false;
        }
        if (!allowedFileExtension.includes(fileExtension)) {
            $('#inputFile').parent().addClass('has-error');
            $('#errorMessageWrongFileExt').show();
            noErrors = false;
        }
    }
    let email = $('#inputRegEmail').val();
    if (email.length === 0){
        $('#inputRegEmail').parent().addClass('has-error');
        $('#errorMessageEmptyEmail').show();
        noErrors = false;
    }else if (email.match('^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+.[A-Za-z]{2,4}') === null){
        $('#inputRegEmail').parent().addClass('has-error');
        $('#errorMessageWrongEmail').show();
        noErrors = false;
    }

    let login = $('#inputRegLogin').val();
    if (login.length === 0){
        $('#inputRegLogin').parent().addClass('has-error');
        $('#errorMessageEmptyLogin').show();
        noErrors = false;
    }else if (login.match('^[A-Za-z0-9_]{3,20}') === null){
        $('#inputRegLogin').parent().addClass('has-error');
        $('#errorMessageWrongLogin').show();
        noErrors = false;
    }

    let pass = $('#inputRegPass').val();
    if (pass.length === 0){
        $('#inputRegPass').parent().addClass('has-error');
        $('#errorMessageEmptyPass').show();
        noErrors = false;
    }else if (pass.length < 6 || pass.length > 30){
        $('#inputRegPass').parent().addClass('has-error');
        $('#errorMessageWrongPass').show();
        noErrors = false;
    }

    let passConfirm = $('#inputRegPassConfirm').val();
    if (passConfirm.length === 0){
        $('#inputRegPassConfirm').parent().addClass('has-error');
        $('#errorMessageEmptyPassConf').show();
        noErrors = false;
    }else if (pass !== passConfirm){
        $('#inputRegPassConfirm').parent().addClass('has-error');
        $('#errorMessageWrongPassConf').show();
        noErrors = false;
    }

    let phoneMasked = $('#inputRegPhone').val();
    let phone = phoneMasked.replace(/[^\d]/g, '');
    if (phoneMasked.length === 0){
        $('#inputRegPhone').parent().addClass('has-error');
        $('#errorMessageEmptyPhone').show();
        noErrors = false;
    }else if (phone.length === 0){
        $('#inputRegPhone').parent().addClass('has-error');
        $('#errorMessageWrongPhone').show();
        noErrors = false;
    }

    let firstName = $('#inputRegFirstName').val();
    if (firstName.length === 0){
        $('#inputRegFirstName').parent().addClass('has-error');
        $('#errorMessageEmptyName').show();
        noErrors = false;
    }else if (firstName.match('^[A-Za-z\u0410-\u042F\u0430-\u044F]{2,30}') === null){
        $('#inputRegFirstName').parent().addClass('has-error');
        $('#errorMessageWrongName').show();
        noErrors = false;
    }

    let lastName = $('#inputRegLastName').val();
    if (lastName.length > 0 && lastName.match('^[A-Za-z\u0410-\u042F\u0430-\u044F]{2,30}') === null){
        $('#inputRegLastName').parent().addClass('has-error');
        $('#errorMessageWrongLastName').show();
        noErrors = false;
    }

    let secondName = $('#inputRegSecondName').val();
    if (secondName.length > 0 && secondName.match('^[A-Za-z\u0410-\u042F\u0430-\u044F]{2,30}') === null){
        $('#inputRegSecondName').parent().addClass('has-error');
        $('#errorMessageWrongSecondName').show();
        noErrors = false;
    }
    return noErrors;
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
