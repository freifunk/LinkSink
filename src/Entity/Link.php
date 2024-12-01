<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eko\FeedBundle\Item\Writer\ItemInterface;

/**
 * Description of Link
 *
 * @author andi
 */

#[ORM\Table(name: "links")]
#[ORM\Entity]
class Link extends BaseEntity implements ItemInterface
{

    use TagTrait;

    #[ORM\Column(name: "pubdate", type: "datetimetz")]
    protected ?DateTime $pubdate = null;

    #[ORM\Column(name: "pubyear", type: "integer", nullable: true)]
    private ?int $pubyear = null;

    #[ORM\Column(name: "guid", type: "string", length: 255)]
    protected string $guid;

    #[ORM\Column(name: "description", type: "text")]
    protected string $description = '';

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(name: "url", type: "string", length: 255)]
    protected string $url = '';

    #[ORM\Column(name: "enclosure_id", type: "integer", nullable: true)]
    protected ?int $enclosure_id = null;

    #[ORM\OneToOne(targetEntity: Enclosure::class)]
    #[ORM\JoinColumn(name: "enclosure_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected ?Enclosure $enclosure = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: "links")]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id")]
    protected ?Category $category = null;


    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: "links")]
    #[ORM\JoinTable(name: "links2tags", joinColumns: [new ORM\JoinColumn(name: "links_id", referencedColumnName: "id")], inverseJoinColumns: [new ORM\JoinColumn(name: "tags_id", referencedColumnName: "id")])]
    protected Collection $tags;

    #[ORM\Column(name: "deleted", type: "boolean", nullable: true)]
    protected ?bool $deleted = null;

    #[ORM\Column(name: "deleted_at", type: "datetimetz", nullable: true)]
    protected ?DateTime $deletedAt = null;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function setPubdate(DateTime $pubdate): self
    {
        $this->pubdate = $pubdate;

        return $this;
    }

    public function getPubdate(): ?DateTime
    {
        return $this->pubdate;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setEnclosure(Enclosure $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    public function getEnclosure(): ?Enclosure
    {
        return $this->enclosure;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setEnclosureId(int $enclosureId): self
    {
        $this->enclosure_id = $enclosureId;

        return $this;
    }

    public function getEnclosureId(): ?int
    {
        return $this->enclosure_id;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setPubyear(int $pubyear): self
    {
        $this->pubyear = $pubyear;

        return $this;
    }

    public function getPubyear(): ?int
    {
        return $this->pubyear;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeletedAt(?DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function getFeedItemTitle(): ?string
    {
        return $this->getTitle();
    }

    public function getFeedItemDescription(): string
    {
        return $this->getDescription();
    }

    public function getFeedItemPubDate(): DateTime
    {
        return $this->getPubdate();
    }

    public function getFeedItemLink(): string
    {
        return $this->getUrl();
    }

    public function getFeedMediaItem(): array
    {
        if (!$this->enclosure) {
            return [];
        }
        return array(
            'type'   => $this->enclosure->getType(),
            'length' => $this->enclosure->getLength(),
            'value'  => $this->enclosure->getUrl()
        );
    }

    public function getCategoryName(): string
    {
        return $this->category->getName();
    }


}
