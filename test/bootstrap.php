<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'IntegrationTestCase.php';

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
