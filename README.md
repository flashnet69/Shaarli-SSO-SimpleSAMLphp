# Shaarli-SSO-SimpleSAMLphp

This plugin allows to connect a user in Shaarli via SSO and and authorizes him to connect only to his instance

## Requirements
- Shaarli >= 0.12.1 
- SimpleSAMLphp configured on the same server as a Service Provider (https://simplesamlphp.org/docs/stable/simplesamlphp-sp)

## Installation

Add the plugins in Shaarli, in the "plugins" folder

Activate the plugin and include the path of simpleSAMLphp (SSO_SIMPLESAML_PATH)

You can now authenticate and login in Shaarli via SSO

## Operating mode
- When I access my Shaarli instance I can connect to it.
- By default, if I access another Shaarli instance and I am already logged in to mine, we are logged in to the other instance. I have set up an automatic logout.
- When I am on the login page, the connection is refused if I am not on my instance
- If I am connected to the SP and I am not on my Shaarli instance, the connection icon is hidden