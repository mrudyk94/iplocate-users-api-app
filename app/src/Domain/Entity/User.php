<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\Traits\EntityId;
use App\Domain\Entity\Traits\Timestampable;
use App\Infrastructure\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Table(name: 'user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements EntityInterface
{
    use EntityId;
    use Timestampable;

    #[ORM\Column(name: 'firstName', type: Types::STRING, length: 100)]
    private string $firstName;

    #[ORM\Column(name: 'lastName', type: Types::STRING, length: 100)]
    private string $lastName;

    #[ORM\Column(name: 'ip', length: 45, nullable: true)]
    private ?string $ip;

    #[ORM\Column(name: 'country', length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\OneToMany(
        targetEntity: PhoneNumber::class,
        mappedBy: 'user',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $phoneNumbers;

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string|null $ip
     */
    public function __construct(string $firstName, string $lastName, ?string $ip = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->ip = $ip;
        $this->phoneNumbers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     * @return void
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return Collection
     */
    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @return $this
     */
    public function addPhoneNumber(PhoneNumber $phoneNumber): static
    {
        if (!$this->phoneNumbers->contains($phoneNumber)) {
            $this->phoneNumbers->add($phoneNumber);
            $phoneNumber->setUser($this);
        }

        return $this;
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @return $this
     */
    public function removePhoneNumber(PhoneNumber $phoneNumber): static
    {
        if ($this->phoneNumbers->removeElement($phoneNumber)) {
            if ($phoneNumber->getUser() === $this) {
                $phoneNumber->setUser(null);
            }
        }

        return $this;
    }
}
