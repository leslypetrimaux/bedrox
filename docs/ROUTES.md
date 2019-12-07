# /config/routes.yaml
Retrouvez ici un exemple pour le fichier de routes.

---
```yaml
home:
  path: '/'
  controller: 'App\Controllers\DefaultController::default'

users_list:
  path: '/users'
  controller: 'App\Controllers\DefaultController::list'

users_get:
  path: '/users/get/{users}/[string]'
  controller: 'App\Controllers\DefaultController::card'
  entity:
    users

set_data:
  path: '/set/string/route_data()'
  controller: 'App\Controllers\DefaultController::data'
```

```bash
# route_data()
[string] # Correspond au type PHP 'string'
[num] # Correspond au type PHP 'int'
[date] # Correspond au type PHP 'DateTime'
[bool] # Correspond au type PHP 'bool'
```
