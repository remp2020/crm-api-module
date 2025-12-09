# CRM API Module

[![Translation status @ Weblate](https://hosted.weblate.org/widgets/remp-crm/-/api-module/svg-badge.svg)](https://hosted.weblate.org/projects/remp-crm/api-module/)

This documentation serves as a complement to [CRM Skeleton](https://github.com/remp2020/crm-skeleton/#registerapicalls)
documentation related to creating and registering API handlers.

It describes API handlers provided by this module for others to use.

## Enabling module

Make sure you add extension to your `app/config/config.neon` file.

```neon
extensions:
	- Crm\ApiModule\DI\ApiModuleExtension
```

### Configuration

#### Preflight requests

Module allows you to configure preflight request handling. Add following snippet to your `config.neon` file:

```neon
services:
	# ...
	apiHeadersConfig:
		setup:
			- setAllowedOrigins('*')
			- setAllowedHeaders('Content-Type', 'Authorization', 'X-Requested-With')
			- setAllowedHttpMethods('*')
			- setAllowedCredentials(true)
			- setAccessControlMaxAge(600) # seconds
```

You can configure allowed origins by explicitly stating them or by using wildcards. Following configurations are valid:

- `setAllowedOrigins("*")`. Matches everything
- `setAllowedOrigins("foo.bar", "*.foo.bar")`. Matches `foo.bar` and all of its subdomains.
- `setAllowedOrigins("foo.bar", "1.foo.bar")`. Matches `foo.bar`, `1.foo.bar`, but nothing else (nor any other subdomain).

#### API requests logging

You can globally enable or disable API logging in the CRM admin - config section. If the logging is enabled, you can further configure which specific paths should be logged or not.

We recommend using configuration of blacklist over whitelist. Otherwise you might encounter a scenario, where logs from one of our APIs might be missing when you need them.

To enable blacklist, add following snippet to your `config.neon` file:

```neon
services:
	# ...
	apiLoggerConfig:
		setup:
			- setPathBlacklist([
				Crm\ApiModule\Models\Api\LoggerEndpointIdentifier('1', 'foo', 'bar'),
			])
```

The `LoggerEndpointIdentifier` requires three parameters: `version`, `package` and `apiCall` - same parameters which you use when you register a new API handler. The snippet above will not log requests to the `/api/v1/foo/bar` endpoint.

You can also use wildcards where necessary:

- `Crm\ApiModule\Models\Api\LoggerEndpointIdentifier('*', 'foo', 'bar')` will match all requests going to `/api/*/foo/bar`.
- `Crm\ApiModule\Models\Api\LoggerEndpointIdentifier('1', '*', '*')` will match all requests going to `/api/v3/*/*`.

Blacklist and whitelist cannot be combined, the latter configured wins.

By default the API logger redacts standard fields found in GET parameters, POST parameters and JSON payload parameters: `password`, `token` and `auth`. If you need to extend this and redact other parameters, you can extend the redaction:

```neon
	# ...
	apiLoggerConfig:
		setup:
			- addRedactedFields(['internal_token'])
```

#### Data retention configuration

You can configure time before which `application:cleanup` deletes old repository data and column which it uses by using (in your project configuration file):

```neon
apiLogsRepository:
	setup:
		- setRetentionThreshold('-2 months', 'created_at')
```

## Database tables migration

Because of need of changing primary keys (int -> bigint), in tables that contain lots of data (or have risk of overflowing primary key if its int), we had to create migration process. Since some tables are very exposed and cannot be locked for more than a couple of seconds, we decided to create new tables, migrate the data manually and keep the old and new table in sync while migrating.

_This migration process is necessary only for installations after specific version for specific table, and is two steps process._

### Api logs migration (installed before version 2.5.0)

Consists of `api_logs` table migration.

Steps:
1. Run phinx migrations command `phinx:migrate`, which creates new table `api_logs_v2` (in case there is no data in table, migration just changes type of primary key and next steps are not needed).
2. Run command `api:convert_api_logs_to_bigint`, which copies data from old table to new (e.g. `api_logs` to `api_logs_v2`). Command will after successful migration atomically rename tables (e.g. `api_logs` -> `api_logs_old` and `api_logs_v2` -> `api_logs`), so when the migration ends only new tables are used.

It's recommended to run `application:bigint_migration_cleanup api_logs` command, at least 2 weeks (to preserve backup data, if some issue emerges) after successful migration to drop left-over tables.

## API documentation

All examples use `http://crm.press` as a base domain. Please change the host to the one you use
before executing the examples.

All examples use `XXX` as a default value for authorization token, please replace it with the
real tokens:

* *API tokens.* Standard API keys for server-server communication. It identifies the calling application as a whole.
They can be generated in CRM Admin (`/api/api-tokens-admin/`) and each API key has to be whitelisted to access
specific API endpoints. By default the API key has access to no endpoint. 
* *User tokens.* Generated for each user during the login process, token identify single user when communicating between
different parts of the system. The token can be read:
    * From `n_token` cookie if the user was logged in via CRM.
    * From the response of [`/api/v1/users/login` endpoint](https://github.com/remp2020/crm-users-module#post-apiv1userslogin) -
    you're free to store the response into your own cookie/local storage/session.

API responses can contain following HTTP codes:

| Value | Description |
| --- | --- |
| 200 OK | Successful response, default value | 
| 400 Bad Request | Invalid request (missing required parameters) | 
| 403 Forbidden | The authorization failed (provided token was not valid) | 
| 404 Not found | Referenced resource wasn't found | 

If possible, the response includes `application/json` encoded payload with message explaining
the error further.

---

#### GET `/api/v1/token/check`

API servers for checking the validity of provided Bearer token within header.

##### *Headers:*

| Name | Value | Required |
| --- |---| --- |
| Authorization | Bearer XXX | yes |

##### *Example:*

```shell
curl -v –X GET http://crm.press/api/v1/token/check \ 
-H "Content-Type:application/json" \
-H "Authorization: Bearer XXX"
```

Response:

```json
{
    "status": "ok"
}
```