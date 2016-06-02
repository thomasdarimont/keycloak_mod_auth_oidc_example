<html>
  <body>
    <h1>Hello, <?php echo($_SERVER['REMOTE_USER']) ?></h1>
    <pre><?php print_r(apache_request_headers()); ?></pre>
    <a href="../">back to index</a>
    <a href="/demo/redirect_uri?logout=<?php echo ("http" . (isset($_SERVER['HTTPS']) ? 's' : '') . "://". $_SERVER['HTTP_HOST'] . ":" . $_SERVER['SERVER_PORT'] ."/") ?>">Logout</a>
  </body>
</html>
