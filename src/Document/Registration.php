<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Registration document.
 * Links one user to one session (join/association document).
 */
#[ODM\Document]
class Registration
{
    // MongoDB ObjectId stored as string in the app layer.
    #[ODM\Id]
    private ?string $id = null;

    // Registered participant.
    #[ODM\ReferenceOne(targetDocument: User::class)]
    private ?User $user = null;

    // Session that the user registers for.
    #[ODM\ReferenceOne(targetDocument: Session::class)]
    private ?Session $session = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }
}
