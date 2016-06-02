# Keycloak Client with apache and mod_auth_oidc

This example project demonstrates how to configure mod_auth_openidc for use with Keycloak.
For simplicity reasons we use a plain http configuration instead of setting up https.

More information about the mod_auth_oidc configuration can be found here:
https://github.com/pingidentity/mod_auth_openidc

A more sophisticated configuration with https / TLS can be found here:
https://github.com/cyclone-project/cyclone-federation-provider-apache-oidc-demo

## Define the Keycloak client for the mod_auth_openidc client
 
Note that the docker host and the Keycloak instance is available via the IP: 172.17.0.1.
Keycloak runs on port 8081. 

The Docker container has the IP: 172.17.0.2

Setup a new client under the "Clients" section of your realm configuration.
- client_id: mod_oidc_example_client
- access_type: confidential
- Valid Redirect Urls
  - http://172.17.0.2/*
  - http://172.17.0.2:80/*
- Base Url
  - http://172.17.0.2/demo
- Web Origins
  - http://172.17.0.2/*
  - http://172.17.0.2:80/*
  
Copy the client secret from the credentials page, e.g.: 4a932456-6562-42fe-998c-32f7e69f29dc

Now you should create a user in your realm.

# mod_auth_openidc Apache module configuration

```
#LoadModule auth_openidc_module modules/mod_auth_openidc.so

ServerName ${HOSTIP}

<VirtualHost *:80>

    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    #this is required by mod_auth_openidc
    OIDCCryptoPassphrase currently-not-supported-by-keycloak

    OIDCProviderMetadataURL ${KEYCLOAK_ADDR}/auth/realms/${KEYCLOAK_REALM}/.well-known/openid-configuration
    
    OIDCClientID ${CLIENT_ID}
    OIDCClientSecret ${CLIENT_SECRET}
    OIDCRedirectURI http://${HOSTIP}/demo/redirect_uri
   
    # maps the prefered_username claim to the REMOTE_USER environment variable 
    OIDCRemoteUserClaim preferred_username

    <Location /demo/>
        AuthType openid-connect
        Require valid-user
    </Location>
</VirtualHost>
```

## Build the image
```
docker build -t keycloak-mod-oidc .
```

## Create the container
```
docker run \
       -it \
       --rm \
       -e HOSTIP=172.17.0.2 \
       -e KEYCLOAK_ADDR=http://172.17.0.1:8081 \
       -e KEYCLOAK_REALM=master \
       -e CLIENT_ID=mod_oidc_example_client \
       -e CLIENT_SECRET=4a932456-6562-42fe-998c-32f7e69f29dc \
       --name keycloak-mod-oidc-demo \
       keycloak-mod-oidc
```

## Browse to demo application
Open http://172.17.0.2/ and click on the link `Access mod_oidc protected page`.
Your browser should now redirect to the keycloak login page of the master realm.
After login you should see a page that greets you with your username and 
a list of headers provided by Keycloak.
Clicking on logout should log you out of your SESSION session and redirect you to the 
index page. 

