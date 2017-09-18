<?php

final class Assert
{
    public static function __callStatic($method, array $arguments)
    {
        if (strpos($method, 'is') === 0) {
            $method = substr($method, 2);
        }

        $method = 'PHPUnit\Framework\Assert::assert'.ucfirst($method);

        call_user_func_array($method, $arguments);
    }

    public static function containsAll(array $excepts, array $provides)
    {
        foreach ($excepts as $value) {
            static::contains($value, $provides);
        }
    }

    public static function success()
    {
        static::equals('Success', 'Success');
    }

    public static function failure($actual = 'Success')
    {
        static::equals('Failure', $actual);
    }
}
