<?php

namespace App\DTO;

class SummaryItem
{
    public function __construct(
        public readonly string $label,
        public readonly string $formattedValue,
        public readonly float $rawValue = 0.0,
        public readonly bool $isHighlighted = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'] ?? '',
            formattedValue: $data['formatted_value'] ?? $data['formattedValue'] ?? '',
            rawValue: (float) ($data['raw_value'] ?? $data['rawValue'] ?? 0),
            isHighlighted: (bool) ($data['is_highlighted'] ?? $data['isHighlighted'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'formatted_value' => $this->formattedValue,
            'raw_value' => $this->rawValue,
            'is_highlighted' => $this->isHighlighted,
        ];
    }
}
