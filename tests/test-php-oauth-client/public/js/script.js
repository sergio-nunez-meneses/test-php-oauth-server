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

function request(buttonName) {
  if (buttonName === 'request') {
    var method = 'POST',
      url = '../../src/request_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
      data = ''; // data = 'client_credentials=' + inputs
  } else if (buttonName === 'validate') {
    var method = 'GET',
      url = '../../src/validate_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else if (buttonName === 'revoke') {
    var method = 'GET',
      url = '../../src/revoke_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else {
    error('Invalid request.');
    return;
  }

  ajax(method, url, contentType, data);
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
    request(button.name);
  });
}
