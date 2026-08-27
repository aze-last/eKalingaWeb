<?php

namespace App\DTO;

class ReportSnapshotData
{
    /**
     * @param  array{title: string, subtitle: string, date_range_label: string, program_label: string, orientation: string, generated_at: string, generated_by: string}  $metadata
     * @param  list<string>  $highlights
     * @param  list<array{label: string, value: string, subtext?: string, status?: string}>  $metrics
     * @param  list<string>  $headers
     * @param  list<array<string, mixed>>  $rows
     * @param  array{prepared_by: string, reviewed_by: string, approved_by: string}  $signatures
     */
    public function __construct(
        public array $metadata,
        public array $highlights,
        public array $metrics,
        public array $headers,
        public array $rows,
        public array $signatures = []
    ) {}

    public function toArray(): array
    {
        return [
            'metadata' => $this->metadata,
            'highlights' => $this->highlights,
            'metrics' => $this->metrics,
            'headers' => $this->headers,
            'rows' => $this->rows,
            'signatures' => $this->signatures,
        ];
    }
}
