<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(
    message: 'The email {{ value }} is not a valid email.',
    )]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Conference $conference = null;

    /**
     * @var Collection<int, Schematic>
     */
    #[ORM\OneToMany(targetEntity: Schematic::class, mappedBy: 'comment')]
    private Collection $schematics;

    public function __construct()
    {
        $this->schematics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): static
    {
        $this->conference = $conference;

        return $this;
    }

    /**
     * @return Collection<int, Schematic>
     */
    public function getSchematics(): Collection
    {
        return $this->schematics;
    }

    public function addSchematic(Schematic $schematic): static
    {
        if (!$this->schematics->contains($schematic)) {
            $this->schematics->add($schematic);
            $schematic->setComment($this);
        }

        return $this;
    }

    public function removeSchematic(Schematic $schematic): static
    {
        if ($this->schematics->removeElement($schematic)) {
            // set the owning side to null (unless already changed)
            if ($schematic->getComment() === $this) {
                $schematic->setComment(null);
            }
        }

        return $this;
    }
}
