<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity;

/**
 * Description of TagTrait
 *
 * @author andi
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait TagTrait {

    public function getTags(): ArrayCollection|Collection
    {
        return $this->tags;
    }

    public function clearTags(): void
    {
        if ($this->tags instanceof ArrayCollection) {
            $this->tags->clear();
        } elseif (!is_array($this->tags)) {
            $this->tags = new ArrayCollection();
        }
    }

    public function hasTag(Tag $tag): bool
    {
        if ($this->tags instanceof ArrayCollection) {
            return $this->tags->contains($tag);
        }  else {
            return false;
        }
    }

    public function addTag(Tag $tag): void
    {
        if (!$this->hasTag($tag)) {
            $this->tags[] = $tag;
        }
    }

    public function getTagsAsText(): string {
        if (count($this->getTags()) > 0) {
            $tags = [];
            foreach ($this->getTags() as $tag) {
                $tags[] = $tag->getName();
            }
            return implode(',', $tags);
        } else {
            return '';
        }
    }
}
