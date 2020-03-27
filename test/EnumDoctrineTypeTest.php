<?php
declare(strict_types=1);

namespace Uginroot\DoctrineTypeSet\Test;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Uginroot\DoctrineTypeEnum\EnumDoctrineTypeAbstract;
use Uginroot\DoctrineTypeEnum\Exceptions\UnexpectedExtendsException;
use Uginroot\DoctrineTypeEnum\Exceptions\UnsupportedPlatformException;
use Uginroot\DoctrineTypeEnum\Test\Enum\Gender;
use Uginroot\DoctrineTypeEnum\Test\Type\GenderDoctrine;
use Uginroot\PhpEnum\EnumAbstract;

class EnumDoctrineTypeTest extends TestCase
{
    /**
     * @var EnumDoctrineTypeAbstract|null
     */
    private $type;

    /**
     * @throws DBALException
     * @throws ReflectionException
     */
    public static function setUpBeforeClass():void
    {
        $class = new ReflectionClass(GenderDoctrine::class);
        Type::addType($class->getShortName(), $class->getName());
    }

    /**
     * @throws DBALException
     * @throws ReflectionException
     */
    protected function setUp():void
    {
        $class = new ReflectionClass(GenderDoctrine::class);
        $type = Type::getType($class->getShortName());
        if($type instanceof EnumDoctrineTypeAbstract){
            $this->type = $type;
        }
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function providerConvertToDataBaseValue():array
    {
        return [
            'null' => [null, null],
            'male' => [new Gender(Gender::MALE), 'MALE'],
            'female' => [new Gender(Gender::FEMALE), 'FEMALE'],
            'nameMale' => [Gender::createByName('MALE'), 'MALE'],
            'nameFemale' => [Gender::createByName('FEMALE'), 'FEMALE'],
        ];
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider providerConvertToDataBaseValue
     */
    public function testConvertToDataBaseValue($value, $expected):void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($value, new MySqlPlatform()));
    }

    /**
     * @param $expected
     * @param $value
     * @throws ReflectionException
     * @dataProvider providerConvertToDataBaseValue
     */
    public function testConvertToPhpValue($expected, $value):void
    {
        $result = $this->type->convertToPHPValue($value, new MySqlPlatform());
        if($expected instanceof EnumAbstract){
            $this->assertTrue($expected->is($result));
            $this->assertSame(get_class($result), get_class($expected));
        } else {
            $this->assertSame($expected, $result);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function testGetSqlDeclaration():void
    {
        $expected = sprintf("ENUM('%s','%s')", 'FEMALE', 'MALE');
        $actual = $this->type->getSQLDeclaration([], new MySqlPlatform());
        $this->assertSame($expected, $actual);
    }

    public function testRequiresSqlCommentHint():void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint(new MySqlPlatform()));
    }

    /**
     * @throws ReflectionException
     */
    public function testUnexpectedExtendsException():void
    {
        $this->expectException(UnexpectedExtendsException::class);
        $class = new ReflectionClass($this->type);
        $method = $class->getMethod('checkClass');
        $method->setAccessible(true);
        $method->invokeArgs($this->type, [stdClass::class]);
    }

    public function testUnsupportedPlatformExceptionHint():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->requiresSQLCommentHint($platform);
    }

    /**
     * @throws ReflectionException
     */
    public function testUnsupportedPlatformExceptionDeclaration():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->getSQLDeclaration([], $platform);
    }

    /**
     * @throws ReflectionException
     */
    public function testUnsupportedPlatformExceptionToPhpValue():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->convertToPHPValue('MALE', $platform);
    }

    public function testUnsupportedPlatformExceptionToDatabaseValue():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->convertToDatabaseValue(new Gender(Gender::MALE), $platform);
    }

    public function testGetMappedDatabaseTypes():void
    {
        $platform = new MySqlPlatform();
        $types = $this->type->getMappedDatabaseTypes($platform);
        $this->assertContains('enum', $types);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetName():void
    {
        $this->assertSame('Gender', $this->type->getName());
    }
}