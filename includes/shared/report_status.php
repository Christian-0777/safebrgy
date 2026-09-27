<?php
function safeBrgyReportStatusInfo(?string $status, ?string $expiresAt): array
{
    $activeStatuses = ['Pending', 'In Progress'];
    $expiresTimestamp = $expiresAt ? strtotime($expiresAt) : false;
    if (in_array($status, $activeStatuses, true) && $expiresTimestamp !== false && $expiresTimestamp <= time()) {
        return ['name' => 'Expired', 'icon' => 'fa-hourglass-end', 'class' => 'expired'];
    }

    $statusIcons = [
        'Pending' => ['icon' => 'fa-clock', 'class' => 'pending'],
        'In Progress' => ['icon' => 'fa-spinner fa-spin', 'class' => 'in-progress'],
        'Resolved' => ['icon' => 'fa-check-circle', 'class' => 'resolved'],
        'Dismissed' => ['icon' => 'fa-times-circle', 'class' => 'dismissed'],
    ];
    $statusInfo = $statusIcons[$status ?? ''] ?? ['icon' => 'fa-question-circle', 'class' => 'unknown'];
    $statusInfo['name'] = $status ?: 'Unknown';

    return $statusInfo;
}

function safeBrgyRenderReportStatusIcon(?string $status, ?string $expiresAt): string
{
    $statusInfo = safeBrgyReportStatusInfo($status, $expiresAt);
    $label = htmlspecialchars($statusInfo['name'], ENT_QUOTES, 'UTF-8');
    $class = htmlspecialchars($statusInfo['class'], ENT_QUOTES, 'UTF-8');
    $icon = htmlspecialchars($statusInfo['icon'], ENT_QUOTES, 'UTF-8');

    return '<span class="report-status-icon status-' . $class . '" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $label . '" role="img" aria-label="' . $label . '"><i class="fas ' . $icon . '" aria-hidden="true"></i></span>';
}
