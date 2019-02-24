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
  path: '/users/get/{users}'
  controller: 'App\Controllers\DefaultController::card'
  params:
    users
```