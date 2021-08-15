<?php

namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompetenceRepository::class)
 */
class Competence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity=Categorie::class, inversedBy="competences")
     */
    private $categorie;

    /**
     * @ORM\OneToMany(targetEntity=UserCompetence::class, mappedBy="competence", orphanRemoval=true)
     */
    private $userCompetences;

    public function __construct()
    {
        $this->userCompetences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection|UserCompetence[]
     */
    public function getUserCompetences(): Collection
    {
        return $this->userCompetences;
    }

    public function addUserCompetence(UserCompetence $userCompetence): self
    {
        if (!$this->userCompetences->contains($userCompetence)) {
            $this->userCompetences[] = $userCompetence;
            $userCompetence->setCompetence($this);
        }

        return $this;
    }

    public function removeUserCompetence(UserCompetence $userCompetence): self
    {
        if ($this->userCompetences->removeElement($userCompetence)) {
            // set the owning side to null (unless already changed)
            if ($userCompetence->getCompetence() === $this) {
                $userCompetence->setCompetence(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getNom();
    }
}
