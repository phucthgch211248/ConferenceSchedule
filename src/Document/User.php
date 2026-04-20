<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * User document stored in MongoDB.
 * This model represents a participant who can register for sessions.
 */
#[ODM\Document]
class User
{
    // MongoDB ObjectId stored as string in the app layer.
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private ?string $name = null;

    #[ODM\Field(type: 'string')]
    private ?string $email = null;

    #[ODM\Field(type: 'string')]
    private ?string $password = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
