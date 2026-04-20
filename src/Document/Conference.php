<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Conference document.
 * Represents a main event that can contain many sessions.
 */
#[ODM\Document]
class Conference
{
    // MongoDB ObjectId stored as string in the app layer.
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private ?string $title = null;

    #[ODM\Field(type: 'string')]
    private ?string $description = null;

    #[ODM\Field(type: 'string')]
    private ?string $location = null;

    // Stored as a MongoDB date; mapped to DateTimeInterface in PHP.
    #[ODM\Field(type: 'date')]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
