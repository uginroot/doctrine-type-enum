<?php


namespace Uginroot\DoctrineTypeEnum\Test\Type;


use Uginroot\DoctrineTypeEnum\AbstractDoctrineTypeEnum;
use Uginroot\DoctrineTypeEnum\Test\Enum\Gender;

class GenderType extends AbstractDoctrineTypeEnum
{

    public function getClass():string
    {
        return Gender::class;
    }
}