<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $datePrise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etatOeuvre = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Oeuvre $oeuvre = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Benevole $Benevole = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePrise(): ?\DateTime
    {
        return $this->datePrise;
    }

    public function setDatePrise(\DateTime $datePrise): static
    {
        $this->datePrise = $datePrise;

        return $this;
    }

    public function getEtatOeuvre(): ?string
    {
        return $this->etatOeuvre;
    }

    public function setEtatOeuvre(?string $etatOeuvre): static
    {
        $this->etatOeuvre = $etatOeuvre;

        return $this;
    }

    public function getOeuvre(): ?Oeuvre
    {
        return $this->oeuvre;
    }

    public function setOeuvre(?Oeuvre $oeuvre): static
    {
        $this->oeuvre = $oeuvre;

        return $this;
    }

    public function getBenevole(): ?Benevole
    {
        return $this->Benevole;
    }

    public function setBenevole(?Benevole $Benevole): static
    {
        $this->Benevole = $Benevole;

        return $this;
    }
}
