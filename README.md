# Install
```bash
composer require uginroot/doctrine-type-enum:^1.2
```

# Using

## Create enum class
```php

# Create enum class
namespace App\Type;

use Uginroot\PhpEnum\EnumAbstract;

class Gender extends EnumAbstract{
    public const FEMALE = 1;
    public const MALE = 0;
}
```

## Create doctrine type class
```php
namespace App\DoctrineType;

use Uginroot\DoctrineTypeEnum\EnumDoctrineTypeAbstract;
use App\Type\Gender;

class GenderType extends EnumDoctrineTypeAbstract{
    public function getClass() : string{
        return Gender::class;
    }
}
```

## Register doctrine type in config/packages/doctrine.yaml file
```yaml
doctrine:
    dbal:
        types:
            GenderType: App\DoctrineType\GenderType
```

## Add mapping data to entity
```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Type\Gender;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User{
    
    /**
     * @var integer|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @var Gender|null
    * @ORM\Column(name="role", type="GenderType", nullable=true)
    */
    private $gender = null;
    
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Gender|null
     */
    public  function getGender(): ?Gender
    {
        return $this->gender;
    }

    /**
     * @param Gender $role
     * @return $this
     */
    public function setGender(Gender $gender):self
    {
        $this->gender = $gender;
        return $this;
    }
}
```