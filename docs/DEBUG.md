# dump()
La fonction `dump()` permet de printer un ou plusieurs éléments (string, int, array, object, etc...) sans stopper l'exécution d'un script.

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
  "dumps": {
    "file": "./mon_dossier/src/App/Controllers/DefaultController.php",
    "line": 18,
    "outputs": [
      {
        "array": {
          "test": "valeur test"
        }
      }
    ]
  },
  "error": {
    "code": "WARN_DUMPS",
    "message": "Des dumps sont encore présent dans votre code !"
  }
}
```

Retour de la fonction en XML :
```xml
<Response>
    <status>success</status>
    <statusCode>200</statusCode>
    <execTime>9.52</execTime>
    <dumps>
        <file>./mon_dossier/src/App/Controllers/DefaultController.php</file>
        <line>18</line>
        <outputs>
            <item-0>
                <array>
                    <test>valeur test</test>
                </array>
            </item-0>
        </outputs>
    </dumps>
    <error>
        <code>WARN_DUMPS</code>
        <message>Des dumps sont encore présent dans votre code !</message>
    </error>
</Response>
```
