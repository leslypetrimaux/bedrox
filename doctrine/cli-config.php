<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bedrox\Core\Env;
use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Skeleton;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

if (!isset($_SERVER['APP']['ENV'])) {
    (new Env())->load(__DIR__ . Env::FILE_ENV);
} else {
    BedroxException::render(
        'BEDROX_LOADER',
        'Impossible de charger correctement l\'environnement de l\'Application. Le projet n\'est pas valide. Rendez-vous sur la documentation.'
    );
}

$isDevMode = true;
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

return ConsoleRunner::createHelperSet(Skeleton::$entityManager);

