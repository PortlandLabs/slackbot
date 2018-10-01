<?php

require dirname(__DIR__) . "/vendor/autoload.php";

// Console Error Reporting
(new \NunoMaduro\Collision\Provider)->register();

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', dirname(__DIR__) . '/vendor/jms/serializer/src'
);

$env = new \Dotenv\Dotenv(dirname(__DIR__));
$env->load();
$env->required(['AUTH_TOKEN', 'ADMIN_CHANNEL']);
