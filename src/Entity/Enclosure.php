<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'enclosure')]
class Enclosure extends BaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $url = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $length = null;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $type = null;

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;
        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}