# Installation d'un nouveau projet
Afin de pouvoir utiliser pleinement Bedrox, il faut commencer par créer un nouveau projet avec composer en tapant la commande suivante :
```bash
composer create-project bedrox/bedrox-api mon_dossier
```
Vous pourrez ainsi démarrer votre nouveau projet avec une architecture complète et pré-configurée.

Les dossiers d'un projet se présentent de la manière suivante :
```yaml
mon_dossier:
  config: # configurations de l'application
    routes:
      users.yaml
    env.yaml
    routes.yaml
    security.yaml
  public: # dossier web
    .htaccess
    index.php
  src: # sources de l'application
    App:
      Cli: # conteneur des commandes à ajouter au CLI
        Commands: # contient mes commandes CLI personnalisées
          CustomExample.php
        Console.php
      Controllers: # contient les controllers des routes
        DefaultController.php
      Entity: # contient les entités pour les databases
        Users.php
      Services: # contient les services à injecter dans les controlleurs
        AppService.php
        UsersService.php
      Kernel.php
  vendor: # comporte toutes les dépendances de composer
  composer.json
```
Nous aborderons tous ces fichiers lors de la configuration du projet

# Configuration d'un projet
Notre projet étant installé, nous allons devoir le configurer afin qu'il fonctionne pour notre environnement. Dans cette partie, nous verrons comment modifier les fichiers de configuration et utiliser le framework.

__L'installation d'un nouveau projet possède certains des exemples qui seront vu dans la documentation.__

Pour correctement modifier ces fichiers, contenu dans le dossier `config/`, nous verrons des exemples concerts.
```diff
- ATTENTION ! Actuellement, seul l'encodage en "utf-8" pour l'application et les databases est supporté.
```

## Environnement
Pour configurer l'environnement de l'application, il faut remplir le fichier `./config/env.yaml` avec la synthaxe suivante :

```yaml
app:
  name: '%APP_NAME%'
  version: '%APP_VERSION%'
  env: 'dev|prod'
  database:
    type: 'native|doctrine'
    driver: 'app_sgbd()'
    ### Voir les exemples pour les différentes databases supportées
  encodage: 'app_encode()'
  format: 'app_format()'
  router: '%RELATIVE_PATH_TO_ROUTER_FILE_FROM_ROOT_PROJECT%'
  security: '%RELATIVE_PATH_TO_SECURITY_FILE_FROM_ROOT_PROJECT%'
```
Vous pouvez retrouver le détails concernant ce fichier dans la documentation [ENV.md](./docs/ENV.md).

## Routes
Vous pouvez déclarer autant de route et de controller que vous le souhaitez. Afin de configurer une route, créez le fichier `./config/routes.yaml`. Vous pouvez le remplir de la manière suivante :

```yaml
%ROUTE_NAME1%:
  path: '%ROUTE_PATH%'
  controller: '%NAMESPACE\CLASSNAME%::%FUNCTION_NAME%'

%ROUTE_NAME2%:
  path: '%ROUTE_PATH%{%ENTITY%}'
  controller: '%NAMESPACE\CLASSNAME%::%FUNCTION_NAME%'
  params:
    %ENTITY%

set_string:
  path: '%ROUTE_PATH%[%TYPE%]'
  controller: '%NAMESPACE\CLASSNAME%::%FUNCTION_NAME%'
```
Vous pouvez retrouver le détails concernant ce fichier dans la documentation [ROUTES.md](./docs/ROUTES.md).

## Sécurité
Afin de configurer le firewall, il faut créer le fichier `./config/security.yaml`  et le remplir de la manière suivante :

```yaml
security:
  firewall:
    type: 'app_auth()'
    token:
      encode: 'token_algos()'
      secret: '%APP_SECRET%'
    anonymous:
      %ROUTE_NAME1%
      %ROUTE_NAME2%
```
Vous pouvez retrouver le détails concernant ce fichier dans la documentation [SECURITY.md](./docs/SECURITY.md).

# Utilisation du framework
Nous verrons dans cette partie comment utiliser Bedrox.

## Environnements
Le framework possède deux modes de développement : `dev` & `prod`.

### Développement
L'environnement `dev` vous permet d'afficher toutes les exceptions et les dumps gérés par le framework.

*(voir la section [debug](#debug))*

### Production
L'environnement `prod` est encore en cours de développement.

Il permettra de gérer le comportement de l'application en fonction des types d'erreurs retournés.

## Debug
Bedrox fournit deux fonctions de *dump*. Celles-ci s'adaptent au format de votre application afin de printer dans un logiciel autre que votre navigateur internet.

```text
dump(...string: array|mixed[]);
dd(...string: array|mixed[]);
```
Vous pouvez retrouver le détails concernant ce fichier dans la documentation [DEBUG.md](./docs/DEBUG.md).

## Command Line Interface
Documentation en cours de rédaction.

## Controllers
Documentation en cours de rédaction.

## Entités
Documentation en cours de rédaction.

## Services
Documentation en cours de rédaction.
