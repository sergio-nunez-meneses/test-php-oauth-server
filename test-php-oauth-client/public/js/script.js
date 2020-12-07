const buttons = document.getElementsByTagName('button');
console.log('cookie: ', document.cookie);

function request(buttonName) {
  let xhr = new XMLHttpRequest();

  if (buttonName === 'request') {
    var method = 'POST',
      url = '../../src/request_authentication_token.php',
      contentType = 'application/x-www-form-urlencoded';
      data = 'name=' + buttonName;
  }

  xhr.open(method, url);
  xhr.setRequestHeader('Content-type', contentType);
  xhr.send(typeof data !== 'undefined' ? data : '');
  // callback function
  xhr.onload = response; // done and successful response (this.readyState = 4 && this.status = 200)
}

function response() {
  if (this.responseText.charAt(0) === '<') {
    error(this.response);
    return;
  }

  console.log(this.getAllResponseHeaders());
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
