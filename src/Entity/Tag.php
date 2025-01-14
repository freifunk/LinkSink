<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;


#[ORM\Table(name: "tags")]
#[ORM\Entity(repositoryClass: "App\Repository\TagRepository")]
class Tag extends BaseEntity
{
    #[ORM\Column(type: "string", length: 255)]
    protected string $name;

    #[ORM\ManyToMany(targetEntity: "Link", mappedBy: "tags")]
    protected Collection $links;

    public function __construct()
    {
        $this->links = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

}