An example for the provided headers can be seen below:
```
(
    [Host] => 172.17.0.2
    [Connection] => keep-alive
    [Accept] => text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
    [Upgrade-Insecure-Requests] => 1
    [User-Agent] => Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/50.0.2661.102 Chrome/50.0.2661.102 Safari/537.36
    [Referer] => http://172.17.0.2/
    [Accept-Encoding] => gzip, deflate, sdch
    [Accept-Language] => en-US,en;q=0.8,de;q=0.6
    [Cookie] => mod_auth_openidc_session=d3ac0da9-bd54-48c9-beed-896f7b72298b;Path=/;HttpOnly;mod_auth_openidc_session=d3ac0da9-bd54-48c9-beed-896f7b72298b
    [OIDC_CLAIM_name] => Theo Tester
    [OIDC_CLAIM_sub] => 7e1bafd9-5c77-4508-a706-034f17a513dd
    [OIDC_CLAIM_email] => tom+tester@localhost
    [OIDC_CLAIM_preferred_username] => tester
    [OIDC_CLAIM_given_name] => Theo
    [OIDC_CLAIM_family_name] => Tester
    [OIDC_CLAIM_jti] => ef77e2d8-6ea6-4392-99ff-46fe642b7aaf
    [OIDC_CLAIM_nbf] => 0
    [OIDC_CLAIM_exp] => 1464901382
    [OIDC_CLAIM_iss] => http://172.17.0.1:8081/auth/realms/master
    [OIDC_CLAIM_iat] => 1464901322
    [OIDC_CLAIM_aud] => mod_oidc_example_client
    [OIDC_CLAIM_typ] => ID
    [OIDC_CLAIM_azp] => mod_oidc_example_client
    [OIDC_CLAIM_nonce] => uaLm6CvBIefgiKdYbU26uCZi82X_dy7QFtyv5LgtYyo
    [OIDC_CLAIM_session_state] => d69c9478-6331-4e70-a4e7-6799bfc0aff1
    [OIDC_access_token] => eyJhbGciOiJSUzI1NiJ9.eyJqdGkiOiJjNTRlZGUyNS1lYWQ2LTRiNzYtOTYwYS1jOWQyMzY0MThlNmYiLCJleHAiOjE0NjQ5MDEzODIsIm5iZiI6MCwiaWF0IjoxNDY0OTAxMzIyLCJpc3MiOiJodHRwOi8vMTcyLjE3LjAuMTo4MDgxL2F1dGgvcmVhbG1zL21hc3RlciIsImF1ZCI6Im1vZF9vaWRjX2V4YW1wbGVfY2xpZW50Iiwic3ViIjoiN2UxYmFmZDktNWM3Ny00NTA4LWE3MDYtMDM0ZjE3YTUxM2RkIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoibW9kX29pZGNfZXhhbXBsZV9jbGllbnQiLCJub25jZSI6InVhTG02Q3ZCSWVmZ2lLZFliVTI2dUNaaTgyWF9keTdRRnR5djVMZ3RZeW8iLCJzZXNzaW9uX3N0YXRlIjoiZDY5Yzk0NzgtNjMzMS00ZTcwLWE0ZTctNjc5OWJmYzBhZmYxIiwiY2xpZW50X3Nlc3Npb24iOiIyYjFkMTAxNC05MTE4LTRhODMtOWYzZS1mNzI0ZDNlNzZkZmEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cDovLzE3Mi4xNy4wLjIvKiIsImh0dHA6Ly8xNzIuMTcuMC4yOjgwLyoiXSwicmVzb3VyY2VfYWNjZXNzIjp7Im1vZF9vaWRjX2V4YW1wbGVfY2xpZW50Ijp7InJvbGVzIjpbInVzZXIiXX0sImFjY291bnQiOnsicm9sZXMiOlsibWFuYWdlLWFjY291bnQiLCJ2aWV3LXByb2ZpbGUiXX19LCJuYW1lIjoiVGhlbyBUZXN0ZXIiLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJ0ZXN0ZXIiLCJnaXZlbl9uYW1lIjoiVGhlbyIsImZhbWlseV9uYW1lIjoiVGVzdGVyIiwiZW1haWwiOiJ0b20rdGVzdGVyQGxvY2FsaG9zdCJ9.PMtCiZrplUXSXtnEAdn12zlmOlmG2kZibWCMgMFuNXjQMgvTJdv_pXezN_zo4aHKJAX_EeX_APS5OwWXWyjPRa1gdTe-46j0kMVj3D0XpNeXk7odw2Cukw7XY6Gww8e9jJppz1_YZsmjgxG9GfF8pxpz0rcguXmoZO7CpeimODrbmG3JOL9YnTzVrdJaud33IhmgWShrjZXtpMSYAiLNABjoUIxphI37LzaC_ZYYGCRI8lcWL6AbRSxTkAap4SJxjsGcuX3SJdhM_6dZTZ-XJdra49ukyjhVFWSkSKm7ii0YkftNMYjLCyvhCh5kLyLLDEQrRiLP5YfjebFEx0yN7Q
    [OIDC_access_token_expires] => 1464901382
)
```

# Troubleshooting

## Docker container cannot access Keycloak

If your container cannot access the keycloak instance running on the host 
you could relax the iptables rules for the local docker0 network interface.
```
sudo iptables -A INPUT -i docker0 -j ACCEPT
```
