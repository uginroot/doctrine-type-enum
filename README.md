# Install
```bash
composer require uginroot/doctrine-type-enum:^1.3
```

# Using

## Create doctrine type
```php

// Create enum class
namespace App\Type;

use Uginroot\PhpEnum\EnumAbstract;

class Gender extends EnumAbstract{
    public const FEMALE = 1;
    public const MALE = 0;
}

// Create doctrine type class
namespace App\DoctrineType;

use Uginroot\DoctrineTypeEnum\EnumDoctrineTypeAbstract;

class GenderType extends EnumDoctrineTypeAbstract{
    public function getClass() : string{
        return Gender::class;
    }
}

// Add mapping data to entity
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $gender;
    
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
     * @param Gender $gender
     * @return $this
     */
    public function setGender(Gender $gender):self
    {
        $this->gender = $gender;
        return $this;
    }
}
```

## Register doctrine type
```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            GenderType: App\DoctrineType\GenderType
```