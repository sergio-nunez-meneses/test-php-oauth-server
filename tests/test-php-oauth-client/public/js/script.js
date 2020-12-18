const buttons = document.getElementsByTagName('button');

if (document.cookie !== '' && document.cookie.startsWith('authentication_cookie')) {
  var cookie = document.cookie.split('=')[1];
  console.log(cookie);
}

function ajax(method, url, contentType, data) {
  let xhr = new XMLHttpRequest();
  xhr.open(method, url);
  xhr.setRequestHeader('Content-type', contentType);
  xhr.send(typeof data !== 'undefined' ? data : '');
  xhr.onload = response; // callback function
}

function request(buttonName, buttonValue) {
  if (buttonName === 'request') {
    var url = '../../src/request_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
      data = ''; // data = 'client_credentials=' + inputs
  } else if (buttonName === 'validate') {
    var url = '../../src/validate_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else if (buttonName === 'redirect') {
    var url = '../../src/redirect_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else if (buttonName === 'revoke') {
    var url = '../../src/revoke_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else {
    error('Invalid request.');
    return;
  }

  ajax(buttonValue, url, contentType, data);
}

function response() {
  if (this.responseText.charAt(0) === '<') {
    error(this.response);
    return;
  }

  console.log(this.responseText);
}

function error(error) {
  console.log(error);
}

for (let button of buttons) {
  button.addEventListener('click', () => {
    request(button.name, button.value);
  });
}
