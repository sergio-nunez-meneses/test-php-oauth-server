const buttons = document.getElementsByTagName('button');

if (document.cookie !== '' && document.cookie.startsWith('authentication_cookie')) {
  var cookie = document.cookie.split('=')[1];
  console.log(cookie);
}

function ajax(method, url, contentType, data) {
  let xhr = new XMLHttpRequest();
  xhr.open(method, url);
  xhr.setRequestHeader('Content-type', contentType);
  // xhr.withCredentials = true;
  xhr.send(data);

  // on successful response (this.readyState = 4 && this.status = 200)
  xhr.onload = response; // callback function
}

function response() {
  if (this.responseText.charAt(0) === '<') {
    error(this.response);
    return;
  }

  console.log(this.responseText);
}

function request(action, method, encodedCredentials) {
  if (action === 'request') {
    var url = '../../src/request_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
      // data = 'client_credentials=' + encodedCredentials; // variable not working yet
  } else if (action === 'validate') {
    var url = '../../src/validate_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else if (action === 'revoke') {
    var url = '../../src/revoke_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
  } else {
    error('Invalid request.');
    return;
  }

  var data = typeof encodedCredentials !== '' ? 'client_credentials=' + encodedCredentials : '';

  ajax(method, url, contentType, data);
}

function error(error) {
  console.log(error);
}

for (let button of buttons) {
  button.addEventListener('click', () => {
    var action = button.name,
      method = button.value;

    if (action === 'request') {
      var username = document.getElementsByName('username')[0].value;
      var password = document.getElementsByName('password')[0].value;
      var clientCredentials = username + ':' + password;
    }

    var encodedCredentials = typeof clientCredentials !== 'undefined' ? btoa(clientCredentials) : '';
    // console.log(atob(encodedCredentials));

    request(action, method, encodedCredentials);
  });
}
