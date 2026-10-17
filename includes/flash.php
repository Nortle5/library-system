<?php
function toast(string $type, string $message): void
{
    $GLOBALS['toasts'][] = ['type' => $type, 'message' => $message];
}