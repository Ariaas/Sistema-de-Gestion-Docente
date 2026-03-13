<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'IntegrationTestCase.php';

// Compatibility layer for legacy integration tests that instantiate
// model classes without namespaces (e.g., new Anio()).
$modelFiles = glob(__DIR__ . '/../model/*.php');
if ($modelFiles !== false) {
    sort($modelFiles, SORT_NATURAL | SORT_FLAG_CASE);
    foreach ($modelFiles as $modelFile) {
        require_once $modelFile;
    }
}

foreach (get_declared_classes() as $declaredClass) {
    if (strpos($declaredClass, 'App\\Model\\') !== 0) {
        continue;
    }

    $shortName = substr($declaredClass, strrpos($declaredClass, '\\') + 1);
    if ($shortName !== '' && !class_exists($shortName, false)) {
        class_alias($declaredClass, $shortName);
    }
}

function getConnection($model)
{
    if (!is_object($model)) {
        return null;
    }
    $rc = new ReflectionObject($model);
    while ($rc) {
        if ($rc->hasMethod('Con')) {
            $m = $rc->getMethod('Con');
            $m->setAccessible(true);
            return $m->invoke($model);
        }
        $rc = $rc->getParentClass();
    }
    return null;
}
