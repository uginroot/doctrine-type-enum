<?php


namespace Uginroot\DoctrineTypeEnum\Test\Type;


use Uginroot\DoctrineTypeEnum\EnumDoctrineTypeAbstract;
use Uginroot\DoctrineTypeEnum\Test\Enum\Gender;

class GenderDoctrine extends EnumDoctrineTypeAbstract
{

    public function getClass():string
    {
        return Gender::class;
    }
}