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

set_string:
  path: '/users/get/[string]' # Correspond au type PHP 'string'
  controller: 'App\Controllers\DefaultController::card'

set_float:
  path: '/users/get/[float]' # Correspond au type PHP 'float'
  controller: 'App\Controllers\DefaultController::card'

set_date:
  path: '/users/get/[date]' # Correspond au type PHP 'DateTime'
  controller: 'App\Controllers\DefaultController::card'

set_bool:
  path: '/users/get/[bool]' # Correspond au type PHP 'bool'
  controller: 'App\Controllers\DefaultController::card'
```
