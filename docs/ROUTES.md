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
  path: '/users/string/[string]' # Correspond au type PHP 'string'
  controller: 'App\Controllers\DefaultController::card'

set_float:
  path: '/users/float/[float]' # Correspond au type PHP 'float'
  controller: 'App\Controllers\DefaultController::card'

set_date:
  path: '/users/date/[date]' # Correspond au type PHP 'DateTime'
  controller: 'App\Controllers\DefaultController::card'

set_bool:
  path: '/users/bool/[bool]' # Correspond au type PHP 'bool'
  controller: 'App\Controllers\DefaultController::card'
```
