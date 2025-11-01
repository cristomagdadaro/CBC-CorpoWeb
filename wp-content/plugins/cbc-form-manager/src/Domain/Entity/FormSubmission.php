<?php
namespace CbcFormManager\Domain\Entity;

if (!defined('ABSPATH')) { exit; }

class FormSubmission
{
    private string $formKey;
    private array $data;
    private int $userId;
    private string $ip;
    private string $userAgent;
    private \DateTimeImmutable $createdAt;

    public function __construct(string $formKey, array $data, int $userId, string $ip, string $userAgent, ?\DateTimeImmutable $createdAt = null)
    {
        $this->formKey = $formKey;
        $this->data = $data;
        $this->userId = $userId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->createdAt = $createdAt ?: new \DateTimeImmutable('now');
    }

    public function getFormKey(): string { return $this->formKey; }
    public function getData(): array { return $this->data; }
    public function getUserId(): int { return $this->userId; }
    public function getIp(): string { return $this->ip; }
    public function getUserAgent(): string { return $this->userAgent; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

