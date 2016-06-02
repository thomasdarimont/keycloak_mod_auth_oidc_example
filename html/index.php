<html>
  <body>
    <p>
        <a href="demo/index.php">Access mod_oidc protected page</a><br/>
        <a href="info.php">phpinfo</a>
    <p/>
    
    <pre><?php print_r(apache_request_headers()); ?></pre>
  </body>
</html>