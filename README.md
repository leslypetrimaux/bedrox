# Framework Bedrox

## Installation d'un nouveau projet
Afin de pouvoir utiliser pleinement Bedrox, il faut commencer par créer un nouveau projet avec composer en tapant la commande suivante :
```bash
composer create-project bedrox/bedrox-api mon_dossier
```
Vous pourrez ainsi démarrer votre nouveau projet avec une architecture complète et pré-configurée.

Les dossiers d'un projet se présentent de la manière suivante :
```yaml
mon_dossier:
  config: # configurations de l'application
    env.yaml
    routes.yaml
    security.yaml
  public: # dossier web
    .htaccess
    index.php
  src: # sources de l'application
    App:
      Controllers: # contient les controllers des routes
        DefaultController.php
      Entity: # contient les entités pour les databases
        Users.php
      Kernel.php
  vendor: # comporte toutes les dépendances de composer
  composer.json
```
Nous aborderons tous ces fichiers lors de la configuration du projet

## Configuration d'un projet
Notre projet étant installé, nous allons devoir le configurer afin qu'il fonctionne pour notre environnement. Dans cette partie, nous verrons comment modifier les fichiers de configuration et utiliser le framework.

### Configurations
Pour correctement modifier ces fichiers, contenu dans le dossier `config/`, nous verrons des exemples concerts.
```diff
- ATTENTION ! Actuellement, seul l'encodage en "utf-8" pour l'application et les databases est supporté.
```

#### Environnement
Pour configurer l'environnement de l'application, il faut remplir le fichier `./config/env.yaml` avec la synthaxe suivante :

```yaml
app:
  name: '%APP_NAME%'
  version: '%APP_VERSION%'
  env: 'dev|prod'
  database:
    driver: 'app_sgbd()'
    ### Voir les exemples pour les différentes databases supportées
  encodage: 'app_encode()'
  format: 'app_format()'
  router: '%RELATIVE_PATH_TO_ROUTER_FILE_FROM_ROOT_PROJECT%'
  security: '%RELATIVE_PATH_TO_SECURITY_FILE_FROM_ROOT_PROJECT%'
```
Vous pouvez retrouver le détails concernant ce fichier dans la documentation [ENV.md](./docs/ENV.md)

#### Routes

#### Sécurité
