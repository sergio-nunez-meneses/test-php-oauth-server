const buttons = getBy('tag', 'button');

if (document.cookie !== '' && document.cookie.startsWith('authentication_cookie')) {
  var cookie = document.cookie.split('=')[1];
  console.log(cookie);
}

function getBy(attribute, value) {
  if (attribute === 'tag') {
    return document.getElementsByTagName(value);
  } else if (attribute === 'id') {
    return document.getElementById(value);
  } else if (attribute === 'name') {
    return document.getElementsByName(value)[0];
  } else if (attribute === 'class') {
    return document.getElementsByClassName(value);
  }
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
    alert('Invalid request.');
    return;
  }

  var url = '../../src/' + action + '_authentication_token.php',
    contentType = 'application/x-www-form-urlencoded',
    data = typeof encodedCredentials !== 'undefined' ? 'client_credentials=' + encodedCredentials : '';

  ajax(method, url, contentType, data);
}

function response() {
  if (this.responseText.charAt(0) === '<') {
    console.log(this.responseText);
    return;
  }

  if (this.responseText.charAt(0) === '{') {
    let response = JSON.parse(this.responseText);

    if (response.response_type === 'error') {
      alert(response.response_value);
      console.log(response.response_value);
      return;
    }
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
