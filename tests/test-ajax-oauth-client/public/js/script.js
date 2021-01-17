const buttons = getBy('tag', 'button');
var columnsContainer = getBy('class', 'columns-container')[0];
console.log(buttons);

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

function ajax(data = null, callback) {
  var encodedData = 'query=' + data,
    xhr = new XMLHttpRequest();

  xhr.open('POST', 'ajax/query_router.php');
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.send(encodedData);
  xhr.onerror = error;
  xhr.onload = callback;
}

function display() {
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

  columnsContainer.innerHTML = response.html;
}

function error(errorMessage) {
  console.log(errorMessage);

  var statusContainer = getBy('class', 'status-container')[0];

  statusContainer.innerHTML = errorMessage;
}

// init web application
ajax('login', display);

// eventListeners
setTimeout(() => {
  for (let button of buttons) {
    console.log(button);

    button.addEventListener('click', () => {
      var action = button.name,
        method = button.value;

      if (action === 'request') {
        var username = getBy('name', 'username').value,
          password = getBy('name', 'password').value,
          clientCredentials = username + ':' + password;
      }

      var encodedCredentials = typeof clientCredentials !== 'undefined' ? btoa(clientCredentials) : '';

      alert(encodedCredentials);
    });
  }
}, 500);
