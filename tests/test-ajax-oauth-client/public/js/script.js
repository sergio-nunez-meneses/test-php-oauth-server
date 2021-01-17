const buttons = getBy('tag', 'button');
var columnsContainer = getBy('class', 'columns-container')[0];

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

function ajax(method, data) {
  var xhr = new XMLHttpRequest();
  xhr.open(method, 'ajax/request_router.php');
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.send(data);
  xhr.onerror = error;
  xhr.onload = getResponse;
}

function request(action, method) {
  const actions = ['request', 'validate', 'redirect', 'revoke', 'login', 'services'],
    methods = ['GET', 'POST'];

  if (actions.indexOf(action) === -1) {
    alert('Invalid token request.');
    return;
  }

  if (methods.indexOf(method) === -1) {
    alert('Invalid HTTP method.');
    return;
  }

  if (action === 'request') {
    var username = getBy('name', 'username').value,
      password = getBy('name', 'password').value,
      clientCredentials = username + ':' + password,
      encodedCredentials = btoa(clientCredentials);
  }

  var data = 'request=' + action;

  if (typeof encodedCredentials !== 'undefined') {
    data += '&&client_credentials=' + encodedCredentials;
  }

  ajax(method, data);
}

function getResponse() {
  if (this.responseText.charAt(0) !== '{') {
    console.log('not JSON');
    return;
  }

  let response = JSON.parse(this.responseText);
  console.log(response);

  if (typeof response.response_type !== 'undefined' && response.response_type === 'error') {
    alert(response.response_value);
    return;
  }

  callback(response);
}

function callback(response) {
  if (response.type === 'authenticated') {
    request('validate', 'POST');
  } else if (response.type === 'validated') {
    request('services', 'POST');
  } else if (response.type === 'revoked') {
    request('login', 'POST');
  } else if (response.callback === 'display') {
    columnsContainer.innerHTML = response.html;
  }
}

function error(errorMessage) {
  console.log(errorMessage);

  var statusContainer = getBy('class', 'status-container')[0];
  statusContainer.innerHTML = errorMessage;
}

// init web application
request('login', 'POST');

// eventListeners
setTimeout(() => {
  for (let button of buttons) {
    button.addEventListener('click', () => {
      var action = button.name,
        method = button.value;

      request(action, method);
    });
  }
}, 500);
