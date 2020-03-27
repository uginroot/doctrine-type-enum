<?php


namespace Uginroot\DoctrineTypeEnum;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use ReflectionClass;
use ReflectionException;
use Uginroot\DoctrineTypeEnum\Exceptions\UnexpectedExtendsException;
use Uginroot\DoctrineTypeEnum\Exceptions\UnsupportedPlatformException;
use Uginroot\PhpEnum\EnumAbstract;

abstract class EnumDoctrineTypeAbstract extends Type
{
    /**
     * @var string
     */
    private $setClass;

    abstract public function getClass():string;

    private function getSetClass():string
    {
        if ($this->setClass === null) {
            $class = $this->getClass();
            $this->checkClass($class);
            $this->setClass = $class;
        }

        return $this->setClass;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform):bool
    {
        if ($platform instanceof MySqlPlatform) {
            return true;
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }


    /**
     * @inheritDoc
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     * @throws ReflectionException
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform):string
    {
        /** @var EnumAbstract $setClass */
        $setClass = $this->getSetClass();
        $names = $setClass::getChoice()->getNames();
        sort($names);
        $namesQuotes = [];
        foreach ($names as $name){
            $namesQuotes[] = sprintf("'%s'", $name);
        }
        $namesString = implode(',', $namesQuotes);

        if ($platform instanceof MySqlPlatform) {
            return sprintf('ENUM(%s)', $namesString);
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
     */
    public function getName():string
    {
        $reflectionClass = new ReflectionClass($this->getSetClass());
        return $reflectionClass->getShortName();
    }

    protected function checkClass(string $class):void
    {
        if (!is_subclass_of($class, EnumAbstract::class)) {
            throw new UnexpectedExtendsException(
                sprintf('Class %s not extends %s', $class, EnumAbstract::class)
            );
        }
    }

    /**
     * @param null|EnumAbstract $value
     * @param AbstractPlatform $platform
     * @return mixed|string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($platform instanceof MySqlPlatform) {
            return $value->getName();
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }

    /**
     * @param null|EnumAbstract $value
     * @param AbstractPlatform $platform
     * @return mixed|EnumAbstract
     * @throws ReflectionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        /** @var EnumAbstract $enumClass */
        $enumClass = $this->getSetClass();

        if ($value === null || is_a($value, $enumClass)) {
            return $value;
        }

        if ($platform instanceof MySqlPlatform) {
            return $enumClass::createByName($value);
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        $types = parent::getMappedDatabaseTypes($platform);

        if ($platform instanceof MySqlPlatform) {
            $platformType = 'enum';
            if(!in_array($platformType, $types)){
                $types[] = $platformType;
            }
        }

        return $types;
    }
}