<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\Traits\EntityId;
use App\Domain\ValueObject\MobilePhone;
use App\Infrastructure\Repository\PhoneNumberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'phone_numbers')]
#[ORM\Entity(repositoryClass: PhoneNumberRepository::class)]
#[ORM\UniqueConstraint(name: 'phone_unique_idx', columns: ['phone'])]
class PhoneNumber implements EntityInterface
{
    use EntityId;

    #[ORM\Column(name: 'phone', type: 'vo_mobile_phone', length: 13)]
    private MobilePhone $number;

    #[ORM\ManyToOne(inversedBy: 'phoneNumbers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * @param string $number
     */
    public function __construct(string $number)
    {
        $this->number = new MobilePhone($number);
    }

    /**
     * @return MobilePhone
     */
    public function getNumber(): MobilePhone
    {
        return $this->number;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return void
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
