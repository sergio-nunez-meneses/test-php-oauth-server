// init web application
ajax('login', display);

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

  getBy('id', 'contentContainer').innerHTML = response.html;
}
