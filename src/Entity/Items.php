<?php

namespace App\Entity;

use App\Repository\ItemsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ItemsRepository::class)]
class Items
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $sku = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?float $auctionPrice = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $auctionStartingAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $auctionEndingAt = null;

    #[ORM\Column(length: 255)]
    private ?string $mediaFile = null;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'sku',
        ]));

        $metadata->addPropertyConstraint('sku', new Assert\NotBlank());

        $metadata->addPropertyConstraint('name', new Assert\NotBlank());

        $metadata->addPropertyConstraint('price', new Assert\NotBlank());

        $metadata->addPropertyConstraint('mediaFile', new Assert\NotBlank());

        $metadata->addPropertyConstraint('auctionPrice', new Assert\Type([
            'type' => 'integer',
            'message' => 'The value {{ value }} is not a valid {{ type }}.',
        ]));

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAuctionPrice(): ?float
    {
        return $this->auctionPrice;
    }

    public function setAuctionPrice(?float $auctionPrice): self
    {
        $this->auctionPrice = $auctionPrice;

        return $this;
    }

    public function getAuctionStartingAt(): ?\DateTimeImmutable
    {
        return $this->auctionStartingAt;
    }

    public function setAuctionStartingAt(?\DateTimeImmutable $auctionStartingAt): self
    {
        $this->auctionStartingAt = $auctionStartingAt;

        return $this;
    }

    public function getAuctionEndingAt(): ?\DateTimeImmutable
    {
        return $this->auctionEndingAt;
    }

    public function setAuctionEndingAt(?\DateTimeImmutable $auctionEndingAt): self
    {
        $this->auctionEndingAt = $auctionEndingAt;

        return $this;
    }

    public function getMediaFile(): ?string
    {
        return $this->mediaFile;
    }

    public function setMediaFile(string $mediaFile): self
    {
        $this->mediaFile = $mediaFile;

        return $this;
    }
}
