<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category extends BaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\OneToMany(targetEntity: Link::class, mappedBy: 'category')]
    protected Collection $links;

    public function __construct()
    {
        $this->links = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addLink(Link $links): self
    {
        $this->links[] = $links;

        return $this;
    }

    public function removeLink(Link $links): void
    {
        $this->links->removeElement($links);
    }

    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function isValid(): bool
    {
        return true;
    }
}
