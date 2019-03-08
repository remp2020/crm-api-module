# CRM API Module

This documentation serves as a complement to [CRM Skeleton](https://github.com/remp2020/crm-skeleton/#registerapicalls)
documentation related to creating and registering API handlers.

It describes API handlers provided by this module for others to use.

## Enabling module

Make sure you add extension to your `app/config/config.neon` file.

```neon
extensions:
	- Crm\ApiModule\DI\ApiModuleExtension
```

## API documentation

All examples use `http://crm.press` as a base domain. Please change the host to the one you use
before executing the examples.

All examples use `XXX` as a default value for authorization token, please replace it with the
real tokens:

* TODO: how to get Bearer tokens
* TODO: how to get User tokens

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