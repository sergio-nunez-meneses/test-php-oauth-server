<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sergio NUNEZ MENESES">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Authentication Token Request</title>
  </head>
  <body>
    <main>
      <?php if (isset($_COOKIE['authentication_cookie'])) {
        ?>
        <h1 class="display-5">Your authentication token</h1>
        <p class="lead font-weight-italic">
          <?php echo $_COOKIE['authentication_cookie']; ?>
        </p>
        <?php
      }
      ?>

      <div class="container w-25 my-5">
        <div class="d-flex flex-column justify-content-center">
          <button class="w-100 my-1 btn btn-lg bg-primary text-white" type="button" name="request" value="post">
            Request Authentication Token
          </button>
          <button class="w-100 my-1 btn btn-lg bg-success text-white" type="button" name="validate" value="get">
            Validate Authentication Token
          </button>
          <button class="w-100 my-1 btn btn-lg bg-danger text-white" type="button" name="revoke" value="get">
            Revoke Authentication Token
          </button>
        </div>
      </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="public/js/script.js"></script>
  </body>
</html>
