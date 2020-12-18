const buttons = document.getElementsByTagName('button');

if (document.cookie !== '' && document.cookie.startsWith('authentication_cookie')) {
  var cookie = document.cookie.split('=')[1];
  console.log(cookie);
}

function ajax(method, url, contentType, data) {
  let xhr = new XMLHttpRequest();
  xhr.open(method, url);
  xhr.setRequestHeader('Content-type', contentType);
  xhr.send(data);
  xhr.onload = response; // callback function
}

function request(action, method) {
  const actions = ['request', 'validate', 'redirect', 'revoke'];

  if (actions.indexOf(action) === -1) {
    error('Invalid request.');
    return;
  }

  var url = '../../src/' + action + '_authentication_token.php',
    contentType = 'application/x-www-form-urlencoded',
    data = typeof encodedCredentials !== 'undefined' ? 'client_credentials=' + encodedCredentials : '';

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
    var action = button.name,
      method = button.value;

    request(action, method);
  });
}
