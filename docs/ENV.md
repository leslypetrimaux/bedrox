# /config/env.yaml
Retrouvez ici la liste des valeurs possibles pour le fichier d'environnement ainsi que des exemples pour les différentes databases.

---
<details>
<summary>Valeurs des SGBD disponibles :</summary>

```bash
# app_sgbd()
mysql # PDO MySQL
mariadb # PDO MySQL
firebase # Firebase Realtime Database
firestore # Firebase Cloud Firestore
```
</details>
<details>
<summary>Valeur d'encodage de caractères de l'application :</summary>

```bash
# app_encode()
utf-8
```
</details>
<details>
<summary>Valeurs des formats de retour de l'application :</summary>

```bash
# app_format()
json
xml
```
</details>

---
> Configuration avec MySQL ou MariaDB :
```yaml
app:
  name: 'Mon Application'
  version: '0.1.6'
  env: 'dev'
  database:
    driver: 'mysql|mariadb'
    host: 'localhost'
    port: '3306'
    user: 'framework'
    password: 'framework'
    schema: 'framework'
  encodage: 'utf-8'
  format: 'json'
  router: './routes.yaml'
  security: './security.yaml'
```

> Configuration avec Firebase Realtime Database ou Cloud Firestore :
```yaml
app:
  name: 'Mon Application'
  version: '0.1.6'
  env: 'dev'
  database:
    driver: 'firebase|firestore'
    host: 'projectId'
    apiKey: 'apiKey'
    clientId: 'clientId'
    oAuthToken: 'googleCloudOAuthToken'
    type: 'public'
  encodage: 'utf-8'
  format: 'xml'
  router: './routes.yaml'
  security: './security.yaml'
```

---
```diff
- ATTENTION ! Pour le moment, seuls les bases accessibles en lecture/écriture public sur Firebase fonctionnent. L'authentification Firebase n'est pas encore supportée.
```