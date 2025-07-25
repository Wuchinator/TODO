<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    #[Assert\GreaterThan('today', message: 'Дата дедлайна должна быть в будущем времени')]
    private ?\DateTime $dueDate = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\Column(name: "deletedAt", type: 'datetime', nullable: true)]
    private ?\DateTime $deletedAt = null;

    public function getDeletedAt(): ?\DateTime {
        return $this->deletedAt;
    }
    public function setDeletedAt(?\DateTime $deletedAt): static {
        $this->deletedAt = $deletedAt;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): string
    {
        $now = new \DateTimeImmutable();

        if ($this->status === 'Завершено' || $this->status === 'Отменено') {
            return $this->status;
        }

        if ($this->dueDate < $now) {
            return 'Просрочено';
        }

        return $this->status;
    }


    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
