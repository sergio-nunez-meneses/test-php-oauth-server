# OAuth 2.0 Server in Pure PHP

### Description

Beta version of a authentication/authorization server developed in pure PHP for [JSON Web Token](https://tools.ietf.org/html/rfc7519) generation, encryption, decryption and validation, inspired by the [OAuth 2.0](https://tools.ietf.org/html/rfc6749) protocol for performing a [client credentials grant type](https://tools.ietf.org/html/rfc6749#section-4.4) variant. Built for pedagogical purposes only.

### Requirements

First, import the file ```aa_server.sql``` in phpMyAdmin, and then the file ```users.sql``` in the ```users``` table. Username is ```juan``` password is ```123456789```.

Inside the folder ```tools```, create the file ```sql.php``` and fill it with your database information:

```php
<?php
define('DB_NAME', 'aa_server');
define('DB_HOST', '');
define('DB_PORT', '');
define('DB_CHAR', 'utf8mb4');
define('DB_USER', '');
define('DB_PWD', '');
define('PDO_OPTIONS', []);
```

Then, create the file ```constants.php``` and fill it with your virtualhost's url:
```php
<?php
define('ISSUER', 'http://virtualhost.name');
```

### Test

The protocol flow is simulated in the file ```user_requires_token.php``` for now. To run it, change directory to the folder ```test``` and paste the following line in the terminal:

```
php user_requests_token.php juan 123456789 http://virtualhost.name/request_token
```

A response like the following should be displayed:
```
Request started at 09:46:05
* Expire in 0 ms for 6 (transfer 0x1b2b7c64210)
* Expire in 1 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 1 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 1 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 2 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 1 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 1 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 5 ms for 1 (transfer 0x1b2b7c64210)
*   Trying ::1...
* TCP_NODELAY set
* Expire in 149989 ms for 3 (transfer 0x1b2b7c64210)
* Expire in 200 ms for 4 (transfer 0x1b2b7c64210)
* Connected to virtualhost.name (::1) port 80 (#0)
> POST /request_token HTTP/1.1
Host: virtualhost.name
Accept: */*
Content-Type: application/x-www-form-urlencoded
Authorization: Basic c2VyZ2lvOjEyMzQ1Njc4OQ==
Content-Length: 29

* upload completely sent off: 29 out of 29 bytes
< HTTP/1.1 200 OK
< Date: Thu, 03 Dec 2020 08:46:05 GMT
< Server: Apache/2.4.41 (Win64) PHP/7.3.12
< X-Powered-By: PHP/7.3.12
< Access-Control-Allow-Origin: *
< Access-Control-Allow-Methods: OPTIONS, GET, POST
< Access-Control-Max-Age: 3600
< Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With
< Content-Length: 1419
< Content-Type: text/html; charset=UTF-8
<
* Connection #0 to host virtualhost.name left intact

Your token has been generated:

WD72cHZ8GvfbxOcfghVz4kVKz73LYnY/2aiquLbyorFq7hOEJtFiUuNITyiJG/j0xPBzDUSGuJsyTqYdYY+hUTAKkbfoCpxDlrTTZ7uAnzwTS3FoFtfgcYL7KKjRkPeELFS4CArHOAwepopPJfRCyDGGsAMWUsWAASEIdQFQHyhdLMMJaaNUCHVq8X+iR9qZrEC/Bf/5HoAp/eeu01EhUNRg6od7vxD+3ZG+dizGMKd4SUVOCbBYebq8sdmrw/rbl9duSbQLoPlF3qnqEJnRhMcA+45x/ulFiluEc4MCKtU40alC+c9MEZl7GC5cQX6mlAmu5ZKSavZacFCSGsUdfVI+TGIwON1ht8FE5BrOkK9DjK6YXLDmVLvAgsjPPNtGMjQhtlEIaT8qBr91SweCojc0g1kB4rwI9lkbeHjyDzLGtFY6TTQYDyQ/vKlGX9g/lPoIPZH81ABHVKRKRcRBpgJBK0G3oZV9c4R4SdSg9aHcGeOJvSwPUD4Uo9izAoCAdDkgI5+b0WJDtRPnOMYBOU+U08HO0YCwbmqD8YR0+e8dq+eTZKFBQvf4AcApxOVYCxwcHhFMQ9ebcnsdCfR8suWPSVjyboOsch3xYTf5bEt3XWgkcdBdyssPFu6nbsqnnOJEfg0BR8szPX7Q/LrBaJEc+K3A2eIupJk1LB/UnNwq+ljk0Pif7H5gC+1N6PSJ6+ilXT10gekUdFNmeQqSqRkxw80FO5gXhTJ9/j0U7pHVJMOHHI04xkci720zz/Z09IvujloTt9EAyJPQyrc/BNXyL+08AvBdwJo2FPYc5b2Jb6MMuI+0dY2i+1nXeuty7BZA3vpQ/kHRnZuBXr7GFdJstbitcE90qNsWltfA+dLOEjRqTwHKh4gBy8TTygyJhxCdHUUTcANhqKDpIiTSxczsfXTksZPR+0ZHs0DBNZZ9Doq05uxyf5QK2vuyB2Yb4pTWRhSDB9RcWiYChssXRsN/eF1IBLHryvCdK0103Hs6136aV201+FLZKXHb/UXwE2oLenwIPEVdsuF7nEmdDa7gMI/WRL4xPWtMBoGB4jFGGqhhB4bzMOXnetmvULp8WH9KkVZsoNl/USrCEgaMWvP/8Qu/PsOtBL6h6CPq/BvujKekcR7ZIFTZHQjt9li7ApLGEJ9L1p7YV/mM7TtZutunJ5K913fHoYVC89lQZ7r0VYkvi4oG4Oe7dVItmrq5jQbAn3Azuw5EjM9NhZpLdohOy13R1GnVQjrO6gZdaTayOmexucYvyYfuk4cw2cQLjXITM3wrhhwWDZsYwud3T699TnyCnT6e/WM+4ccgDdZ7dFptIP0pvgQRDXFMO7L0k+Z94qE8HWluVmOoPUdpMQ==

* Expire in 0 ms for 6 (transfer 0x1b2b7c64210)
* Expire in 1 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
* Expire in 0 ms for 1 (transfer 0x1b2b7c64210)
*   Trying ::1...
* TCP_NODELAY set
* Expire in 149999 ms for 3 (transfer 0x1b2b7c64210)
* Expire in 200 ms for 4 (transfer 0x1b2b7c64210)
* Connected to virtualhost.name (::1) port 80 (#0)
> GET /access_token HTTP/1.1
Host: virtualhost.name
Accept: */*
Content-Type: application/json
Authorization: Bearer WD72cHZ8GvfbxOcfghVz4kVKz73LYnY/2aiquLbyorFq7hOEJtFiUuNITyiJG/j0xPBzDUSGuJsyTqYdYY+hUTAKkbfoCpxDlrTTZ7uAnzwTS3FoFtfgcYL7KKjRkPeELFS4CArHOAwepopPJfRCyDGGsAMWUsWAASEIdQFQHyhdLMMJaaNUCHVq8X+iR9qZrEC/Bf/5HoAp/eeu01EhUNRg6od7vxD+3ZG+dizGMKd4SUVOCbBYebq8sdmrw/rbl9duSbQLoPlF3qnqEJnRhMcA+45x/ulFiluEc4MCKtU40alC+c9MEZl7GC5cQX6mlAmu5ZKSavZacFCSGsUdfVI+TGIwON1ht8FE5BrOkK9DjK6YXLDmVLvAgsjPPNtGMjQhtlEIaT8qBr91SweCojc0g1kB4rwI9lkbeHjyDzLGtFY6TTQYDyQ/vKlGX9g/lPoIPZH81ABHVKRKRcRBpgJBK0G3oZV9c4R4SdSg9aHcGeOJvSwPUD4Uo9izAoCAdDkgI5+b0WJDtRPnOMYBOU+U08HO0YCwbmqD8YR0+e8dq+eTZKFBQvf4AcApxOVYCxwcHhFMQ9ebcnsdCfR8suWPSVjyboOsch3xYTf5bEt3XWgkcdBdyssPFu6nbsqnnOJEfg0BR8szPX7Q/LrBaJEc+K3A2eIupJk1LB/UnNwq+ljk0Pif7H5gC+1N6PSJ6+ilXT10gekUdFNmeQqSqRkxw80FO5gXhTJ9/j0U7pHVJMOHHI04xkci720zz/Z09IvujloTt9EAyJPQyrc/BNXyL+08AvBdwJo2FPYc5b2Jb6MMuI+0dY2i+1nXeuty7BZA3vpQ/kHRnZuBXr7GFdJstbitcE90qNsWltfA+dLOEjRqTwHKh4gBy8TTygyJhxCdHUUTcANhqKDpIiTSxczsfXTksZPR+0ZHs0DBNZZ9Doq05uxyf5QK2vuyB2Yb4pTWRhSDB9RcWiYChssXRsN/eF1IBLHryvCdK0103Hs6136aV201+FLZKXHb/UXwE2oLenwIPEVdsuF7nEmdDa7gMI/WRL4xPWtMBoGB4jFGGqhhB4bzMOXnetmvULp8WH9KkVZsoNl/USrCEgaMWvP/8Qu/PsOtBL6h6CPq/BvujKekcR7ZIFTZHQjt9li7ApLGEJ9L1p7YV/mM7TtZutunJ5K913fHoYVC89lQZ7r0VYkvi4oG4Oe7dVItmrq5jQbAn3Azuw5EjM9NhZpLdohOy13R1GnVQjrO6gZdaTayOmexucYvyYfuk4cw2cQLjXITM3wrhhwWDZsYwud3T699TnyCnT6e/WM+4ccgDdZ7dFptIP0pvgQRDXFMO7L0k+Z94qE8HWluVmOoPUdpMQ==

< HTTP/1.1 200 OK
< Date: Thu, 03 Dec 2020 08:46:06 GMT
< Server: Apache/2.4.41 (Win64) PHP/7.3.12
< X-Powered-By: PHP/7.3.12
< Access-Control-Allow-Origin: *
< Access-Control-Allow-Methods: OPTIONS, GET, POST
< Access-Control-Max-Age: 3600
< Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With
< Content-Length: 1368
< Content-Type: text/html; charset=UTF-8
<
* Connection #0 to host virtualhost.name left intact
string(1368) "V3B0/pv4Rf2BiVpzGK+5YWEHuuwG2/804z3iqn9WJtIFvjQGP4CWg0vDD0FjUYp2wF/z5tRftI8PRpG8w8WFfIimcqiu4UMkWFIsEXSMi5nXomGEb0rhf3droCI/2NJSbLJDfRbo/Sfp33fJO6JQfe9EF9ao17vzYhN1QazGwURG7MHiqpVCXr9m8SrilPUjB53dzpm+EpSJF1ThK8GvDpFFH4oYH0z2EbzOriOW9T90P9aGKF0qdSgILQrOMQRktI/CWpIU3dezIQovL8PFsifJe4Q5HtMU/dWOQSZNh7A3mKctz7dwx61xwOsqQnGKzJmXe1saFFg7ozdoZ4wcWn5azPsd3IDUBs2bWMAb1EXkEvyiOXGlvGimsq3QWZKHFhlFHyHPnwcxxnKk5l8IMRbnXImb6FelAjt7ekaJmkV3gzgaiR6cZq0Rmp298IZux13SsWvFwBy3LpGeWLREi+QAQvc/910xA/QHaQC0zSVUAJaXxsS1xbfpZYdOqrceutPF9o5VdXEIlDkC+7Oj7VHFPQg0zjdPNc+C1XcjwTBfcOeNXCGLZvNX0i6GpQsurJluXXCGjFWUQnU2/zawmLJCATkHOhjEUPAC+O4LqJpZsn14GqHV3kux2S4wI8RGppc1RrZ2jaPR3fW9FXNkQY4UoVL7Ym7D/5p09Ci/OQot7FE76RawBYf1fXBPp1fyyn3muXnJRIcEJGRb0qX7tlc4UYaBdKZT2ueLuTOgPnvKudV8tKZ+TkyzkX+EZR2fqfV9NuEeMxRWe6r9GNs8y5JZFmAiCY3D5moZtdWB/lTFY+aYl1y07jSYHJFiDoNQzV+nEhTLveyFndZcQxJT0MiNQW0LWKUe/jB2T8uWqQ71SHoPXAWPYwfLCmlb3zOl605VfSH7vptKJyo+3u2JQ6pAbLgrR7vwNhJgk8xn7VoA4atVD/7dNA29n94XrVrsjKDWOISecbwAvgJgqlCNMJ7r2NuEVPVoeBQOn0ODq3/IOJ3TY0lKk44lKYp+hPQ3LU2maO1pHLkLi6KOgFHVasoI5aOY0AwJaPLKovq4VTDbHwKfwgNUdHgEBIG8wuT/X2f1kZP912NA8f2MDvRKpywCLRkFRMCXEpMc24UV9o0lQbBVD1cqURc+NImN3c8b7Xp1vHHXgr8lffE13cn9CxccZIwyrbiC523ShZoWZaCLnNQ1u/wDqo5duR3UtLYENnU3tRGm7iLnU+x0wKHcDvvWR6TGI6II0O5u1ny3FADXw+3XecBxwCzfrjvwfVDAiz3nchgbRSw1LKxW0Zg65XkaWoW9OvYVhW2fHi7c9u5gS6AihrYgq0tnAR1A3sQZz6OqEs8ER2GDQ0E/9xB82A=="

Welcome, Juan.
Your authentication token has been validated, you can now access our services.

Redirecting to http://services.local/service

Request ended at 09:46:06
```

### TODO:

- Update database and ```JWTModel```.
- Improve error handling and remove redundant conditions in ```JWTController```.
- Optimize ```CurlController``` for better request/response handling.
- Improve URL's.
- Remove ```views``` and ```templates``` folders.
