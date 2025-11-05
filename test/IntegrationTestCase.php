<?php

use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $ref = new ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {
            $prop->setAccessible(true);
            $val = $prop->getValue($this);
            $this->nullifyConnectionsRecursively($val);
            $prop->setValue($this, null);
        }

        $this->nullifyGlobalsAndStatics();

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        parent::tearDown();
    }

    private function nullifyConnectionsRecursively(&$value)
    {
        if (is_object($value)) {
            $rc = new ReflectionObject($value);
            while ($rc) {
                if ($rc->hasProperty('conex')) {
                    $p = $rc->getProperty('conex');
                    $p->setAccessible(true);
                    $p->setValue($value, null);
                    break;
                }
                $rc = $rc->getParentClass();
            }

            foreach ($rc ? $rc->getProperties() : [] as $innerProp) {
                $innerProp->setAccessible(true);
                $innerVal = $innerProp->getValue($value);
                $this->nullifyConnectionsRecursively($innerVal);
                $innerProp->setValue($value, null);
            }
        } elseif (is_array($value)) {
            foreach ($value as &$item) {
                $this->nullifyConnectionsRecursively($item);
            }
            unset($item);
        }
    }

    private function nullifyGlobalsAndStatics()
    {
        $superglobals = ['_SESSION', '_POST', '_GET', '_COOKIE', '_FILES'];
        foreach ($superglobals as $sg) {
            if (isset($GLOBALS[$sg]) && is_array($GLOBALS[$sg])) {
                foreach ($GLOBALS[$sg] as $k => $v) {
                    $this->nullifyConnectionsRecursively($GLOBALS[$sg][$k]);
                    $GLOBALS[$sg][$k] = null;
                }
            }
        }

        foreach ($GLOBALS as $k => $v) {
            if ($k === 'GLOBALS') continue;
            if (is_object($v) || is_array($v)) {
                $this->nullifyConnectionsRecursively($GLOBALS[$k]);
                $GLOBALS[$k] = null;
            }
        }

        foreach (get_declared_classes() as $class) {
            try {
                $rc = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                continue;
            }
            foreach ($rc->getProperties(ReflectionProperty::IS_STATIC) as $sp) {
                $sp->setAccessible(true);
                $val = $sp->getValue();
                if (is_object($val)) {
                    $this->nullifyConnectionsRecursively($val);
                    $sp->setValue(null);
                } elseif (is_array($val)) {
                    $this->nullifyConnectionsRecursively($val);
                    $sp->setValue([]);
                }
            }
        }
    }
}
