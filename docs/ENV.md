# /config/env.yaml
Retrouvez ici la liste des valeurs possibles pour le fichier d'environnement ainsi que des exemples pour les différentes databases.

---
<details>
<summary>Valeurs des SGBD native disponibles :</summary>

```bash
# app_sgbd()
mysql # PDO MySQL
mariadb # PDO MySQL
firebase # Firebase Realtime Database
firestore # Firebase Cloud Firestore
```
</details>
<details>
<summary>Pour la configuration de Doctrine, référez-vous à la documentation correspondante.</summary>
</details>

---
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
> Configuration EDR (native) avec MySQL ou MariaDB :
```yaml
app:
  name: 'Mon Application'
  version: '0.6'
  env: 'dev'
  database:
    type: 'native'
    driver: 'mysql'
    host: 'localhost'
    port: '3306'
    user: 'framework'
    password: 'framework'
    schema: 'framework'
  encodage: 'utf-8'
  format: 'json'
```

> Configuration Doctrine avec MySQL ou MariaDB :
```yaml
app:
  name: 'Mon Application'
  version: '0.4'
  env: 'dev'
  database:
    type: 'doctrine'
    driver: 'pdo_mysql'
    host: 'localhost'
    port: '3306'
    user: 'framework'
    password: 'framework'
    schema: 'framework'
    encode: 'utf8mb4'
  encodage: 'utf-8'
  format: 'json'
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
```

---
```diff
- ATTENTION ! Pour le moment, seuls les bases accessibles en lecture/écriture public sur Firebase fonctionnent.
- L'authentification Firebase n'est pas encore supportée.
```
