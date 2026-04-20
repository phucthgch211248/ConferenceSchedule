<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Session document.
 * Represents one timeslot/activity that belongs to a conference.
 */
#[ODM\Document]
class Session
{
    // MongoDB ObjectId stored as string in the app layer.
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private ?string $title = null;

    // Stored as MongoDB date values for schedule calculations.
    #[ODM\Field(type: 'date')]
    private ?\DateTimeInterface $startTime = null;

    #[ODM\Field(type: 'date')]
    private ?\DateTimeInterface $endTime = null;

    // Many sessions can point to one conference (Session belongs to Conference).
    #[ODM\ReferenceOne(targetDocument: Conference::class)]
    private ?Conference $conference = null;

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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }
}
