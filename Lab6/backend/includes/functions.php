<?php
function validate_username(string $u): bool {
    return (bool) preg_match('/^[a-zA-Z0-9_]{1,50}$/', $u);
}

function validate_grade(string $g): bool {
    return (bool) preg_match('/^\d+(\.\d{1,2})?$/', $g)
        && (float)$g >= 1
        && (float)$g <= 10;
}

function validate_group(string $g): bool {
    return in_array($g, get_groups(), true);
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
