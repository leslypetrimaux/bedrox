# dump()
La fonction `dump()` permet de printer un ou plusieurs éléments (string, int, array, object, etc...) sans stopper l'exécution d'un script.

Cette fonction est actuellement en cours de développement afin de regrouper tous les dumps pour les printer une seule fois (non disponible).
```diff
- CETTE FONCTION EST INSTABLE ! UTILISEZ LA FONCTION dd()
```

# dd()
La fonction `dd()` permet de printer un ou plusieurs éléments (string, int, array, object, etc...) et stopper l'exécution du script.

# Exemples

## dd(...string array|mixed[])
Appel de la fonction dans un controller :
```php
<?php

namespace App\Controllers;

use Bedrox\Core\Controller;

class DefaultController extends Controller
{
    /**
     * @return array
     */
    public function default(): array
    {
        $array = array(
            'test' => 'valeur test'
        );
        dd($array);
        return $array;
    }
}
```

Retour de la fonction en JSON :
```json
{
  "status": "success",
  "statusCode": 200,
  "execTime": 24.9,
  "data": {
    "file": "./mon_dossier/src/App/Controllers/DefaultController.php",
    "line": 18,
    "dumps": [
      {
        "array": {
          "test": "valeur test"
        }
      }
    ]
  },
  "error": null
}
```

Retour de la fonction en XML :
```xml
<Response>
    <status>success</status>
    <statusCode>200</statusCode>
    <execTime>9.52</execTime>
    <data>
        <file>./mon_dossier/src/App/Controllers/DefaultController.php</file>
        <line>18</line>
        <dumps>
            <item-0>
                <array>
                    <test>valeur test</test>
                </array>
            </item-0>
        </dumps>
    </data>
    <error/>
</Response>
```
